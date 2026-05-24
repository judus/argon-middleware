# Changelog

## [Unreleased]

## [1.0.0] - 2026-05-24

- Restored the local quality gate across PHPUnit, Psalm, and PHPCS.
- Declared the package PHP runtime constraint explicitly as `^8.2`.
- Removed default response-formatting middleware, the placeholder dispatcher, and the ResultContext API.
- Removed the unused Argon Support runtime dependency.
- Added publishable Composer package metadata.
- Renamed the request handler service provider to `MiddlewarePipelineServiceProvider`.
