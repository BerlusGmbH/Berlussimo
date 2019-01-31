<?php

namespace App\GraphQL\Directives;


use Closure;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeExtensionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\AST\PartialParser;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\TypeManipulator;

class AuthenticateSessionDirective extends BaseDirective implements TypeManipulator, FieldMiddleware
{
    /**
     * todo remove as soon as name() is static itself.
     * @var string
     */
    const NAME = 'authenticateSession';

    /**
     * The authentication factory implementation.
     *
     * @var AuthFactory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param AuthFactory $auth
     * @return void
     */
    public function __construct(AuthFactory $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name()
    {
        return 'authenticationDirective';
    }

    /**
     * @param DocumentAST $documentAST
     * @param TypeDefinitionNode $node
     * @return DocumentAST
     * @throws DirectiveException
     */
    public function manipulateTypeDefinition(DocumentAST &$documentAST, TypeDefinitionNode &$node)
    {
        return $documentAST->setDefinition(
            self::addAuthenticateSessionDirectiveToFields(
                $node
            )
        );
    }

    /**
     * @param TypeDefinitionNode $objectType
     * @return ObjectTypeDefinitionNode|ObjectTypeExtensionNode
     *
     * @throws DirectiveException
     */
    public static function addAuthenticateSessionDirectiveToFields(TypeDefinitionNode $objectType)
    {
        if (
            !$objectType instanceof ObjectTypeDefinitionNode
            && !$objectType instanceof ObjectTypeExtensionNode
        ) {
            throw new DirectiveException(
                'The ' . self::NAME . ' directive may only be placed on fields or object types.'
            );
        }

        $startSessionDirective = PartialParser::directive("@authenticateSession");

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
     * Handle node value.
     *
     * @param FieldValue $value
     * @param Closure $next
     * @return FieldValue
     */
    public function handleField(FieldValue $value, Closure $next)
    {
        $resolver = $value->getResolver();

        return $next(
            $value->setResolver(
                function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($resolver) {
                    $request = $context->request();

                    if (!$request->user() || !$request->session()) {
                        return $resolver(
                            $root,
                            $args,
                            $context,
                            $resolveInfo
                        );
                    }

                    if ($this->auth->viaRemember()) {
                        $passwordHash = explode('|', $request->cookies->get($this->auth->getRecallerName()))[2];

                        if ($passwordHash != $request->user()->getAuthPassword()) {
                            $this->logout($request);
                        }
                    }

                    if (!$request->session()->has('password_hash')) {
                        $this->storePasswordHashInSession($request);
                    }

                    if ($request->session()->get('password_hash') !== $request->user()->getAuthPassword()) {
                        $this->logout($request);
                    }

                    $this->storePasswordHashInSession($request);

                    return $resolver(
                        $root,
                        $args,
                        $context,
                        $resolveInfo
                    );
                }
            )
        );
    }

    /**
     * Log the user out of the application.
     *
     * @param Request $request
     * @return void
     *
     * @throws AuthenticationException
     */
    protected function logout($request)
    {
        $this->auth->logout();

        $request->session()->flush();

        throw new AuthenticationException;
    }

    /**
     * Store the user's current password hash in the session.
     *
     * @param Request $request
     * @return void
     */
    protected function storePasswordHashInSession($request)
    {
        if (!$request->user()) {
            return;
        }

        $request->session()->put([
            'password_hash' => $request->user()->getAuthPassword(),
        ]);
    }
}
