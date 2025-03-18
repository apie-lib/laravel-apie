<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Core\ValueObjects\IsStringValueObject;
use Illuminate\Contracts\Auth\Authenticatable;
use ReflectionClass;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @implements IdentifierInterface<LaravelUserDecorator>
 */
final class LaravelUserDecoratorIdentifier implements IdentifierInterface
{
    use IsStringValueObject;

    /**
     * @var class-string<UserInterface>
     */
    private string $userClass;

    private string $userId;

    public static function createFrom(Authenticatable $user): self
    {
        return new self(get_class($user) . '@' . $user->getAuthIdentifier());
    }

    protected function convert(string $input): string
    {
        $split = explode('@', $input, 2);
        if (!class_exists($split[0])) {
            throw new InvalidStringForValueObjectException($input, $this);
        }
        $this->userClass = $split[0];
        $this->userId = $split[1];
        return $this->userClass . '@' . $this->userId;
    }

    public static function getReferenceFor(): ReflectionClass
    {
        return new ReflectionClass(LaravelUserDecorator::class);
    }
}
