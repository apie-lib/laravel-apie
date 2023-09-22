<?php
namespace Apie\LaravelApie\Wrappers\Cms;

use Apie\Common\Interfaces\DashboardContentFactoryInterface;

class DashboardContentFactory implements DashboardContentFactoryInterface
{
    public function create(
        string $template,
        array $templateParameters = []
    ): DashboardContents {
        return new DashboardContents($template, $templateParameters);
    }
}
