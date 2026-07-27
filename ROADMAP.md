# ROADMAP.md

> **Project:** pcmd
>
> **Version:** 1.0
>
> This roadmap is the implementation plan for pcmd.
>
> It is intended to be followed sequentially by both human contributors and AI coding agents.
>
> Each phase builds upon previous phases.
>
> Unless explicitly stated otherwise, **a phase is considered complete only when all acceptance criteria pass and all tests are green.**

---

# 1. Project Foundation

## Goal

Create the initial project skeleton.

This phase establishes the repository structure, coding standards, tooling, and development workflow.

No application logic should be implemented yet.

---

## Why

A clean foundation reduces technical debt later.

Every future phase assumes this structure exists.

---

## Tasks

- Initialize Git repository
- Create Composer project
- Configure PSR-4 autoloading
- Configure PHPStan
- Configure PHPUnit
- Configure Pint (or PHP-CS-Fixer)
- Configure EditorConfig
- Configure GitHub Actions
- Configure Dependabot
- Create README.md
- Create LICENSE
- Create CONTRIBUTING.md
- Create CHANGELOG.md
- Create SECURITY.md
- Create CODE_OF_CONDUCT.md
- Create `.gitignore`

---

## Repository Layout

```
src/
tests/
docs/
resources/
scripts/
bin/
```

---

## Coding Standard

- PSR-12
- strict_types=1
- constructor property promotion
- readonly where applicable
- typed properties
- typed return values
- no mixed unless unavoidable

---

## Deliverables

- Empty repository structure
- CI passing
- Composer installs successfully
- Tests execute successfully

---

## Acceptance Criteria

- `composer install` succeeds
- `composer test` succeeds
- `composer analyse` succeeds
- GitHub Actions pass
- No warnings

---

## Out of Scope

- CLI
- Commands
- Configuration
- Environment detection

---

# 2. CLI Bootstrap

## Goal

Create the executable.

At the end of this phase

```
pcmd
```

should launch successfully.

---

## Tasks

Create

```
bin/pcmd
```

Create

```
Application.php
```

Parse

```
argv
```

Create minimal terminal output.

---

## Required Features

Running

```
pcmd
```

prints

```
pcmd

Version 0.1
```

Running

```
pcmd --version
```

prints version.

Running

```
pcmd --help
```

prints placeholder help.

---

## Deliverables

Executable CLI.

---

## Acceptance Criteria

```
pcmd
```

works.

```
pcmd --help
```

works.

```
pcmd --version
```

works.

---

## Out of Scope

Configuration.

Commands.

Discovery.

---

# 3. Configuration System

## Goal

Implement immutable configuration.

---

## Tasks

Create

```
Config

ConfigLoader

Defaults
```

Support

```
~/.pcmd/config.php
```

Merge

```
defaults

↓

user config

↓

CLI overrides
```

---

## Required Features

Typed getters

```
bool()

int()

string()

array()

get()

has()
```

---

## Error Handling

Malformed configuration

↓

ConfigurationException

---

## Deliverables

Fully working configuration loader.

---

## Acceptance Criteria

Missing config

↓

defaults

Malformed config

↓

error

Valid config

↓

loads correctly

---

## Out of Scope

Caching.

Plugins.

---

# 4. Dependency Injection

## Goal

Build lightweight dependency management.

Avoid global state.

Avoid singletons.

---

## Tasks

Create

```
ContainerInterface

Container

ServiceProvider

```

Support

- singleton
- transient
- factory

---

## Design

Container exists only during execution.

No static access.

---

## Services

Initially register

```
Configuration

Logger

Filesystem

Process

Terminal
```

---

## Deliverables

Working DI container.

---

## Acceptance Criteria

Services resolve correctly.

Dependencies inject correctly.

Circular dependencies detected.

---

## Out of Scope

Laravel container.

---

# 5. Terminal I/O

## Goal

Build terminal abstraction.

No command should directly call

```
echo
```

---

## Tasks

Create

```
Terminal

Output

Input

ProgressBar

Spinner

Table

Tree
```

---

## Required Features

Support

```
info()

warn()

error()

success()

line()

newline()
```

---

Support

```
ask()

confirm()

choice()

secret()
```

---

Support

ANSI detection.

---

Support

Terminal width.

---

## Deliverables

Reusable terminal layer.

---

## Acceptance Criteria

Progress works.

Tables render.

Questions work.

No ANSI corruption.

---

## Out of Scope

Discovery.

Commands.

---

# 6. Exception System

## Goal

Create consistent exception hierarchy.

---

## Tasks

Create

```
PcmdException
```

Derived exceptions

```
ConfigurationException

DiscoveryException

ValidationException

BootstrapException

ExecutionException

EnvironmentException

ProcessException

FilesystemException
```

---

## Responsibilities

Every subsystem throws typed exceptions.

Never generic Exception.

---

## Deliverables

Complete exception tree.

---

## Acceptance Criteria

Every subsystem has its own exception.

Exceptions render correctly.

---

# 7. Filesystem Layer

## Goal

Create filesystem abstraction.

---

## Tasks

Implement

```
Filesystem

Path

DirectoryWalker

TemporaryDirectory

TemporaryFile
```

---

## API

Support

```
read

write

move

copy

delete

exists

mkdir

walk

glob
```

---

Normalize paths.

---

Avoid PHP warnings.

Throw exceptions.

---

## Deliverables

Complete filesystem service.

---

## Acceptance Criteria

Cross-platform.

Fully testable.

100% unit tests.

---

## Out of Scope

Discovery.

---

# 8. Process Layer

## Goal

Implement external process execution.

---

## Tasks

Create

```
ProcessManager

ProcessBuilder

ProcessResult
```

---

## Required Features

```
run

capture

stream

cwd

timeout

env
```

---

Support

stdout

stderr

exit code

---

## Deliverables

Reliable process execution.

---

## Acceptance Criteria

Git command works.

PHP command works.

Timeout works.

---

## Out of Scope

Parallel execution.

---

# 9. Logging System

## Goal

Implement logging infrastructure.

---

## Tasks

Create

```
LoggerInterface

FileLogger

NullLogger

LoggerFactory
```

---

## Features

Support

```
debug

info

notice

warning

error

critical
```

---

Mask secrets.

Rotate logs.

---

Store logs

```
~/.pcmd/logs/
```

---

## Deliverables

Working logger.

---

## Acceptance Criteria

Logs written correctly.

Log levels respected.

Secrets hidden.

---

# 10. Environment Detection

## Goal

Detect project environments.

---

## Tasks

Create

```
Environment

EnvironmentManager

EnvironmentDetectorInterface
```

---

Implement

```
GenericDetector

LaravelDetector
```

Future

```
SymfonyDetector

WordPressDetector
```

---

## Detection Strategy

Start

```
cwd
```

Walk upward.

Search for markers.

---

Laravel markers

```
artisan

bootstrap/app.php

vendor/autoload.php
```

---

Generic

Always succeeds.

Lowest priority.

---

## Deliverables

Environment detection subsystem.

---

## Acceptance Criteria

Laravel detected correctly.

Generic detected correctly.

Project root resolved correctly.

Nested directories work.

Multiple detector conflicts handled correctly.

---

## Out of Scope

Framework bootstrap.

Command discovery.

Command execution.

---

## Milestone 1 Complete

After completing the first ten phases, the project should provide:

- A production-ready repository structure
- A working CLI executable
- Immutable configuration
- Dependency injection
- Terminal abstraction
- Typed exception hierarchy
- Filesystem abstraction
- Process execution
- Logging infrastructure
- Automatic environment detection

At this point, **no commands exist yet**, but the core infrastructure required to build the rest of the system is in place.

---

# 11. Command Discovery

## Goal

Automatically discover every available command.

No manual registration.

No configuration.

No rebuilding.

---

## Why

Adding a new command should be as simple as dropping a PHP file into the correct directory.

The user should never need to tell pcmd that a command exists.

---

## Tasks

Create

```
CommandDiscovery

DirectoryScanner

MetadataReader

DiscoveryResult
```

---

Search locations

```
~/.pcmd/commands/general/

~/.pcmd/commands/<environment>/
```

Future

```
plugins/*/commands/
```

---

Perform recursive scanning.

Ignore

```
.*

*.bak

*.disabled

*.tmp
```

---

Build metadata objects.

Never execute commands.

---

## Deliverables

Automatic command discovery.

---

## Acceptance Criteria

- General commands discovered
- Laravel commands discovered
- Invalid files skipped
- Duplicate detection works
- Discovery completes without executing command code

---

## Out of Scope

Registry.

Execution.

---

# 12. Metadata Parser

## Goal

Read command metadata without executing the command.

---

## Why

Listing commands should not bootstrap Laravel or execute PHP business logic.

---

## Tasks

Design a metadata format.

Read

- name
- aliases
- description
- tags
- examples
- environment
- hidden flag

without loading runtime logic.

---

## Strategy

Separate metadata from execution.

For example

```
metadata

↓

callable
```

rather than executing the callable.

---

## Validation

Ensure

- valid names
- unique aliases
- supported environment
- required fields

---

## Deliverables

Metadata parser.

---

## Acceptance Criteria

Metadata parsed correctly.

Invalid metadata rejected.

Zero framework bootstrapping.

---

# 13. Command Registry

## Goal

Store discovered command metadata.

---

## Tasks

Create

```
CommandRegistry

CommandMetadata

AliasIndex

SearchIndex
```

---

Support

```
register()

find()

exists()

search()

commands()

aliases()
```

---

Indexes

Maintain separate indexes for

- canonical names
- aliases
- tags

---

## Duplicate Detection

Reject duplicate

- command names
- aliases

Provide meaningful diagnostics.

---

## Deliverables

Fast in-memory registry.

---

## Acceptance Criteria

Lookup is efficient.

Alias resolution works.

Duplicate detection works.

Registry remains immutable after startup.

---

# 14. Command Resolver

## Goal

Convert CLI input into a resolved command.

---

## Tasks

Create

```
CommandResolver

ResolvedCommand
```

---

Pipeline

```
Input

↓

Normalize

↓

Alias Resolution

↓

Registry Lookup

↓

Environment Validation

↓

Argument Parsing

↓

Option Parsing

↓

ResolvedCommand
```

---

## Validation

Detect

- unknown command
- unsupported environment
- invalid arguments
- invalid options

---

## Deliverables

Resolved command object.

---

## Acceptance Criteria

Aliases work.

Normalization works.

Helpful errors produced.

---

# 15. Command Loader

## Goal

Load a command only when it is needed.

---

## Tasks

Create

```
CommandLoader
```

---

Responsibilities

- include PHP file
- validate returned definition
- create executable object

---

Loading Strategy

Lazy loading only.

Never preload every command.

---

## Validation

Reject

- invalid return types
- malformed definitions
- duplicate metadata

---

## Deliverables

Working lazy loader.

---

## Acceptance Criteria

Only requested command loaded.

Load failures handled gracefully.

---

# 16. Context System

## Goal

Provide every command with a unified runtime API.

---

## Tasks

Create

```
Context

ContextFactory

RuntimeState
```

---

Inject

- configuration
- filesystem
- logger
- process manager
- terminal
- environment
- resolved command

---

## Rule

Commands receive

```php
Context $ctx
```

Nothing else.

---

## Deliverables

Complete Context API.

---

## Acceptance Criteria

Commands access all services.

No globals.

No service locator.

Context destroyed after execution.

---

# 17. Command Executor

## Goal

Execute commands safely.

---

## Tasks

Create

```
CommandExecutor

ExitCodeResolver
```

---

Execution flow

```
Context

↓

Before Hooks

↓

Command

↓

After Hooks

↓

Cleanup

↓

Exit Code
```

---

Catch

- exceptions
- validation failures
- runtime errors

---

Convert results into exit codes.

---

## Deliverables

Reliable executor.

---

## Acceptance Criteria

Commands execute correctly.

Exceptions formatted consistently.

Resources cleaned up.

---

# 18. Hook System

## Goal

Allow shared lifecycle behavior.

---

## Tasks

Implement

```
HookManager

BeforeHook

AfterHook

ShutdownHook
```

---

Support

```
before

after

shutdown
```

Future

```
boot

pre-bootstrap
```

---

## Responsibilities

Hooks may

- initialize services
- perform logging
- measure execution
- perform cleanup

---

## Deliverables

Hook infrastructure.

---

## Acceptance Criteria

Hooks execute in deterministic order.

Hook failures handled properly.

---

# 19. Cache System

## Goal

Reduce startup time.

---

## Tasks

Create

```
CacheManager

DiscoveryCache

MetadataCache
```

---

Cache

- discovered commands
- metadata
- detector results

---

Invalidate cache when

- command added
- command removed
- metadata changed
- plugin changed

---

Store under

```
~/.pcmd/cache/
```

---

## Deliverables

Persistent cache.

---

## Acceptance Criteria

Second startup faster than first.

Cache invalidates correctly.

Corrupt cache automatically rebuilt.

---

# 20. Built-in Commands

## Goal

Implement the commands that ship with pcmd.

These commands are framework-independent and always available.

---

## Initial Commands

```
help

list

version

doctor

cache:clear

cache:rebuild

config:show

about
```

---

## Responsibilities

### help

Display command documentation.

---

### list

List available commands.

Support

```
--tag

--hidden

--environment
```

---

### version

Display version information.

---

### doctor

Run diagnostics.

Check

- configuration
- permissions
- cache
- command directories
- plugins

---

### cache:clear

Delete all cache.

---

### cache:rebuild

Rebuild discovery cache.

---

### config:show

Display active configuration.

---

### about

Display project information.

---

## Deliverables

Core command set.

---

## Acceptance Criteria

All built-in commands work without Laravel.

Help output generated automatically.

List reflects discovered commands.

Doctor detects common problems.

---

## Milestone 2 Complete

After Phase 20, pcmd has become a functional command runner.

It now supports:

- Automatic command discovery
- Metadata parsing
- Command registry
- Command resolution
- Lazy command loading
- Runtime Context
- Command execution
- Lifecycle hooks
- Discovery caching
- Built-in management commands

At this stage, the core engine is complete. The remaining phases focus on framework integration, plugin support, testing, optimization, packaging, and release readiness.

---

# 21. General Command API

## Goal

Provide a stable public API for writing framework-independent commands.

These commands should work in any directory, regardless of whether a framework is detected.

---

## Tasks

Finalize the public Command API.

Implement

```
CommandDefinition

Argument

Option

Validator

Context
```

---

## Features

Support

- positional arguments
- named options
- validation
- examples
- aliases
- hidden commands
- tags
- notes

---

## Documentation

Every public API must be documented.

No undocumented public methods.

---

## Deliverables

Stable General Command API.

---

## Acceptance Criteria

- All public APIs documented
- Example commands function correctly
- Backward compatibility guaranteed within major version
- No breaking changes after v1.0

---

# 22. Laravel Adapter

## Goal

Integrate Laravel without coupling the core to Illuminate.

---

## Tasks

Implement

```
LaravelAdapter

LaravelBootstrapper

LaravelEnvironment

LaravelContainerBridge
```

---

## Bootstrap Sequence

```
Locate Root

↓

vendor/autoload.php

↓

bootstrap/app.php

↓

HTTP/Console Kernel

↓

Application Ready
```

---

## Services

Expose

- Container
- Artisan
- Database
- Cache
- Queue
- Events
- Config
- Storage

through the adapter.

---

## Deliverables

Complete Laravel adapter.

---

## Acceptance Criteria

- Laravel 11+ supported
- Container accessible
- Eloquent usable
- Artisan callable
- Core contains no Illuminate dependencies

---

# 23. Laravel Command API

## Goal

Allow commands to leverage Laravel features while preserving the same developer experience.

---

## Tasks

Extend Context.

Provide

```php
$ctx->laravel()
```

---

## Features

Support

- Artisan calls
- Database transactions
- Cache
- Queue
- Events
- Storage
- Environment detection

---

## Best Practices

Commands should use

```php
$ctx->laravel()
```

instead of bootstrapping Laravel themselves.

---

## Deliverables

Stable Laravel command API.

---

## Acceptance Criteria

Example commands

- query Eloquent
- dispatch jobs
- clear cache
- call Artisan

without additional setup.

---

# 24. Helper Library System

## Goal

Provide reusable helper classes shared by commands.

---

## Tasks

Implement helper loading.

Search

```
~/.pcmd/helpers/
```

---

## Examples

```
Database.php

Json.php

Images.php

Http.php

Git.php
```

---

## Rules

Helpers

- are optional
- are reusable
- contain no command metadata
- may be imported by any command

---

## Deliverables

Shared helper infrastructure.

---

## Acceptance Criteria

Commands can reuse helper libraries without duplication.

---

# 25. Plugin Architecture (v1)

## Goal

Allow external packages to extend pcmd.

---

## Tasks

Design plugin manifest.

Implement

```
PluginManager

PluginLoader

PluginManifest
```

---

## Plugin Capabilities

Plugins may provide

- commands
- helpers
- environment detectors
- adapters
- hooks

---

## Plugin Directory

```
~/.pcmd/plugins/
```

---

## Deliverables

Working plugin loader.

---

## Acceptance Criteria

Plugins install without modifying core.

Plugins discovered automatically.

Plugins isolated from one another.

---

# 26. Testing Infrastructure

## Goal

Create a robust automated testing environment.

---

## Tasks

Configure

- PHPUnit
- Test helpers
- Fixtures
- Temporary directories
- Snapshot testing (where appropriate)

---

## Test Categories

```
Unit

Integration

End-to-End

Regression
```

---

## Deliverables

Complete testing framework.

---

## Acceptance Criteria

Every subsystem has automated tests.

CI executes all tests.

---

# 27. Comprehensive Unit Tests

## Goal

Achieve high confidence in every subsystem.

---

## Coverage Targets

Target

```
90%+
```

line coverage for critical infrastructure.

---

## Priority Areas

- Discovery
- Registry
- Resolver
- Filesystem
- Process
- Context
- Configuration

---

## Requirements

Every public class

must have

- constructor tests
- success tests
- failure tests
- edge-case tests

---

## Deliverables

Extensive unit test suite.

---

## Acceptance Criteria

All unit tests pass.

Coverage targets achieved.

---

# 28. Integration & End-to-End Tests

## Goal

Verify complete application behavior.

---

## Tasks

Create fixture projects.

Examples

```
Generic PHP

Laravel

Broken Laravel

Nested Laravel

Duplicate Commands
```

---

## Execute Real CLI

Test

```
pcmd help

pcmd list

pcmd doctor

pcmd cache:clear
```

---

## Deliverables

Reliable integration testing.

---

## Acceptance Criteria

Commands behave correctly across supported environments.

---

# 29. Performance Optimization

## Goal

Optimize startup speed and memory usage.

---

## Tasks

Benchmark

- startup
- discovery
- registry lookup
- cache
- execution

---

## Optimize

- lazy loading
- object allocation
- filesystem access
- metadata parsing

---

## Targets

Cold startup

< 100 ms (typical machine)

Warm startup

< 30 ms

Memory

As low as reasonably possible.

---

## Deliverables

Performance report.

Optimized implementation.

---

## Acceptance Criteria

Performance targets consistently met.

---

# 30. Documentation & Release Preparation

## Goal

Prepare pcmd for its first public release.

---

## Tasks

Review all documentation.

Ensure consistency between

- AGENTS.md
- SPECIFICATION.md
- ARCHITECTURE.md
- COMMAND_API.md
- DIRECTORY_STRUCTURE.md
- ROADMAP.md

---

Generate

- API documentation
- example commands
- migration guides
- contributor guide

---

## Package

Prepare

- Composer package
- GitHub release
- release notes
- changelog

---

## Version

Release

```
v1.0.0
```

---

## Acceptance Criteria

- Documentation complete
- CI green
- Tests green
- Static analysis clean
- No known critical issues
- Ready for public use

---

## Milestone 3 Complete

At the completion of Phase 30, **pcmd v1.0** is feature-complete.

It includes:

- Stable command API
- Laravel integration
- Helper libraries
- Plugin support
- Comprehensive testing
- Performance optimizations
- Complete documentation
- Public release artifacts

The remaining roadmap (Phases 31+) will focus on expanding the ecosystem, supporting additional frameworks, improving the plugin system, and adding advanced developer tooling without introducing breaking changes to the v1 API.

---

# 31. Symfony Adapter

## Goal

Add first-class Symfony support.

The architecture should prove that supporting additional frameworks requires no changes to the core.

---

## Tasks

Implement

```
SymfonyDetector

SymfonyAdapter

SymfonyBootstrapper

SymfonyContainerBridge
```

---

## Detection

Detect Symfony using markers such as

```
bin/console

config/

vendor/autoload.php
```

---

## Services

Expose

- Container
- Console Application
- Doctrine (if installed)
- Cache
- Event Dispatcher
- Configuration

through the adapter.

---

## Acceptance Criteria

- Symfony projects detected correctly
- Commands execute inside Symfony applications
- No core modifications required

---

# 32. WordPress Adapter

## Goal

Support WordPress projects.

---

## Tasks

Implement

```
WordPressDetector

WordPressAdapter

WordPressBootstrapper
```

---

## Detection

Markers

```
wp-config.php

wp-content/

wp-includes/
```

---

## Services

Expose

- Database
- Options
- Plugins
- Themes
- Uploads
- Hooks

---

## Acceptance Criteria

- WordPress projects detected
- Commands execute successfully
- Bootstrap isolated inside adapter

---

# 33. Framework SDK

## Goal

Provide a reusable SDK for implementing new framework adapters.

---

## Tasks

Create abstract base classes

```
AbstractFrameworkAdapter

AbstractEnvironmentDetector

FrameworkContext
```

---

## Documentation

Document the minimum implementation required for a new adapter.

---

## Deliverables

Developer SDK for framework integrations.

---

## Acceptance Criteria

A new adapter can be implemented with minimal boilerplate.

---

# 34. Plugin SDK

## Goal

Allow third-party developers to build plugins without depending on internal implementation details.

---

## Tasks

Publish

```
PluginInterface

PluginManifest

PluginContext

PluginInstaller
```

---

## Features

Plugins may register

- commands
- helpers
- hooks
- detectors
- adapters
- services

---

## Documentation

Provide complete plugin development guide.

---

## Acceptance Criteria

Sample plugin created using only public APIs.

---

# 35. Package Manager

## Goal

Allow users to install and update command packs and plugins.

---

## Tasks

Create built-in commands

```
plugin:install

plugin:remove

plugin:update

plugin:list

plugin:search
```

Future

```
command:install

command:update
```

---

## Sources

Initially support

- Git repositories
- Local directories

Future

- Official registry

---

## Acceptance Criteria

Plugins install without manual file copying.

---

# 36. Update System

## Goal

Provide self-update capabilities.

---

## Tasks

Implement

```
self:update

self:check

self:rollback
```

---

## Features

- Version checking
- Integrity verification
- Rollback support

---

## Acceptance Criteria

Updates are reliable and recoverable.

---

# 37. Cross-Platform Support

## Goal

Ensure pcmd behaves consistently across major operating systems.

---

## Supported Platforms

- Linux
- macOS
- Windows

---

## Tasks

Test

- path handling
- process execution
- ANSI output
- filesystem behavior
- terminal input

---

## Acceptance Criteria

Entire test suite passes on all supported platforms.

---

# 38. Packaging & Distribution

## Goal

Make installation straightforward.

---

## Distribution Methods

Support

- Composer
- PHAR
- Standalone binaries (future)

---

## Tasks

Automate release artifacts.

Generate

- checksums
- signatures
- release archives

---

## Acceptance Criteria

Installation documented and reproducible.

---

# 39. Security Hardening

## Goal

Improve reliability and security without sacrificing usability.

---

## Tasks

Review

- command loading
- plugin loading
- filesystem access
- process execution
- configuration parsing

---

## Features

Add

- plugin signature verification (future)
- integrity checks
- safer temporary file handling

---

## Acceptance Criteria

Security review completed.

Critical findings resolved.

---

# 40. Ecosystem Launch

## Goal

Launch the first official pcmd ecosystem.

---

## Official Command Packs

Examples

```
Laravel Essentials

Git Utilities

JSON Tools

Filesystem Tools

Image Utilities

Database Utilities

Docker Tools
```

---

## Official Plugins

Examples

```
GitHub

GitLab

Docker

AWS

DigitalOcean

Cloudflare
```

---

## Documentation

Publish

- website
- tutorials
- API reference
- plugin guide
- framework guide

---

## Acceptance Criteria

Developers can install, extend, and contribute to the pcmd ecosystem with minimal onboarding.

---

## Milestone 4 Complete

At the completion of Phase 40, **pcmd has evolved from a standalone CLI into a complete extensible platform**.

It now provides:

- Multi-framework support
- Public plugin SDK
- Framework SDK
- Package management
- Update system
- Cross-platform compatibility
- Secure distribution
- An initial ecosystem of official command packs and plugins

From this point forward, development should primarily focus on expanding capabilities while preserving backward compatibility with the v1 public APIs.

---

# 41. Future Framework Adapters

## Goal

Allow the ecosystem to support additional frameworks without modifying the core.

---

## Candidate Adapters

Examples

```
Drupal

Magento

CodeIgniter

CakePHP

Yii

Slim

Laminas
```

---

## Tasks

Ensure every adapter only requires

```
Detector

Bootstrapper

Adapter
```

No core modifications.

---

## Acceptance Criteria

A new framework can be added entirely as a plugin or package.

---

# 42. Command Marketplace

## Goal

Create a centralized repository of reusable command packs.

---

## Vision

Developers should be able to install command collections with a single command.

Example

```
pcmd command:install laravel-tools
```

---

## Features

Marketplace should support

- searching
- installation
- updates
- ratings
- documentation
- verified publishers

---

## Acceptance Criteria

Marketplace protocol documented.

Official registry available.

---

# 43. Remote Command Packs

## Goal

Allow commands to be installed from remote repositories.

---

## Supported Sources

Initially

```
GitHub

GitLab

Local Git repositories
```

Future

```
Official Registry

Private Registries
```

---

## Tasks

Implement

```
command:install

command:update

command:remove
```

---

## Acceptance Criteria

Remote command packs install safely.

Updates preserve user data.

---

# 44. AI Integration

## Goal

Make pcmd AI-friendly.

---

## Vision

AI assistants should understand pcmd projects without reverse engineering.

---

## Tasks

Provide

```
AGENTS.md

SPECIFICATION.md

ARCHITECTURE.md

COMMAND_API.md

DIRECTORY_STRUCTURE.md

ROADMAP.md
```

as machine-readable project documentation.

---

Future commands

```
pcmd ai:index

pcmd ai:docs

pcmd ai:verify
```

may generate AI context automatically.

---

## Acceptance Criteria

An AI coding agent can understand and extend the project using only the documentation.

---

# 45. Telemetry (Optional)

## Goal

Provide optional anonymous usage metrics.

---

## Principles

Telemetry is

- disabled by default
- opt-in only
- anonymous
- transparent

---

## Data Collection

Possible metrics

- startup duration
- command execution time
- plugin usage
- framework usage

Never collect

- source code
- command arguments
- environment variables
- secrets
- personal information

---

## Acceptance Criteria

Telemetry can be enabled or disabled at any time.

Core functionality never depends on telemetry.

---

# 46. Version 2.0 Planning

## Goal

Prepare the architectural vision for the next major version.

---

## Candidate Features

Examples

```
Async command execution

Parallel task scheduler

Native TUI framework

Background workers

Interactive dashboards

Remote execution

Workspace management

Dependency graph visualization

Incremental discovery

Command sandboxing

Plugin signing

HTTP client abstraction
```

---

## Compatibility

Version 2.0 should preserve the design philosophy established in Version 1.0 wherever practical.

Breaking changes must be documented through ADRs and accompanied by migration guides.

---

## Acceptance Criteria

A formal v2 roadmap is published before any breaking implementation begins.

---

# Final Milestone

At the completion of the roadmap, **pcmd** should be:

- A standalone developer CLI platform
- Framework-agnostic
- Extensible through plugins
- AI-friendly by design
- Production-ready
- Cross-platform
- Highly testable
- Backward compatible within major versions
- Easy to contribute to
- Capable of supporting thousands of reusable commands across multiple frameworks

The long-term vision is for **pcmd** to become for PHP development what Git is for version control: a small, dependable core with a rich ecosystem built around stable public APIs.