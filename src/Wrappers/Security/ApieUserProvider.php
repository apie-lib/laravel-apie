<?php
namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\ApieFacade;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\Entities\EntityInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class ApieUserProvider implements UserProvider
{
    public function __construct(private readonly ApieFacade $apieFacade)
    {
    }

    /**
     * @return ApieUserDecorator<EntityInterface>
     */
    public function retrieveById($identifier): ApieUserDecorator
    {
        $identifier = new DecryptedAuthenticatedUser($identifier);
        $boundedContextId = $identifier->getBoundedContextId();
        $entity = $this->apieFacade->find($identifier->getId(), $boundedContextId);
        return new ApieUserDecorator($identifier, $entity);
    }

    /**
     * @return ApieUserDecorator<EntityInterface>|null
     */
    public function retrieveByToken($identifier, $token): ?ApieUserDecorator
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
    }

    /**
     * @param array<int|string, mixed> $credentials
     * @return ApieUserDecorator<EntityInterface>|null
     */
    public function retrieveByCredentials(array $credentials): ?ApieUserDecorator
    {
        // TODO find the verifyAuthentication action...
        return null;
    }

    /**
     * @param array<int|string, mixed> $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        // TODO find the verifyAuthentication action...
        return false;
    }
}
