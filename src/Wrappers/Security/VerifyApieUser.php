<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Cms\Controllers\FormCommitController;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
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
        $context = $this->contextBuilderFactory->createFromRequest($psrRequest);
        $decryptedAuthenticatedUser = $context->getContext(DecryptedAuthenticatedUser::class, false);
        if ($context->hasContext(ContextConstants::AUTHENTICATED_USER)
            && $decryptedAuthenticatedUser instanceof DecryptedAuthenticatedUser) {
            $userIdentifier = $decryptedAuthenticatedUser->toNative();
            $user = resolve(ApieUserProvider::class)->retrieveById($userIdentifier);
            Auth::login($user);
        }
        
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
            $decryptedUserId = DecryptedAuthenticatedUser::createFromEntity(
                $actionResponse->result,
                new BoundedContextId($psrRequest->getAttribute(ContextConstants::BOUNDED_CONTEXT_ID)),
                time() + 3600
            );
            $userIdentifier = $decryptedUserId->toNative();
            $user = resolve(ApieUserProvider::class)->retrieveById($userIdentifier);
            Auth::login($user);
        }
        return parent::createResponse($psrRequest, $actionResponse);
    }
}
