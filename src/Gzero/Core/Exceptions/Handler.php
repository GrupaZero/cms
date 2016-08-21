<?php namespace Gzero\Core\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use Gzero\Validator\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

    const VALIDATION_ERROR = 400;   // (Bad Request)
    const SERVER_ERROR = 500;       // (Internal Server Error)

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Symfony\Component\HttpKernel\Exception\HttpException::class
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
     * @param \Exception               $e       Excetion
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->ajax() || $request->wantsJson() || preg_match('/^api/', $request->getHost())) {

            if ($e instanceof ValidationException) {
                return $this->errorResponse(
                    $request,
                    [
                        'code'    => self::VALIDATION_ERROR,
                        'message' => 'Validation Error',
                        'errors'  => $e->getErrors()
                    ]
                );
            }

            $code = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : self::SERVER_ERROR;

            if (app()->environment() == 'production') {
                return $this->errorResponse(
                    $request,
                    [
                        'code'    => $code,
                        'message' => ($e->getMessage()) ? $e->getMessage() : 'Internal Server Error',
                    ]
                );
            }
            return $this->errorResponse(
                $request,
                [
                    'code'    => $code,
                    'type'    => get_class($e),
                    'message' => ($e->getMessage()) ? $e->getMessage() : 'Internal Server Error',
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                ]
            );
        } else {
            if (app()->environment() == 'production') {
                return parent::render($request, $e);
            }
            $whoops = new Run;
            $whoops->pushHandler(new PrettyPageHandler);
            return response(
                $whoops->handleException($e),
                method_exists($e, 'getStatusCode') ? $e->getStatusCode() : self::SERVER_ERROR,
                method_exists($e, 'getHeaders') ? $e->getHeaders() : []
            );
        }
    }


    /**
     * Error response wrapper
     *
     * @param \Illuminate\Http\Request $request       Request object
     * @param array                    $errorResponse Error response array
     *
     * @return Response
     */
    protected function errorResponse($request, $errorResponse)
    {
        /** @var $cors \Asm89\Stack\CorsService */
        $cors = app()->make('Asm89\Stack\CorsService');
        return $cors->addActualRequestHeaders(
            response()->json($errorResponse, !empty($errorResponse['code']) ? $errorResponse['code'] : self::SERVER_ERROR),
            $request
        );
    }
}
