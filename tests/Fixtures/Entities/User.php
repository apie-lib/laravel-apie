<?php
namespace Apie\Tests\LaravelApie\Fixtures\Entities;

use Apie\Core\Entities\EntityInterface;
use Apie\Tests\LaravelApie\Fixtures\ValueObjects\UserIdentifier;

class User implements EntityInterface
{
    public function __construct(
        private readonly UserIdentifier $id
    ) {
    }

    public function getId(): UserIdentifier
    {
        return $this->id;
    }
}
