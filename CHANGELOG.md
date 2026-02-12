# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.5.0 - UNRELEASED]

### Changed

- Switched to [Mago](https://mago.carthage.software/) for formatting and analysis

### Added

- Added a `Log::append()` method to allow attaching multiple entries, without using `LogList`
- Added `ToggledWriter` to enable or disable log writing at runtime

## [0.4.0]

### Added

- Added `LogList` to attach multiple entries to a single key

## [0.3.0]

### Changed

- LoggerWriter provides safer info/error message

## [0.2.0]

### Changed

- LogResponseError sets the error to the HTTP reason phrase
- LoggerWriter uses PSR-3 interpolation, and passes the context
- Output namespace changed to Writer

## [0.1.0]

- Initial release
