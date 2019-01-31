<?php

namespace App\GraphQL\Directives;


use Closure;
use Exception;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\AST\PartialParser;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\TypeManipulator;
use Symfony\Component\HttpFoundation\Cookie;

class StartSessionDirective extends BaseDirective implements TypeManipulator, FieldMiddleware
{
    /**
     * todo remove as soon as name() is static itself.
     * @var string
     */
    const NAME = 'startSession';

    /**
     * The session manager.
     *
     * @var SessionManager
     */
    protected $manager;

    /**
     * @param SessionManager $manager
     * @return void
     */
    public function __construct(SessionManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name()
    {
        return 'startSession';
    }

    /**
     * @param DocumentAST $documentAST
     * @param TypeDefinitionNode $typeDefinition
     * @return DocumentAST
     * @throws DirectiveException
     */
    public function manipulateTypeDefinition(DocumentAST &$documentAST, TypeDefinitionNode &$typeDefinition)
    {
        return $documentAST->setDefinition(
            self::addStartSessionDirectiveToFields(
                $typeDefinition
            )
        );


    }

    /**
     * @param \GraphQL\Language\AST\ObjectTypeDefinitionNode|\GraphQL\Language\AST\ObjectTypeExtensionNode $objectType
     * @param array $middlewareArgValue
     * @return \GraphQL\Language\AST\ObjectTypeDefinitionNode|\GraphQL\Language\AST\ObjectTypeExtensionNode
     *
     * @throws DirectiveException
     */
    public static function addStartSessionDirectiveToFields(TypeDefinitionNode $objectType)
    {
        if (
            !$objectType instanceof ObjectTypeDefinitionNode
            && !$objectType instanceof ObjectTypeExtensionNode
        ) {
            throw new DirectiveException(
                'The ' . self::NAME . ' directive may only be placed on fields or object types.'
            );
        }

        $startSessionDirective = PartialParser::directive("@startSession");

        $objectType->fields = new NodeList(
            (new Collection($objectType->fields))
                ->map(function (FieldDefinitionNode $fieldDefinition) use ($startSessionDirective): FieldDefinitionNode {
                    // If the field already has middleware defined, skip over it
                    // Field middleware are more specific then those defined on a type
                    if (ASTHelper::directiveDefinition($fieldDefinition, self::NAME)) {
                        return $fieldDefinition;
                    }

                    $fieldDefinition->directives = $fieldDefinition->directives->merge([$startSessionDirective]);

                    return $fieldDefinition;
                })
                ->toArray()
        );

        return $objectType;
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $value
     * @param Closure $next
     * @return FieldValue
     * @throws Exception
     */
    public function handleField(FieldValue $value, Closure $next)
    {
        $resolver = $value->getResolver();

        return $next(
            $value->setResolver(
                function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($resolver) {
                    if (!$this->sessionConfigured()) {
                        return $resolver(
                            $root,
                            $args,
                            $context,
                            $resolveInfo
                        );
                    }

                    $request = $context->request();

                    $request->setLaravelSession(
                        $session = $this->startSession($request)
                    );

                    $this->collectGarbage($session);

                    $this->storeCurrentUrl($request, $session);

                    $result = $resolver(
                        $root,
                        $args,
                        $context,
                        $resolveInfo
                    );

                    $this->queueCookie($session);

                    $this->saveSession($request);

                    return $result;
                }
            )
        );
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    protected function sessionConfigured()
    {
        return !is_null($this->manager->getSessionConfig()['driver'] ?? null);
    }

    /**
     * Start the session for the given request.
     *
     * @param Request $request
     * @return Session
     */
    protected function startSession(Request $request)
    {
        return tap($this->getSession($request), function ($session) use ($request) {
            $session->setRequestOnHandler($request);

            $session->start();
        });
    }

    /**
     * Get the session implementation from the manager.
     *
     * @param Request $request
     * @return Session
     */
    public function getSession(Request $request)
    {
        return tap($this->manager->driver(), function ($session) use ($request) {
            $session->setId($request->cookies->get($session->getName()));
        });
    }

    /**
     * Remove the garbage from the session if necessary.
     *
     * @param Session $session
     * @return void
     * @throws Exception
     */
    protected function collectGarbage(Session $session)
    {
        $config = $this->manager->getSessionConfig();

        // Here we will see if this request hits the garbage collection lottery by hitting
        // the odds needed to perform garbage collection on any given request. If we do
        // hit it, we'll call this handler to let it delete all the expired sessions.
        if ($this->configHitsLottery($config)) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }

    /**
     * Determine if the configuration odds hit the lottery.
     *
     * @param array $config
     * @return bool
     * @throws Exception
     */
    protected function configHitsLottery(array $config)
    {
        return random_int(1, $config['lottery'][1]) <= $config['lottery'][0];
    }

    /**
     * Get the session lifetime in seconds.
     *
     * @return int
     */
    protected function getSessionLifetimeInSeconds()
    {
        return ($this->manager->getSessionConfig()['lifetime'] ?? null) * 60;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param Request $request
     * @param Session $session
     * @return void
     */
    protected function storeCurrentUrl(Request $request, $session)
    {
        if ($request->method() === 'GET' &&
            $request->route() &&
            !$request->ajax() &&
            !$request->prefetch()) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param Session $session
     * @return void
     */
    protected function queueCookie(Session $session)
    {
        if ($this->sessionIsPersistent($config = $this->manager->getSessionConfig())) {
            \Cookie::queue(new Cookie(
                $session->getName(), $session->getId(), $this->getCookieExpirationDate(),
                $config['path'], $config['domain'], $config['secure'] ?? false,
                $config['http_only'] ?? true, false, $config['same_site'] ?? null
            ));
        }
    }

    /**
     * Determine if the configured session driver is persistent.
     *
     * @param array|null $config
     * @return bool
     */
    protected function sessionIsPersistent(array $config = null)
    {
        $config = $config ?: $this->manager->getSessionConfig();

        return !in_array($config['driver'], [null, 'array']);
    }

    /**
     * Get the cookie lifetime in seconds.
     *
     * @return \DateTimeInterface|int
     */
    protected function getCookieExpirationDate()
    {
        $config = $this->manager->getSessionConfig();

        return $config['expire_on_close'] ? 0 : Date::instance(
            Carbon::now()->addRealMinutes($config['lifetime'])
        );
    }

    /**
     * Save the session data to storage.
     *
     * @param Request $request
     * @return void
     */
    protected function saveSession($request)
    {
        $this->manager->driver()->save();
    }
}
