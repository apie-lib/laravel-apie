<?php

namespace Apie\LaravelApie;

use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\Actions\ActionInterface;
use Apie\Core\Actions\ApieFacadeInterface;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Context\ApieContext;
use Apie\Core\Datalayers\Lists\EntityListInterface;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\LaravelApie\Wrappers\Core\BoundedContextSelected;
use Apie\LaravelApie\Wrappers\Security\ApieUserDecorator;
use Apie\LaravelApie\ContextBuilders\ApieCurrentUserContextBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;
use ReflectionClass;
use ReflectionMethod;

/**
 * @method static EntityListInterface<EntityInterface> all(string|ReflectionClass<EntityInterface> $class, BoundedContext|BoundedContextId $boundedContext)
 * @method static EntityInterface find(IdentifierInterface<EntityInterface> $identifier, BoundedContext|BoundedContextId $boundedContext)
 * @method static EntityInterface persistNew(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext)
 * @method static EntityInterface persistExisting(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext)
 * @method static EntityInterface upsert(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext)
 * @method static void removeExisting(EntityInterface $entity, BoundedContext|BoundedContextId $boundedContext)
 * @method static string|int|float|bool|ItemList|ItemHashmap|null normalize(mixed $object, ApieContext $apieContext)
 * @method static mixed denormalizeNewObject(string|int|float|bool|ItemList|ItemHashmap|array<mixed>|null $object, string $desiredType, ApieContext $apieContext)
 * @method static mixed denormalizeOnExistingObject(ItemHashmap $object, object $existingObject, ApieContext $apieContext)
 * @method static mixed denormalizeOnMethodCall(string|int|float|bool|ItemList|ItemHashmap|array<mixed>|null $input, ?object $object, ReflectionMethod $method, ApieContext $apieContext)
 * @method static ActionInterface createAction(ApieContext $apieContext)
 *
 * @see ApieFacadeInterface
 */
class Apie extends Facade
{
    /** @var DecryptedAuthenticatedUser<EntityInterface>|null */
    private static ?DecryptedAuthenticatedUser $currentUser = null;
    protected static function getFacadeAccessor(): string
    {
        return 'apie';
    }

    public static function loginAs(EntityInterface $entity, BoundedContext|BoundedContextId|string|null $boundedContext): void
    {
        if (is_string($boundedContext)) {
            $boundedContext = new BoundedContextId($boundedContext);
        }
        $boundedContextId = $boundedContext instanceof BoundedContext ? $boundedContext->getId() : $boundedContext;
        if ($boundedContextId === null) {
            $boundedContextId = resolve(BoundedContextSelected::class)->getBoundedContextFromRequest()?->getId();
        }
        if ($boundedContextId === null) {
            $className = $entity->getId()->getReferenceFor()->name;
            $boundedContextId = resolve(BoundedContextSelected::class)->getBoundedContextFromClassName($className)?->getId();
        }
        if ($boundedContextId === null) {
            throw new \RuntimeException('Could not determine bounded context for the given entity or request.');
        }

        // Auto-login the new user
        $decryptedUserId = DecryptedAuthenticatedUser::createFromEntity(
            $entity,
            $boundedContextId,
            time() + 3600
        );
        $decoratedUser = new ApieUserDecorator($decryptedUserId, $entity);
        Auth::login($decoratedUser);
        self::$currentUser = $decryptedUserId;
    }

    public static function logout(): void
    {
        Auth::logout();
        self::$currentUser = null;
    }

    /**
     * @internal
     * @see ApieCurrentUserContextBuilder
     * @return DecryptedAuthenticatedUser<EntityInterface>|null
     */
    public static function getCurrentUser(): ?DecryptedAuthenticatedUser
    {
        return tap(self::$currentUser, function () {
            self::$currentUser = null;
        });
    }
}