<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Cms\Controllers\FormCommitController;
use Apie\Common\ContextConstants;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\ValueObjects\Utils;
use Closure;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApieUser extends FormCommitController
{
    public function handle(Request $request, Closure $next): Response
    {
        $psrRequest = app(ServerRequestInterface::class);
        if (!$this->supports($psrRequest)) {
            return $next($request);
        }
        $this->__invoke($psrRequest);
        return $next($request);
    }

    private function supports(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('_is_apie', false)
            && $request->getAttribute(ContextConstants::OPERATION_ID)
            && str_starts_with($request->getAttribute(ContextConstants::OPERATION_ID), 'call-method-')
            && 'verifyAuthentication' === $request->getAttribute(ContextConstants::METHOD_NAME);
    }

    protected function createResponse(ServerRequestInterface $psrRequest, ActionResponse $actionResponse): ResponseInterface
    {
        if ($actionResponse->status === ActionResponseStatus::SUCCESS && $actionResponse->result instanceof EntityInterface) {
            $userIdentifier = get_class($actionResponse->result)
                . '/'
                . $psrRequest->getAttribute(ContextConstants::BOUNDED_CONTEXT_ID)
                . '/'
                . Utils::toString($actionResponse->result->getId());
            $user = resolve(ApieUserProvider::class)->retrieveById($userIdentifier);
            Auth::login($user);
        }
        return parent::createResponse($psrRequest, $actionResponse);
    }
}
