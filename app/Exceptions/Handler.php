<?php

namespace App\Exceptions;

use App\Messages\ErrorMessage;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use URL;

class Handler extends ExceptionHandler
{
    const ERROR_MESSAGES = 'errors';
    const WARNING_MESSAGES = 'warnings';
    const INFO_MESSAGES = 'info';

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
        } elseif ($e instanceof MessageException) {
            return $this->convertMessageExceptionToResponse($e);
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
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
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
        return $this->redirectWithMessage($e->getMessage());
    }

    protected function redirectWithMessage($message, $messageType = ErrorMessage::TYPE, $redirectTo = null)
    {
        if (isset($redirectTo)) {
            return redirect()->to($redirectTo)->with([$messageType => [$message]]);
        } elseif (0 === strpos(URL::previous(), request()->root()) && URL::previous() != URL::full()) {
            return redirect()->to(URL::previous())->with([$messageType => [$message]]);
        } else {
            return redirect()->to('/')->with([$messageType => [$message]]);
        }
    }

    protected function convertMessageExceptionToResponse(MessageException $e)
    {
        return $this->redirectWithMessage($e->getMessage(), $e->getMessageObject()->getType(), $e->getRedirectTo());
    }
}
