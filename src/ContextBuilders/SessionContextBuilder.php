<?php
namespace Apie\LaravelApie\ContextBuilders;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Apie\Core\ContextConstants;
use Apie\Core\Enums\RequestMethod;
use Apie\Core\ValueObjects\Utils;
use Illuminate\Http\Request;
use Illuminate\Session\SymfonySessionDecorator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionContextBuilder implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        $request = request();
        if ($request instanceof Request && $request->hasSession()) {
            $session =  $request->session();
            $context = $context->withContext(SessionInterface::class, new SymfonySessionDecorator($session));
            // TODO: move to its own context builder
            if ($context->getContext(RequestMethod::class, false) === RequestMethod::GET) {
                if ($session->has('_filled_in') && $context->hasContext(ContextConstants::DISPLAY_FORM)) {
                    $context = $context->withContext(
                        ContextConstants::RAW_CONTENTS,
                        Utils::toArray($session->get('_filled_in', []))
                    );
                }
                $context = $context->withContext(
                    ContextConstants::VALIDATION_ERRORS,
                    Utils::toArray($session->get('_validation_errors', []))
                );
                // TODO: unset from session
            }
        }

        return $context;
    }
}
