<?php namespace Gzero\Core\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

    const FORBIDDEN = 403;
    const SERVER_ERROR = 500;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class
    ];

    // @codingStandardsIgnoreStart

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $e Exception
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    // @codingStandardsIgnoreEnd

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request Request object
     * @param \Exception               $e       Exception
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException('Not found', $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(self::FORBIDDEN, 'Forbidden', $e);
        }

        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        } elseif ($e instanceof HttpException) {
            return $this->httpException($request, $e);
        }

        $flatted = FlattenException::create($e);

        if (app()->environment() === 'local' && config('app.debug')) {
            $handler = new PrettyPageHandler;
            if ($this->wantsJson($request)) {
                $handler = new JsonResponseHandler();
                $handler->addTraceToOutput(true);
            }
            $whoops = new Run;
            $whoops->allowQuit(false);
            $whoops->writeToOutput(false);
            $whoops->pushHandler($handler);
            $whoops->register();

            $response = response(
                $whoops->handleException($e),
                $this->isProperHTTPErrorCode($flatted->getStatusCode()) ? $flatted->getStatusCode() : self::SERVER_ERROR,
                $flatted->getHeaders() ?: []
            );
            if ($this->wantsJson($request)) {
                $response->header('Content-Type', 'application/json');
                if (class_exists('Barryvdh\Cors\Stack\CorsService')) {
                    app('Barryvdh\Cors\Stack\CorsService')->addActualRequestHeaders($response, $request);
                }
            }
            return $response;
        } else {
            if ($this->wantsJson($request)) {
                $response = response()->json(
                    [
                        'error' => [
                            'message' => 'Internal Server Error'
                        ]
                    ],
                    $this->isProperHTTPErrorCode($flatted->getStatusCode()) ? $flatted->getStatusCode() : self::SERVER_ERROR,
                    $flatted->getHeaders()
                );
                if (class_exists('Barryvdh\Cors\Stack\CorsService')) {
                    app('Barryvdh\Cors\Stack\CorsService')->addActualRequestHeaders($response, $request);
                }
                return $response;
            }
            return parent::convertExceptionToResponse($e);
        }
    }

    /**
     * It checks if specific code is valid HTTP error code
     *
     * @param int $code Error code
     *
     * @return bool
     */
    protected function isProperHTTPErrorCode($code)
    {
        return (!empty($code) && (int) $code >= 400);
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException $e       Execption
     * @param  \Illuminate\Http\Request                   $request Request
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        $errors = $e->validator->errors()->getMessages();

        if ($this->wantsJson($request)) {
            return response()->json(
                [
                    'error' => [
                        'message' => 'Validation Error',
                        'errors'  => $errors
                    ]
                ],
                422
            );
        }

        return redirect()->back()->withInput($request->input())->withErrors($errors);
    }

    // @codingStandardsIgnoreStart

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request Request
     *
     * @param AuthenticationException   $e       Exception
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $e)
    {
        if ($this->wantsJson($request)) {
            return response()->json(['error' => ['message' => 'Unauthorized']], 401);
        }

        return redirect()->guest('login');
    }

    /**
     * @param               $request
     * @param HttpException $e
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    protected function httpException($request, HttpException $e)
    {
        $message = $e->getMessage();
        // Making sure that we always return message for 404's
        if ($e->getStatusCode() === 404 && empty($message)) {
            $message = 'Not Found';
        }
        if ($this->wantsJson($request)) {
            return response()->json(['error' => ['message' => $message]], $e->getStatusCode());
        }

        return $this->renderHttpException($e);
    }

    // @codingStandardsIgnoreEnd

    /**
     * @param \Illuminate\Http\Request $request Request
     *
     * @return bool
     */
    protected function wantsJson($request): bool
    {
        return $request->expectsJson() || str_is('api.*', $request->getHost());
    }
}
