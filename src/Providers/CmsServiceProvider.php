<?php
namespace Apie\LaravelApie\Providers;

use Apie\Cms\CmsServiceProvider as CmsCmsServiceProvider;
use Apie\LaravelApie\Wrappers\Cms\DashboardContents;

class CmsServiceProvider extends CmsCmsServiceProvider
{
    public function register()
    {
        parent::register();

        // symfony equivalent: sf_cms.yaml
        $this->app->bind('apie.cms.dashboard_content', DashboardContents::class);
        // blade extensions?

        $this->app->bind(DashboardContents::class);
    }
}
