<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\ApiTokenCookieFactory;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class Login
{
    protected $cookieFactory;

    /**
     * @param ApiTokenCookieFactory $cookieFactory
     */
    public function __construct(ApiTokenCookieFactory $cookieFactory)
    {
        $this->cookieFactory = $cookieFactory;
    }

    /**
     * Return a value for the field.
     *
     * @param null $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param mixed[] $args The arguments that were passed into the field.
     * @param \Nuwave\Lighthouse\Support\Contracts\GraphQLContext $context Arbitrary data that is shared between all fields of a single query.
     * @param \GraphQL\Type\Definition\ResolveInfo $resolveInfo Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resolve($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if ($this->attemptLogin(request())) {
            Cookie::queue($this->cookieFactory->make(
                Auth::user()->getKey(), request()->session()->token()
            ));
        } else {
            throw ValidationException::withMessages([
                $this->username() => [trans('auth.failed')],
            ]);
        }
        return [];
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return [
            'username' => $request->input('variables.' . $this->username()),
            'password' => $request->input('variables.password'),
        ];
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'username';
    }
}
