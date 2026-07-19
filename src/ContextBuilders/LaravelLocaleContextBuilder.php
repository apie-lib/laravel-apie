<?php
namespace Apie\LaravelApie\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Illuminate\Support\Facades\App;

final class LaravelLocaleContextBuilder implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        $locale = App::getLocale();
        if (!$locale) {
            return $context;
        }

        if (!$context->hasContext(ContextConstants::LOCALE)) {
            $context = $context->withContext(ContextConstants::LOCALE, $locale);
        }
        if (!$context->hasContext(ContextConstants::ACCEPT_LOCALE)) {
            $context = $context->withContext(ContextConstants::ACCEPT_LOCALE, $locale);
        }
        if (!$context->hasContext(ContextConstants::DATA_LOCALE)) {
            $context = $context->withContext(ContextConstants::DATA_LOCALE, $locale);
        }

        return $context;
    }
}
