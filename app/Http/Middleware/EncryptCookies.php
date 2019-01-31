<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
use Symfony\Component\HttpFoundation\Request;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $decryptExcept = [
        'laravel_token'
    ];

    /**
     * Decrypt the cookies on the request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function decrypt(Request $request)
    {
        foreach ($request->cookies as $key => $cookie) {
            if ($this->isDisabled($key) || $this->decryptIsDisabled($key)) {
                continue;
            }

            try {
                $request->cookies->set($key, $this->decryptCookie($key, $cookie));
            } catch (DecryptException $e) {
                $request->cookies->set($key, null);
            }
        }

        return $request;
    }

    /**
     * Determine whether encryption has been disabled for the given cookie.
     *
     * @param string $name
     * @return bool
     */
    public function decryptIsDisabled($name)
    {
        return in_array($name, $this->decryptExcept);
    }
}
