<?php
namespace Apie\LaravelApie\Wrappers\Security;

use Apie\Common\Wrappers\AbstractApieUserDecorator;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Decorator around an Apie entity to tell Laravel we are logged in.
 *
 * @template T of EntityInterface
 * @extends AbstractApieUserDecorator<T>
 */
final class ApieUserDecorator extends AbstractApieUserDecorator implements Authenticatable
{
    public function getAuthIdentifierName()
    {
        return $this->entity->getId();
    }
    public function getAuthIdentifier()
    {
        return $this->id->toNative();
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return '';
    }

    public function setRememberToken($token)
    {
    }

    public function getRememberTokenName()
    {
        return '';
    }
}
