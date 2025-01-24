<?php

namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Core\Entities\EntityInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class LaravelUserDecorator implements EntityInterface
{
    private LaravelUserDecoratorIdentifier $id;
    public function __construct(private readonly Authenticatable $user)
    {
        $this->id = LaravelUserDecoratorIdentifier::createFrom($user);
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }


    public function getId(): LaravelUserDecoratorIdentifier
    {
        return $this->id;
    }
}
