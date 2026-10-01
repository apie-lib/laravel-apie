<img src="https://raw.githubusercontent.com/apie-lib/apie-lib-monorepo/main/docs/apie-logo.svg" width="100px" align="left" />
<h1>laravel-apie</h1>






 [![Latest Stable Version](https://poser.pugx.org/apie/laravel-apie/v)](https://packagist.org/packages/apie/laravel-apie) [![Total Downloads](https://poser.pugx.org/apie/laravel-apie/downloads)](https://packagist.org/packages/apie/laravel-apie) [![Latest Unstable Version](https://poser.pugx.org/apie/laravel-apie/v/unstable)](https://packagist.org/packages/apie/laravel-apie) [![License](https://poser.pugx.org/apie/laravel-apie/license)](https://packagist.org/packages/apie/laravel-apie) [![PHP Composer](https://apie-lib.github.io/projectCoverage/coverage-laravel-apie.svg)](https://apie-lib.github.io/projectCoverage/laravel-apie/index.html)  

[![PHP Composer](https://github.com/apie-lib/laravel-apie/actions/workflows/php.yml/badge.svg?event=push)](https://github.com/apie-lib/laravel-apie/actions/workflows/php.yml)

This package is part of the [Apie](https://github.com/apie-lib) library.
The code is maintained in a monorepo, so PR's need to be sent to the [monorepo](https://github.com/apie-lib/apie-lib-monorepo/pulls)

## Documentation
This package is Apie's Laravel adapter, the counterpart of `apie/apie-bundle` on the
Symfony side. It is required in a Laravel application together with the individual
`apie/*` packages you want to use (e.g. `apie/core`, `apie/rest-api`, `apie/maker`).

```bash
composer require apie/laravel-apie
```

Because of the `extra.laravel.providers` entry in its `composer.json`, Laravel's
package auto-discovery registers `Apie\LaravelApie\ApieServiceProvider` automatically.
This provider in turn registers the generated per-package Service Providers (such as
`Apie\Core\CoreServiceProvider`, `Apie\Maker\MakerServiceProvider`, or
`Apie\RestApi\RestApiServiceProvider`) that `apie/service-provider-generator` produces
from each package's framework-agnostic YAML service definitions via
`bin/update-service-provider`; only the Service Providers for packages you actually
required are loaded. `ApieServiceProvider` also wires Laravel-specific concerns the
YAML files cannot express directly: cache, locking, queue, session, CSRF, and
authenticated-user wrappers around Laravel's own services, plus an `ApieErrorRenderer`
and exception `Handler` integration. Publish `Apie\LaravelApie\Config\LaravelConfiguration`
to customize these bindings; domain objects, actions, and datalayers themselves stay
framework-agnostic and are testable without booting Laravel.

Unlike `apie/apie-bundle`, which loads the YAML files straight into the Symfony
container, this package never reads the YAML files at runtime — it consumes the
already-generated PHP Service Provider classes, which keeps Laravel bootstrapping fast
and avoids a YAML parser dependency in production.
