<?php
namespace Apie\LaravelApie\Wrappers\Cms;

use Stringable;

class DashboardContents implements Stringable
{
    public function __toString(): string
    {
        return (string) view(config('apie.cms.dashboard_template'));
    }
}
