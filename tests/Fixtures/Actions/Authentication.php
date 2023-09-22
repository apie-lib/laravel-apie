<?php
namespace Apie\Tests\LaravelApie\Fixtures\Actions;

use Apie\Tests\LaravelApie\Fixtures\Entities\User;
use Apie\Tests\LaravelApie\Fixtures\ValueObjects\UserIdentifier;

class Authentication
{
    public static function verifyAuthentication(string $username, string $password): ?User
    {
        if ($username === 'admin' && $password === 'admin') {
            return new User(UserIdentifier::fromNative('admin'));
        }

        return null;
    }
}
