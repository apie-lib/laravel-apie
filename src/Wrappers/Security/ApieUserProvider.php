<?php
namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\ApieFacade;
use Apie\Common\Wrappers\ApieUserDecorator;
use Apie\Common\Wrappers\ApieUserDecoratorIdentifier;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ApieUserProvider implements UserProvider
{
    public function __construct(private readonly ApieFacade $apieFacade)
    {
    }

    public function retrieveById($identifier): ApieUserDecorator
    {
        $identifier = new ApieUserDecoratorIdentifier($identifier);
        $boundedContextId = $identifier->getBoundedContextId();
        $entity = $this->apieFacade->find($identifier->getIdentifier(), $boundedContextId);
        return new ApieUserDecorator($identifier, $entity);
    }

    public function retrieveByToken($identifier, $token): ?ApieUserDecorator
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
    }

    public function retrieveByCredentials(array $credentials): ?ApieUserDecorator
    {
        // TODO find the verifyAuthentication action...
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // TODO find the verifyAuthentication action...
        return false;
    }
}
