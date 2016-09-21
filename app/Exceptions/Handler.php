<?php

namespace App\Exceptions;

use Exception;
use URL;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if (ob_get_status()) {
            ob_end_clean();
        }

        if ($this->hasMiddleware('api', $request)) {
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            } elseif ($e instanceof ModelNotFoundException) {
                $e = new NotFoundHttpException($e->getMessage(), $e);
            } elseif ($e instanceof AuthenticationException) {
                return $this->unauthenticated($request, $e);
            } elseif ($e instanceof AuthorizationException) {
                $e = new HttpException(403, $e->getMessage());
            } elseif ($e instanceof ValidationException && $e->getResponse()) {
                return $e->getResponse();
            }

            if ($this->isHttpException($e)) {
                return $this->toIlluminateResponse($this->renderHttpException($e), $e);
            } else {
                return $this->toIlluminateResponse($this->convertExceptionToJsonResponse($e), $e);
            }
        }

        if ($e instanceof AuthorizationException) {
            return $this->convertAuthorizationExceptionToResponse($e);
        }

        return parent::render($request, $e);
    }

    /**
     * @param $middleware
     * @return bool
     */
    protected function hasMiddleware($middleware, $request)
    {
        if ($request->route() !== null) {
            return in_array($middleware, $request->route()->middleware());
        } else {
            return false;
        }
    }

    /**
     * Create a Symfony response for the given exception.
     *
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertExceptionToJsonResponse(Exception $e)
    {
        $e = FlattenException::create($e);

        $json = ['error' => [
            'status' => (string)$e->getStatusCode(),
            'title' => $e->getMessage()
        ]];

        if (config('app.debug')) {
            $trace = ['trace' => $e->getTrace()];
            $json['error']['meta'] = $trace;
        }

        return SymfonyResponse::create($json, $e->getStatusCode(), $e->getHeaders());
    }

    protected function convertAuthorizationExceptionToResponse(AuthorizationException $e)
    {
        if (0 === strpos(URL::previous(), request()->root()) && URL::previous() != URL::current()) {
            return redirect()->to(URL::previous())->with(['errors' => [$e->getMessage()]]);
        } else {
            return $this->toIlluminateResponse($this->renderHttpException(new HttpException(403, $e->getMessage())), $e);
        }
    }
}
