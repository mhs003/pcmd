# ARCHITECTURE.md

> **Project:** pcmd
>
> **Version:** 1.0
>
> This document defines the internal architecture of pcmd.
>
> While `SPECIFICATION.md` defines **what** the software does,
> this document defines **how** it is implemented.
>
> This document is intended for contributors and AI coding agents.
>
> It is the authoritative architectural reference.

---

## Implementation Status

This architecture document describes the **target design** for v1.0. Below is the current implementation state of each component.

| Component | Status | Notes |
|-----------|--------|-------|
| §3 CLI | ✅ Implemented | `src/CLI/` — ArgvParser, Input, Output |
| §3 Application | ✅ Implemented | `src/Application/` — orchestrates lifecycle |
| §3 Configuration | ✅ Implemented | `src/Configuration/` — Config, ConfigLoader, Defaults |
| §3 Environment Detector | ✅ Implemented | `src/Environment/` — GenericDetector, LaravelDetector |
| §3 Discovery | ✅ Implemented | `src/Discovery/` — CommandDiscovery, DirectoryScanner, DiscoveryCache (mtime-based) |
| §3 Registry | ✅ Implemented | `src/Registry/` — CommandRegistry, CommandMetadata (with arg/option defs) |
| §3 Resolver | ✅ Implemented | `src/Resolution/` — CommandResolver, ResolvedCommand, InputValidator |
| §3 Bootstrap | 🔶 Partial | LaravelAdapter bootstraps via vendor/autoload.php + bootstrap/app.php |
| §3 Context | ✅ Implemented | `src/Context/` — Context (with framework adapter support) |
| §3 Executor | ✅ Implemented | `src/Execution/` — CommandExecutor, CommandLoader, HookRunner |
| §3 Logger | ✅ Implemented | `src/Logging/` — Logger, NullLogger |
| §3 Cache | ✅ Implemented | `src/Discovery/DiscoveryCache` — mtime-based invalidation |
| §3 Process Manager | ✅ Implemented | `src/Process/` — ProcessManager, ProcessResult |
| §3 Filesystem | ✅ Implemented | `src/Filesystem/` — Filesystem, Path |
| §15 Framework Adapter | ✅ Implemented | FrameworkAdapterInterface with boot/shutdown/name; LaravelAdapter implements it |
| §16 Laravel Adapter | ✅ Implemented | LaravelAdapter with boot, ArtisanBridge, db/cache/config/queue/events/storage/version/environment; 6 example commands shipped |
| §19 Hook System | ✅ Implemented | HookRunner loads ~/.pcmd/hooks/{before,after,shutdown}.php |
| §20 Helper System | ✅ Implemented | HelperLoader loads ~/.pcmd/helpers/ on demand; $ctx->helper('name') access |
| §21 Plugin Architecture | ⬜ Not started | Directory exists; no loading |
| §21 Bundled Commands | ✅ Implemented | resources/commands/ with general + Laravel examples; fallback discovery (user commands win); publish:commands built-in |
| §22 Error Handling | ✅ Implemented | Typed exceptions, centralized formatter, --debug mode with stack traces and SQL context |

The dependency direction rules (§1, §22) are followed throughout the implemented code.

---

The architecture of pcmd is built around one fundamental idea:

> **The core application should know as little as possible about the environments it supports.**

The executable itself should never become "Laravel-aware", "Symfony-aware", or "WordPress-aware".

Instead:

```
pcmd
        │
        ▼
Environment Adapter
        │
        ▼
Framework
```

Never

```
pcmd
        │
        ▼
Laravel
```

The framework is an extension.

Not part of the core.

---

## Design Goals

The architecture prioritizes

- simplicity
- extensibility
- maintainability
- portability
- low startup cost

over feature count.

---

## Core Principle

Everything should have one responsibility.

Every subsystem should solve exactly one problem.

---

## Dependency Direction

Dependencies always flow downward.

```
CLI

↓

Application

↓

Environment

↓

Discovery

↓

Execution

↓

Command
```

Never upward.

---

## Forbidden Architecture

Never allow

```
Command

↓

Discovery
```

or

```
Command

↓

Environment Detector
```

Commands should receive services through Context.

Never perform global lookups.

---

# 2. High-Level Architecture

The application consists of independent subsystems.

```
                CLI

                 │

      Argument Parser

                 │

          Application

                 │

      Configuration Loader

                 │

    Environment Detector

                 │

      Command Discovery

                 │

      Command Registry

                 │

     Command Resolver

                 │

 Context Construction

                 │

    Framework Adapter

                 │

     Command Executor

                 │

       User Command
```

Every box is replaceable.

Every box has one responsibility.

---

## Benefits

This design allows

- independent testing
- independent replacement
- independent optimization

without affecting other systems.

---

# 3. Core Components

The application consists of the following major components.

## CLI

Responsible for

- receiving user input
- initializing the application

Nothing else.

---

## Application

Coordinates everything.

The Application object never performs business logic.

It only orchestrates components.

---

## Configuration

Loads configuration.

Provides immutable configuration objects.

---

## Environment Detector

Determines

- Generic
- Laravel
- Symfony
- etc.

Never loads commands.

---

## Discovery

Discovers command files.

Nothing more.

---

## Registry

Stores command metadata.

Provides lookup.

---

## Resolver

Finds the requested command.

---

## Bootstrap

Bootstraps framework.

Only when required.

---

## Context

Provides runtime API.

---

## Executor

Runs commands.

Handles lifecycle.

---

## Logger

Records diagnostics.

---

## Cache

Stores discovery results.

---

## Process Manager

Runs external processes.

---

## Filesystem

Provides filesystem abstraction.

---

# 4. Boot Process

Application startup follows a fixed sequence.

```
main()

↓

CLI

↓

Configuration

↓

Environment Detection

↓

Command Discovery

↓

Registry

↓

Resolve Command

↓

Bootstrap

↓

Context

↓

Execution
```

This order is fixed.

Changing the order is considered an architectural change.

---

## Startup Rules

Configuration loads before everything.

Environment detection happens before discovery.

Bootstrap occurs after resolution.

Commands execute last.

---

## Failure Rules

If any stage fails

execution stops immediately.

No later stages execute.

---

# 5. CLI Layer

The CLI layer is intentionally tiny.

Responsibilities

- read argv
- read environment variables
- invoke Application

Nothing else.

The CLI layer should never know

- Laravel
- commands
- adapters
- discovery

---

Example

```
argv

↓

Argument Parser

↓

Application::run()
```

The CLI should be almost impossible to break.

---

# 6. Application Layer

The Application object coordinates every subsystem.

Pseudo flow

```
load config

↓

detect environment

↓

discover commands

↓

resolve command

↓

bootstrap adapter

↓

execute
```

The Application should never contain

filesystem logic

command logic

framework logic

logging implementation

cache implementation

These belong elsewhere.

---

## Responsibilities

Application owns

application lifecycle

Nothing else.

---

# 7. Configuration Architecture

Configuration is immutable.

Once loaded,

it never changes.

Commands receive read-only configuration.

---

Configuration sources

```
defaults

↓

config.php

↓

environment overrides

↓

CLI flags
```

Application receives one Config object.

Every subsystem receives the same Config.

Never reload configuration.

---

Benefits

No inconsistent state.

Easy testing.

---

# 8. Environment Detection

Environment detection is plugin-based.

The detector knows nothing about Laravel.

Instead

```
Environment Detector

↓

Detectors

↓

Laravel Detector

Symfony Detector

WordPress Detector
```

Each detector implements one interface.

Example

```php
interface EnvironmentDetector
{
    public function detect(string $directory): ?Environment;
}
```

Application loops through detectors.

First successful detector wins.

---

Detector responsibilities

Determine

- environment type

- project root

- metadata

Nothing else.

---

Detectors never

bootstrap frameworks

discover commands

load config

---

# 9. Command Discovery

Discovery scans command directories.

Responsibilities

Locate files.

Read metadata.

Build registry entries.

Nothing else.

---

Discovery never

executes commands

loads frameworks

creates contexts

---

Pipeline

```
directories

↓

recursive scan

↓

filter files

↓

parse metadata

↓

build registry

↓

cache
```

---

Discovery should be cache-aware.

When cache is valid

filesystem scan should be skipped.

---

Discovery should ignore

```
.*

*.bak

*.disabled

*.tmp
```

---

Errors inside one command

must not stop discovery.

---

# 10. Command Registry

The registry stores command metadata.

It never stores command instances.

Instead

```
Command Name

↓

Metadata

↓

File

↓

Lazy Load
```

Commands should only be loaded

when executed.

---

Metadata includes

- canonical name

- aliases

- description

- environment

- file path

- arguments

- options

---

Responsibilities

lookup

listing

search

validation

duplicate detection

---

Registry API

Conceptually

```
register()

find()

findAlias()

exists()

commands()

search()

remove()
```

The registry should never

execute commands

load PHP files

bootstrap frameworks

perform filesystem operations

It is purely an in-memory metadata index.

---

## Registry Lifetime

One registry exists per execution.

It is created during startup.

Destroyed after exit.

It should never be global.

Never be a singleton.

Never persist between executions.

---

## Performance

Registry lookups should be O(1) whenever practical.

Searching by alias should not require scanning every command.

Use indexed maps internally rather than linear searches.

---

# 11. Command Resolution

## Overview

The Command Resolver transforms user input into an executable command.

Input

```
pcmd search:reindex --queue
```

↓

Output

```
ResolvedCommand
```

The resolver never executes commands.

It only resolves.

---

## Responsibilities

The resolver

- normalizes names
- resolves aliases
- validates existence
- verifies environment compatibility
- verifies reserved names
- produces a ResolvedCommand object

---

## Resolution Pipeline

```
Raw Input

↓

Normalize

↓

Resolve Alias

↓

Lookup Registry

↓

Verify Environment

↓

Validate Arguments

↓

Return ResolvedCommand
```

---

## Normalization

The resolver converts

```
Search:ReIndex
```

into

```
search:reindex
```

Whitespace should be trimmed.

Case ignored.

---

## Alias Resolution

Aliases are resolved before lookup.

Example

```
reindex
```

↓

```
search:reindex
```

Aliases always resolve to canonical names.

---

## ResolvedCommand

A ResolvedCommand should contain

```
Canonical Name

Metadata

Arguments

Options

Command File

Environment

Framework Adapter
```

No PHP code has been executed yet.

---

## Resolver Independence

Resolver must never

- bootstrap Laravel
- load command files
- instantiate commands
- execute closures

---

# 12. Command Loader

## Overview

The loader converts a command file into an executable command definition.

Loading is lazy.

Commands are loaded only when needed.

---

## Why Lazy Loading?

Consider

```
800 commands
```

The user executes

```
json:pretty
```

Only

```
json:pretty
```

should be loaded.

Not

```
799
```

others.

---

## Responsibilities

Loader

- includes PHP file
- validates returned structure
- converts into CommandDefinition

---

## Validation

The loader validates

- returned value
- required fields
- option definitions
- argument definitions
- callable

Invalid commands never execute.

---

## Failure

Loading errors produce

```
Command Load Error

Command

products:repair

Reason

Invalid return value
```

---

## Security

Commands execute in-process.

No sandbox exists.

Loading trusted code is assumed.

---

# 13. Command Executor

## Overview

The executor owns the runtime lifecycle.

Once execution reaches this stage,

everything has already been validated.

---

## Responsibilities

Executor

- invokes hooks
- invokes command
- catches exceptions
- formats failures
- returns exit codes

---

## Execution Flow

```
Before Hooks

↓

Run Command

↓

Catch Exception

↓

After Hooks

↓

Cleanup

↓

Exit
```

---

## Return Values

Commands may

```
return;

return 0;

throw Exception;
```

Executor converts these into proper exit codes.

---

## Exception Handling

Unhandled exceptions

↓

formatted error

↓

exit code

↓

cleanup

---

Executor should never allow raw PHP fatal output in normal mode.

---

## Timing

Future versions may measure

- startup time
- execution time
- framework bootstrap time

Executor owns runtime metrics.

---

# 14. Context Architecture

## Overview

Context is the runtime boundary.

Everything a command can access should come through Context.

Commands should rarely need any other dependency.

---

## Why Context?

Without Context

```
Global State

Singletons

Static APIs

Service Locator
```

With Context

```
Context

↓

Services
```

Cleaner.

Testable.

Predictable.

---

## Context Lifetime

Created

before execution.

Destroyed

after execution.

Never shared.

---

## Context Structure

```
Context

├── Config

├── Environment

├── Terminal

├── Logger

├── Filesystem

├── Process

├── Framework Adapter

├── Command Metadata

└── Runtime State
```

---

## Immutability

Core context data

should remain immutable.

Runtime services may expose mutable operations.

---

## Extensibility

Future adapters should attach to Context.

Never modify command signatures.

---

# 15. Framework Adapter Architecture

## Purpose

Framework adapters isolate framework-specific behavior.

Core pcmd never depends directly on frameworks.

---

## Structure

```
Context

↓

FrameworkAdapter

↓

LaravelAdapter

SymfonyAdapter

WordPressAdapter
```

---

## Interface

Conceptually

```php
interface FrameworkAdapter
{
    public function boot(): void;

    public function shutdown(): void;

    public function name(): string;
}
```

Additional framework APIs belong on derived interfaces.

---

## Responsibilities

Adapter

- bootstrap
- expose services
- expose metadata
- cleanup

Nothing more.

---

## Forbidden Responsibilities

Adapters never

- discover commands
- parse CLI
- load configuration
- execute commands

---

## Benefits

Frameworks remain replaceable.

Core remains portable.

---

# 16. Laravel Adapter

## Purpose

The Laravel adapter provides a bridge between pcmd and a Laravel application.

It is loaded only after successful environment detection.

---

## Bootstrap

Bootstrap sequence

```
Locate Root

↓

vendor/autoload.php

↓

bootstrap/app.php

↓

Kernel

↓

Application

↓

Ready
```

---

## Services

Expose

```
Application

Container

Artisan

DB

Cache

Config

Queue

Events

Filesystem
```

through a stable API.

---

## Isolation

Laravel-specific code stays entirely inside this adapter.

No Illuminate classes should leak into the core.

---

## Future Compatibility

The adapter should tolerate Laravel upgrades by minimizing reliance on internal framework implementation details.

Favor public APIs.

---

# 17. Filesystem Layer

## Overview

All filesystem operations pass through a dedicated abstraction.

Never call

```
file_get_contents()

mkdir()

unlink()
```

directly inside commands when an abstraction exists.

---

## Responsibilities

Filesystem layer provides

```
read

write

copy

move

delete

mkdir

exists

glob

walk

temp
```

---

## Goals

- consistent errors
- easier testing
- future virtualization
- centralized path normalization

---

## Path Handling

All paths should become normalized absolute paths internally.

Relative paths should resolve using

```
cwd()

or

root()
```

depending on operation.

---

## Testing

Filesystem implementation should be mockable.

---

# 18. Process Layer

## Overview

External programs execute through the Process subsystem.

Never use

```
exec()

system()

shell_exec()

passthru()
```

inside commands.

---

## Responsibilities

```
run()

capture()

stream()

timeout()

cwd()

environment()
```

---

## Benefits

Centralized

- escaping
- timeout handling
- output capture
- platform compatibility

---

## Failure

Process failures return structured results.

Avoid throwing exceptions for non-zero exit codes unless configured.

---

## Future

Future implementations may support

- async execution
- cancellation
- parallel processes

without changing command APIs.

---

# 19. Hook System

## Overview

Hooks allow shared behavior before and after command execution.

Hooks are optional.

---

## Lifecycle

```
Bootstrap

↓

Before Hooks

↓

Command

↓

After Hooks

↓

Cleanup
```

---

## Types

Possible hooks

```
boot

before

after

shutdown
```

---

## Responsibilities

Hooks may

- initialize helpers
- logging
- metrics
- confirmations
- telemetry

Hooks should not contain business logic.

---

## Isolation

One failing hook should abort execution cleanly.

Hooks execute in deterministic order.

---

# 20. Plugin Architecture

## Vision

Everything beyond the core should be pluggable.

The executable should remain small.

---

## Plugin Categories

Examples

```
Environment Plugins

Command Packs

Helper Libraries

Framework Adapters

Terminal Themes

Discovery Providers
```

---

## Discovery

Plugins may eventually reside in

```
~/.pcmd/plugins/
```

Each plugin is independently discoverable.

---

## Loading

Plugin loading occurs after configuration but before command discovery.

This allows plugins to contribute

- detectors
- commands
- helpers
- adapters

---

## Isolation

Plugins should communicate through documented interfaces only.

They should never depend on internal implementation details.

---

## Future Package Format

The exact plugin packaging format is intentionally unspecified.

The architecture should allow future installation methods such as

- Composer packages
- Git repositories
- ZIP archives
- official plugin registry

without redesigning the core application.

---

# 21. Error Handling Architecture

## Overview

Error handling should be centralized.

Subsystems report failures.

The Application determines how failures are presented.

Commands should never format fatal errors themselves.

---

## Error Categories

Errors fall into several categories.

```
Configuration

Discovery

Environment

Validation

Bootstrap

Execution

Internal
```

Every category should have its own exception type.

Example

```
ConfigurationException

DiscoveryException

BootstrapException
```

Avoid generic Exception.

---

## Exception Flow

```
Subsystem

↓

Typed Exception

↓

Application

↓

Formatter

↓

Console

↓

Exit Code
```

The Application is the only component responsible for converting exceptions into user-visible output.

---

## Formatting

Errors should be rendered consistently.

Example

```
──────────────────────────────

Configuration Error

Reason

Invalid cache configuration.

Solution

Expected a boolean.

Received

"yes"

──────────────────────────────
```

---

## Debug Mode

When

```
--debug
```

is enabled,

display

- stack trace
- execution stage
- subsystem
- timing

Otherwise only display user-friendly information.

---

## Internal Rule

Subsystems never write directly to STDERR.

They throw typed exceptions.

---

# 22. Dependency Rules

## Philosophy

Dependencies should always point inward.

Higher-level modules depend on abstractions.

Never on concrete implementations.

---

## Layer Diagram

```
CLI

↓

Application

↓

Interfaces

↓

Implementations
```

Never

```
Implementation

↓

Application
```

---

## Allowed Dependencies

CLI

↓

Application

Application

↓

Configuration

↓

Environment

↓

Discovery

↓

Registry

↓

Executor

↓

Context

Executor

↓

Command

Context

↓

Filesystem

↓

Logger

↓

Process

↓

Framework Adapter

---

## Forbidden Dependencies

Commands must never depend directly on

```
Registry

Discovery

Resolver

CLI

Application
```

---

Framework adapters must never depend on

```
Discovery

Registry

Resolver
```

---

Filesystem must never depend on

Laravel

Symfony

WordPress

---

Process manager must never know

Environment

Commands

Frameworks

---

## Circular Dependencies

Circular dependencies are prohibited.

If two systems need each other,

extract a third abstraction.

---

## Interfaces

Every replaceable subsystem should expose an interface.

Examples

```
FilesystemInterface

LoggerInterface

ProcessInterface

EnvironmentDetectorInterface

FrameworkAdapterInterface
```

Application should depend only on interfaces.

---

# 23. Internal Directory Layout

The repository should remain organized by subsystem.

Recommended structure

```
src/

Application/

CLI/

Configuration/

Environment/

Discovery/

Registry/

Resolution/

Execution/

Context/

Filesystem/

Logging/

Process/

Framework/

Laravel/

Contracts/

Exceptions/

Support/

Terminal/
```

---

## Tests

```
tests/

Application/

Discovery/

Environment/

Execution/

Context/

Framework/

Filesystem/

Process/

Integration/

Fixtures/
```

---

## Resources

```
resources/

stubs/

templates/

examples/
```

---

## Documentation

```
docs/

AGENTS.md

SPECIFICATION.md

ARCHITECTURE.md

COMMAND_API.md

DIRECTORY_STRUCTURE.md

ROADMAP.md
```

---

## Design Rule

Directories represent responsibilities.

Avoid

```
Helpers/

Utils/

Misc/

Common/
```

Prefer explicit subsystem names.

---

# 24. Testing Architecture

## Philosophy

Architecture without tests is incomplete.

Every subsystem should be independently testable.

---

## Test Types

### Unit Tests

Test one subsystem.

Mock all dependencies.

---

### Integration Tests

Verify interaction between subsystems.

Examples

Discovery

↓

Registry

↓

Resolver

---

### End-to-End Tests

Execute

```
pcmd ...
```

Verify

- output
- exit code
- side effects

---

### Regression Tests

Every bug fixed should receive a regression test.

---

## Fixtures

Test fixtures should live under

```
tests/Fixtures/
```

Examples

```
laravel-app/

generic/

broken-config/

duplicate-commands/

invalid-command/
```

---

## Performance Tests

Future versions may benchmark

- startup
- discovery
- registry lookup
- bootstrap

Performance regressions should be detectable.

---

## Coverage

Critical infrastructure should target high test coverage.

Especially

- discovery
- resolver
- executor
- context
- framework adapters

---

# 25. Future Extension Strategy

## Philosophy

The architecture should make future features additive.

Existing code should rarely require modification.

---

## New Frameworks

Adding Symfony should require

```
SymfonyDetector

SymfonyAdapter

Symfony Commands
```

The core should remain untouched.

---

## New Command Packs

Future command collections should simply be added under

```
commands/
```

No registration.

No rebuild.

No source modification.

---

## New Services

Adding a new Context service should not require changing command signatures.

Example

Today

```
$ctx->fs()
```

Tomorrow

```
$ctx->http()
```

Existing commands remain valid.

---

## New Plugin Types

Potential future plugins

```
AI Assistants

Cloud Providers

Docker

Kubernetes

GitHub

GitLab

AWS

DigitalOcean

Linode

Database Tools
```

The architecture should already accommodate these.

---

## Backward Compatibility

Public APIs should evolve conservatively.

Favor extension over replacement.

---

# 26. Performance Architecture

## Startup

Cold startup should minimize

- filesystem access
- reflection
- object creation

---

## Lazy Loading

Everything expensive should be lazy.

Examples

Commands

Framework adapters

Logger backends

Plugin implementations

---

## Object Lifetime

Avoid long-lived mutable objects.

Most services exist for one execution only.

---

## Memory

Do not retain command instances after execution.

Keep metadata lightweight.

---

## Caching Strategy

Cache

- metadata
- parsed definitions
- detector results

Never cache runtime state.

---

## Scalability

The architecture should comfortably support

- thousands of commands
- dozens of plugins
- multiple framework adapters

without architectural changes.

---

# 27. Security Architecture

## Trust Model

Core assumes locally installed commands are trusted.

However,

the architecture should prepare for future trust verification.

---

## Permissions

Future commands may declare capabilities.

Example

```
filesystem

database

network

process

artisan
```

These are informational in v1.

---

## Secrets

Secrets should flow only through Context.

Never expose them through logs.

---

## Production Protection

Framework adapters should expose

```
isProduction()
```

Commands may use this for confirmation logic.

---

## Isolation

Future sandboxing should be possible without changing command definitions.

---

# 28. Architectural Constraints

The following constraints are permanent.

Do not violate them.

---

## One Responsibility

Every subsystem has one job.

---

## Immutable Configuration

Configuration never changes after startup.

---

## Lazy Commands

Commands are loaded only when executed.

---

## Adapter Isolation

Framework logic never enters the core.

---

## Stable Context

Command signatures remain

```php
function (Context $ctx)
```

Future features extend Context.

Never replace it.

---

## No Global State

Avoid

Singletons

Global variables

Static registries

Shared mutable state

---

## Composition

Prefer composition.

Avoid deep inheritance.

---

## Deterministic Behavior

The same command with the same inputs should behave identically.

---

# 29. Architectural Decision Records (ADR)

Major architectural changes should be recorded.

Recommended format

```
ADR-0001

Context API

Status

Accepted

Reason

...

Consequences

...
```

Store ADRs under

```
docs/adr/
```

This preserves design history.

---

# 30. Final Notes

This architecture is intentionally conservative.

It values

- simplicity
- maintainability
- extensibility
- predictable behavior

over novelty.

Every new subsystem should fit naturally into the existing architecture rather than introducing special cases.

Whenever possible:

- add new adapters instead of modifying the core,
- add new services instead of changing public APIs,
- add new plugins instead of hardcoding behavior.

The architecture is designed so that five years from now, supporting a new framework or a new class of developer tools should primarily involve implementing new adapters and plugins—not rewriting existing systems.

If any implementation conflicts with this document, the implementation should be considered incorrect until the architecture is intentionally revised.

This document, together with `SPECIFICATION.md`, forms the authoritative foundation for all future development of **pcmd**.