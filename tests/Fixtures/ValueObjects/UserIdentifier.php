<?php
namespace Apie\Tests\LaravelApie\Fixtures\ValueObjects;

use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Identifiers\KebabCaseSlug;
use Apie\Tests\LaravelApie\Fixtures\Entities\User;
use ReflectionClass;

class UserIdentifier extends KebabCaseSlug implements IdentifierInterface
{
    public static function getReferenceFor(): ReflectionClass
    {
        return new ReflectionClass(User::class);
    }
}
