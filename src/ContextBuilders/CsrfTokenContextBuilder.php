<?php
namespace Apie\LaravelApie\ContextBuilders;

use Apie\Common\ContextConstants;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\Exceptions\InvalidCsrfTokenException;
use Apie\Core\Session\CsrfTokenProvider;
use Apie\Core\Session\FakeTokenProvider;
use Apie\Serializer\Encoders\FormSubmitDecoder;
use Apie\Serializer\Interfaces\DecoderInterface;

class CsrfTokenContextBuilder implements ContextBuilderInterface, CsrfTokenProvider
{
    private string $tokenName = 'apie,apie';

    /** @var array<string, bool> */
    private array $alreadyChecked = [];

    private function getCsrfToken(): ?string
    {
        $session = app('session');

        if (isset($session)) {
            return $session->token();
        }
        return null;
    }

    public function process(ApieContext $context): ApieContext
    {
        $this->tokenName = $context->hasContext(ContextConstants::RESOURCE_NAME)
            ? $context->getContext(ContextConstants::RESOURCE_NAME)
            : 'apie';
        $this->tokenName .= ',';
        $this->tokenName .=  $context->hasContext(ContextConstants::APIE_ACTION)
            ? $context->getContext(ContextConstants::APIE_ACTION)
            : 'apie';
        if ($context->hasContext(DecoderInterface::class)
            && $context->hasContext(ContextConstants::RAW_CONTENTS)
            && $context->getContext(DecoderInterface::class) instanceof FormSubmitDecoder) {
            $csrf = $context->getContext(ContextConstants::RAW_CONTENTS)['_csrf'] ?? '';
            $this->validateToken($csrf);
        }


        if (!$this->getCsrfToken()) {
            return $context->withContext(CsrfTokenProvider::class, new FakeTokenProvider());
        }
        
        return $context->withContext(CsrfTokenProvider::class, $this);
    }

    public function createToken(): string
    {
        return csrf_token();
    }

    public function validateToken(string $token): void
    {
        if (!empty($this->alreadyChecked[$token])) {
            return;
        }
        $csrfToken = $this->getCsrfToken();
        if (null === $csrfToken) {
            $fakeProvider = new FakeTokenProvider;
            $fakeProvider->validateToken($token);
            return;
        }
        if (!hash_equals($csrfToken, $token)) {
            throw new InvalidCsrfTokenException();
        }
        $this->alreadyChecked[$token] = true;
    }
}
