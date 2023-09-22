<?php

namespace Apie\LaravelApie\ErrorHandler;

use Apie\Common\IntegrationTestLogger;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    private static bool $alreadyRenderErrorPage = false;

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function report(Throwable $e)
    {
        IntegrationTestLogger::logException($e);
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        $response = null;
        if (!self::$alreadyRenderErrorPage) {
            self::$alreadyRenderErrorPage = true;
            try {
                /** @var ApieErrorRenderer $apieErrorHandler */
                $apieErrorHandler = resolve(ApieErrorRenderer::class);
                if ($apieErrorHandler->isApieRequest($request)) {
                    if ($apieErrorHandler->canCreateCmsResponse($request)) {
                        $response = $apieErrorHandler->createCmsResponse($request, $e);
                    } else {
                        $response = $apieErrorHandler->createApiResponse($e);
                    }

                }
            } finally {
                self::$alreadyRenderErrorPage = false;
            }
        }
        return $response ?? parent::render($request, $e);
    }
}
