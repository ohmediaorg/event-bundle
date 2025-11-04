# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [[abc84ff](https://github.com/ohmediaorg/event-bundle/commit/abc84ff5c581a93afc22f3687064b16990600eb1)] - 2025-08-20

### Added

### Changed

### Fixed

- ensure tags are removed from all UIs if not enabled

## [[935a0dd](https://github.com/ohmediaorg/event-bundle/commit/935a0dd56d5cacd021a1a84418ae5d79bc2e7188)] - 2025-08-20

### Added

- EventTag entity for Event categorization

### Changed

### Fixed

## [[c31f703](https://github.com/ohmediaorg/event-bundle/commit/c31f7033a47bb9c6b7cb05726182f948fb518f5a)] - 2025-05-06

The dynamic event template must now be configured through the `page_template`
bundle parameter as opposed to implementing a template of a specific name or
placing the `events()` shortcode.

### Added

- config value for `page_template` to denote the dynamic event page

### Changed

- `events()` demoted to a regular Twig function

### Fixed
