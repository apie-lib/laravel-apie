<?php
namespace Apie\Tests\LaravelApie\Fixtures\ValueObjects;

use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Identifiers\UuidV4;
use Apie\Tests\LaravelApie\Fixtures\Entities\TestEntity;
use ReflectionClass;

class TestEntityIdentifier extends UuidV4 implements IdentifierInterface
{
    public static function getReferenceFor(): ReflectionClass
    {
        return new ReflectionClass(TestEntity::class);
    }
}
