# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 2.0.0
### Added
- Added GitHub actions;
- Added types where possible;
### Changed
- Updated composer packages
- Updated composer.json (#37, #38);
### Removed
- Dropped support for old PHP versions;
- Dropped Travis CI;

## 1.1.2
 ### Changed
  - Replaced `array_key_exists` with `property_exists` for compatibility with
    PHP 7.4

## 1.1.1
### Changed
 - Allow compatibility with guzzlehttp 7.0 in composer json & added tests to verify this

## 1.1.0
### Added
- Support for "Authenticated Users" feature: `publishToUsers`, `generateToken` and `deleteUser`
### Changed
- `publish` renamed to `publishToInterests` (`publish` method deprecated).

## 1.0.0 
### Added
 - Changelog for GA release
