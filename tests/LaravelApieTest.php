<?php
namespace Apie\Tests\LaravelApie;

use Apie\Common\ApieFacade;
use Apie\LaravelApie\ApieServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Orchestra\Testbench\TestCase;

final class LaravelApieTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ApieServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        tap($app->make('config'), function (Repository $config) {
            $config->set(
                'apie.bounded_contexts',
                [
                    'default' => [
                        'entities_folder' => __DIR__ . '/Fixtures/Entities',
                        'entities_namespace' => 'Apie\\Tests\\LaravelApie\\Fixtures\\Entities\\',
                        'actions_folder' => __DIR__ . '/Fixtures/Actions',
                        'actions_namespace' => 'Apie\\Tests\\LaravelApie\\Fixtures\\Actions\\',
                    ],
                ]
            );
        });
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_register_apie_as_a_service()
    {
        $this->assertInstanceOf(ApieFacade::class, resolve('apie'));
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_view_swagger_ui()
    {
        $response = $this->get('/api/default/openapi.yaml');
        $response->assertOk();
        // TODO figure out
        // $response->assertSeeText('/api/default/TestEntity');
    }
}
