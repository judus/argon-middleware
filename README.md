# Argon Middleware

[![PHP](https://img.shields.io/badge/php-8.2+-blue)](https://www.php.net/)
[![Build](https://github.com/judus/argon-middleware/actions/workflows/php.yml/badge.svg)](https://github.com/judus/argon-middleware/actions)
[![codecov](https://codecov.io/gh/judus/argon-middleware/branch/master/graph/badge.svg)](https://codecov.io/gh/judus/argon-middleware)
[![Psalm Level](https://shepherd.dev/github/judus/argon-middleware/coverage.svg)](https://shepherd.dev/github/judus/argon-middleware)
[![Latest Version](https://img.shields.io/packagist/v/maduser/argon-middleware.svg)](https://packagist.org/packages/maduser/argon-middleware)
[![Downloads](https://img.shields.io/packagist/dt/maduser/argon-middleware.svg)](https://packagist.org/packages/maduser/argon-middleware)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

`maduser/argon-middleware` provides the PSR-15 middleware pipeline
infrastructure for the Argon HTTP stack. It builds request-handler chains,
resolves middleware through the container, and stores compiled pipelines through
the configured pipeline store.

## Installation

```bash
composer require maduser/argon-middleware
```

## Service Provider

Register `MiddlewarePipelineServiceProvider` in an Argon container:

```php
use Maduser\Argon\Middleware\Provider\MiddlewarePipelineServiceProvider;

$container->register(MiddlewarePipelineServiceProvider::class);
```

By default, tagged middleware is loaded from the `middleware.http` tag. The tag
can be changed through the `middleware.tag` container parameter before the
provider is registered.

## Scope

This package owns middleware loading, resolving, dispatching, and pipeline
storage. It does not match routes, convert controller return values, or format
error responses.

## Quality Gate

```bash
composer check
```
