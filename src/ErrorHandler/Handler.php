<?php

namespace Apie\LaravelApie\ErrorHandler;

use Apie\Common\IntegrationTestLogger;
use Apie\Core\Exceptions\ApieException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler as ExceptionsHandler;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionsHandler
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

    public function __construct(
        Container  $container,
        private ExceptionHandler $internal
    ) {
        parent::__construct($container);
    }

    public function report(Throwable $e): void
    {
        IntegrationTestLogger::logException($e);
        $this->internal->report($e);
    }

    public function shouldReport(Throwable $e): bool
    {
        return $this->internal->shouldReport($e);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface  $output
     */
    public function renderForConsole($output, Throwable $e): void
    {
        $this->internal->renderForConsole($output, $e);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function render($request, Throwable $e): Response|HttpResponse
    {
        $response = null;
        if (!self::$alreadyRenderErrorPage) {
            self::$alreadyRenderErrorPage = true;
            try {
                /** @var ApieErrorRenderer $apieErrorHandler */
                $apieErrorHandler = resolve(ApieErrorRenderer::class);
                if ($apieErrorHandler->isApieRequest($request) || $e instanceof ApieException) {
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
        if ($response) {
            return $response;
        }
        return $this->internal->render($request, $e);
    }
}
