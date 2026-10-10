<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\ApieFacade;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\Actions\ActionResponse;
use Apie\Core\Actions\ActionResponseStatus;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\ContextConstants;
use Apie\Core\Entities\EntityInterface;
use Apie\RestApi\Exceptions\InvalidContentTypeException;
use Closure;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApieUser
{
    public function __construct(
        private readonly ContextBuilderFactory $contextBuilderFactory,
        private readonly ApieFacade $apieFacade,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $psrRequest = app(ServerRequestInterface::class);
        try {
            $context = $this->contextBuilderFactory->createFromRequest($psrRequest);
            $decryptedAuthenticatedUser = $context->getContext(DecryptedAuthenticatedUser::class, false);
            if ($context->hasContext(ContextConstants::AUTHENTICATED_USER)
                && $decryptedAuthenticatedUser instanceof DecryptedAuthenticatedUser) {
                $userIdentifier = $decryptedAuthenticatedUser->toNative();
                $user = resolve(ApieUserProvider::class)->retrieveById($userIdentifier);
                Auth::login($user);
            }
        } catch (InvalidContentTypeException) {
            // Ignore invalid content type exceptions here.
        }
        
        if (!$this->supports($psrRequest)) {
            return $next($request);
        }
        $context = $this->contextBuilderFactory->createFromRequest($psrRequest, [ContextConstants::CMS => true]);
        $action = $this->apieFacade->createAction($context);
        $actionResponse = $action($context, $context->getContext(ContextConstants::RAW_CONTENTS));
        $this->loginAsUserOnSuccessfulResponse($psrRequest, $actionResponse);
        return $next($request);
    }

    private function supports(ServerRequestInterface $request): bool
    {
        return $request->getAttribute('_is_apie', false)
            && $request->getAttribute(ContextConstants::OPERATION_ID)
            && str_starts_with($request->getAttribute(ContextConstants::OPERATION_ID), 'call-method-')
            && 'verifyAuthentication' === $request->getAttribute(ContextConstants::METHOD_NAME);
    }

    private function loginAsUserOnSuccessfulResponse(ServerRequestInterface $psrRequest, ActionResponse $actionResponse): void
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
    }
}
