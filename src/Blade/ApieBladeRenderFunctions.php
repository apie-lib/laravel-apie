<?php
namespace Apie\LaravelApie\Blade;

use Apie\Common\Wrappers\TemplateRenderFunctions;
use Apie\HtmlBuilders\Factories\FieldDisplayComponentFactory;
use Apie\HtmlBuilders\Interfaces\ComponentRendererInterface;

final class ApieBladeRenderFunctions
{
    use TemplateRenderFunctions;
    
    public function __construct(
        private readonly ComponentRendererInterface $renderer,
        private readonly FieldDisplayComponentFactory $fieldDisplayComponentFactory
    ) {
    }


}
