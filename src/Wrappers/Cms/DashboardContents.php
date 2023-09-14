<?php
namespace Apie\LaravelApie\Wrappers\Cms;

use Stringable;

class DashboardContents implements Stringable
{
    /**
     * @param array<string, mixed> $templateParameters
     */
    public function __construct(
        private readonly string $template,
        private readonly array $templateParameters
    ) {
    }
    public function __toString(): string
    {
        return (string) view($this->template, $this->templateParameters);
    }
}
