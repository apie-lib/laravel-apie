<?php
namespace Apie\Tests\LaravelApie;

use Apie\Common\ApieFacade;
use Apie\Common\Events\AddAuthenticationCookie;
use Apie\Common\ValueObjects\DecryptedAuthenticatedUser;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\LaravelApie\Apie;
use Apie\LaravelApie\ApieServiceProvider;
use Apie\LaravelApie\Config\ValidateAndSanitizeConfig;
use Apie\LaravelApie\Wrappers\Security\ApieUserDecorator;
use Apie\Tests\LaravelApie\Fixtures\Entities\User;
use Apie\Tests\LaravelApie\Fixtures\ValueObjects\UserIdentifier;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Cookie\Middleware\EncryptCookies;
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
            $config->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
            $config->set('apie.encryption_key', 'test');
            $config->set('apie.enable_security', true);
            $config->set(
                'apie.bounded_contexts',
                [
                    'default' => [
                        'entities_folder' => __DIR__ . '/Fixtures/Entities',
                        'entities_namespace' => 'Apie\\Tests\\LaravelApie\\Fixtures\\Entities\\',
                        'actions_folder' => __DIR__ . '/Fixtures/Actions',
                        'actions_namespace' => 'Apie\\Tests\\LaravelApie\\Fixtures\\Actions\\',
                        'policy_folder' => __DIR__ . '/Fixtures/Policies',
                        'policy_namespace' => 'Apie\\Tests\\LaravelApie\\Fixtures\\Policies\\',
                    ],
                ]
            );
            $config->set(
                'apie.scan_bounded_contexts',
                [
                ]
            );
            $config->set('auth.guards.web', ['driver' => 'session', 'provider' => 'apie']);
            $config->set('auth.providers.apie', ['driver' => 'apie', 'model' => ApieUserDecorator::class]);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function it_can_register_apie_as_a_service()
    {
        $this->assertInstanceOf(ApieFacade::class, resolve('apie'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function the_facade_can_login_as_a_user()
    {
        $response = $this->postJson('/api/default/mockLogin', [
            'user' => [
                'id' => 'admin',
            ]
        ]);
        $response->assertOk();
        $cookie = $response->getCookie(AddAuthenticationCookie::COOKIE_NAME, decrypt: false);
        $this->assertNotNull($cookie);
        $response->assertJson([
            'id' => 'admin',
        ]);
    
        $response = $this->withUnencryptedCookies([$cookie->getName() => $cookie->getValue()])
            ->get('/api/default/me');
        $response->assertOk();
        $response->assertJson([
            'id' => 'admin',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function testValidateAndSanitizeConfig(): void
    {
        $input = [
            'enable_security' => true,
        ];
        $result = ValidateAndSanitizeConfig::process($input);
        $this->assertArrayHasKey('enable_security', $result);
        $this->assertTrue($result['enable_security']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    public function it_can_view_swagger_ui()
    {
        $response = $this->get('/api/default/openapi.yaml');
        $response->assertOk();
        $response->assertSeeText('TestEntity-post');
    }
}
