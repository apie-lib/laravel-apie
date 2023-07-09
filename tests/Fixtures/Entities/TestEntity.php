<?php
namespace Apie\Tests\LaravelApie\Fixtures\Entities;

use Apie\Core\Entities\EntityInterface;
use Apie\Tests\LaravelApie\Fixtures\ValueObjects\TestEntityIdentifier;

class TestEntity implements EntityInterface
{
    public function __construct(
        private readonly TestEntityIdentifier $id
    ) {
    }

    public function getId(): TestEntityIdentifier
    {
        return $this->id;
    }
}
