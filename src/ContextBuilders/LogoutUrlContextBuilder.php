<?php
namespace Apie\LaravelApie\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;

class LogoutUrlContextBuilder implements ContextBuilderInterface
{
    public function __construct(private readonly ?string $logoutUrl)
    {
    }

    public function process(ApieContext $context): ApieContext
    {
        if ($this->logoutUrl) {
            return $context->withContext(
                ContextConstants::LOGOUT_URL,
                $this->logoutUrl
            );
        }
        return $context;
    }
}