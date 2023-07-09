<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\ApieFacade;
use Apie\Common\ContextConstants;
use Apie\Common\RequestBodyDecoder;
use Apie\Common\Wrappers\ApieUserDecorator;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\ContextBuilders\ContextBuilderFactory;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\ValueObjects\Utils;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApieUserAuthenticator
{
    public function __construct(
        private readonly ApieFacade $apieFacade,
        private readonly ContextBuilderFactory $contextBuilderFactory,
        private readonly RequestBodyDecoder $decoder
    ) {
    }

    public function supports(ServerRequestInterface $request): ?bool
    {
        return $request->getAttribute('_is_apie', false)
            && $request->getAttribute(ContextConstants::OPERATION_ID)
            && str_starts_with($request->getAttribute(ContextConstants::OPERATION_ID), 'call-method-')
            && 'verifyAuthentication' === $request->getAttribute(ContextConstants::METHOD_NAME);
    }

    public function authenticate(ServerRequestInterface $request): ?ApieUserDecorator
    {
        try {
            $psrRequest = $request->withHeader('Accept', 'application/json');
            if (!$this->supports($psrRequest)) {
                return null;
            }
            $actionClass = $psrRequest->getAttribute(ContextConstants::APIE_ACTION);
            /** @var ActionInterface $action */
            $action = new $actionClass($this->apieFacade);
            $context = $this->contextBuilderFactory->createFromRequest($psrRequest, $psrRequest->getAttributes());
            $actionResponse = $action($context, $this->decoder->decodeBody($psrRequest));

            if ($actionResponse->result instanceof EntityInterface) {
                $userIdentifier = get_class($actionResponse->result)
                    . '/'
                    . $psrRequest->getAttribute(ContextConstants::BOUNDED_CONTEXT_ID)
                    . '/'
                    . Utils::toString($actionResponse->result->getId());
                return resolve(ApieUserProvider::class)->retrieveById($userIdentifier);
            }
        } catch (Exception $error) {
            throw new AuthenticationException('Could not authenticate user!', 0, $error);
        }
        throw new AuthenticationException('Could not authenticate user!');
    }
}
