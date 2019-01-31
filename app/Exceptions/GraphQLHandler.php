<?php


namespace App\Exceptions;


use Closure;
use GraphQL\Error\Error;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Nuwave\Lighthouse\Execution\ErrorHandler;

class GraphQLHandler implements ErrorHandler
{

    /**
     * This function receives all GraphQL errors and may alter them or do something else with them.
     *
     * Always call $next($error) to keep the Pipeline going. Multiple such Handlers may be registered
     * as an array in the config.
     *
     * @param \GraphQL\Error\Error $error
     * @param \Closure $next
     * @return array
     */
    public static function handle(Error $error, Closure $next): array
    {
        $underlyingException = $error->getPrevious();

        if ($underlyingException && $underlyingException instanceof ValidationException) {
            // Reconstruct the error, passing in the extensions of the underlying exception
            $error = new Error(
                $error->message,
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                new \App\Exceptions\ValidationException(
                    $underlyingException->validator,
                    $underlyingException->response,
                    $underlyingException->errorBag
                )
            );
        }

        if ($underlyingException && $underlyingException instanceof AuthenticationException) {
            $underlyingException = new \Nuwave\Lighthouse\Exceptions\AuthenticationException(
                $underlyingException->getMessage(),
                $underlyingException->guards(),
                $underlyingException->redirectTo()
            );

            // Reconstruct the error, passing in the extensions of the underlying exception
            $error = new Error(
                $error->message,
                $error->nodes,
                $error->getSource(),
                $error->getPositions(),
                $error->getPath(),
                $underlyingException,
                $underlyingException->extensionsContent()
            );
        }

        return $next($error);
    }
}