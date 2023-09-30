<?php
namespace Apie\LaravelApie\Providers;

use Apie\Cms\CmsServiceProvider as CmsCmsServiceProvider;
use Apie\HtmlBuilders\Configuration\ApplicationConfiguration;
use Apie\LaravelApie\Wrappers\Cms\DashboardContents;

class CmsServiceProvider extends CmsCmsServiceProvider
{
    public function register()
    {
        parent::register();

        // symfony equivalent: sf_cms.yaml
        $this->app->bind('apie.cms.dashboard_content', DashboardContents::class);
        // blade extensions?

        $this->app->bind(DashboardContents::class, function () {
            return new DashboardContents(
                config('apie.cms.dashboard_template'),
                []
            );
        });

        // workaround against apie/service-provider-generator not parsing parameters in arrays
        $this->app->singleton(ApplicationConfiguration::class, function () {
            return new ApplicationConfiguration([
                'base_url' => config('apie.cms.base_url'),
            ]);
        });
    }
}
