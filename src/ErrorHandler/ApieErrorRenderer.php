<?php

namespace Apie\LaravelApie\ErrorHandler;

use Apie\Common\ContextConstants;
use Apie\Common\ErrorHandler\ApiErrorRenderer as CommonApiErrorRenderer;
use Apie\HtmlBuilders\ErrorHandler\CmsErrorRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApieErrorRenderer
{
    public function __construct(
        private readonly ?CmsErrorRenderer $cmsErrorRenderer,
        private readonly CommonApiErrorRenderer $apiErrorRenderer,
        private readonly string $cmsBaseUrl
    ) {
    }

    public function isApieRequest(Request $request): bool
    {
        return $request->attributes->has('_is_apie') ||
            $this->canCreateCmsResponse($request);
    }

    public function canCreateCmsResponse(Request $request): bool
    {
        if ($request->attributes->has(ContextConstants::CMS)
            && null !== $this->cmsErrorRenderer
        ) {
            return true;
        }
        return str_starts_with($request->getPathInfo(), $this->cmsBaseUrl);
    }

    public function createApiResponse(Throwable $error): Response
    {
        return $this->apiErrorRenderer->createApiResponse($error);
    }

    public function createCmsResponse(Request $request, Throwable $error): Response
    {
        if (!$this->cmsErrorRenderer) {
            return new Response("Internal error", 500);
        }
        return $this->cmsErrorRenderer->createCmsResponse($request, $error);
    }
}
