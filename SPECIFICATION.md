# SPECIFICATION.md

> **Project:** pcmd
>
> **Status:** Draft v1.0
>
> **Audience:** End users, contributors, and AI coding agents.
>
> This document defines **what pcmd is**, **how it behaves**, and **how every public feature should work**.
>
> This is the primary specification for the executable.
>
> If implementation differs from this document, the implementation is considered incorrect.

---

## Implementation Status

This specification describes the **target behavior** for v1.0. Sections marked below indicate what has been implemented so far.

| Area | Status |
|------|--------|
| §2 CLI Syntax | ✅ Mostly implemented. Dry-run not wired. Suggestions work. |
| §3 Command Discovery | ✅ Implemented (recursive scan, ignore patterns, duplicate detection, caching) |
| §4 Environment Detection | ✅ Implemented (Generic + Laravel; directory walk-up) |
| §5 Command Resolution | ✅ Implemented (normalize, alias, env check, validation, suggestions) |
| §6 Command Lifecycle | ✅ Implemented (bootstrap, context, hooks, execute, cleanup, exit) |
| §7 Context API | ✅ Implemented (cwd, root, home, temp, arg, option, terminal, fs, process, log, table, spinner, progress, helper) |
| §8 Arguments & Options | ✅ Implemented (Argument/Option builders, InputValidator, type/file/regex/validation chain) |
| §9 Interactive Console | 🔶 Partial (ask/confirm/choice/secret work; tree not implemented) |
| §10 Configuration | ✅ Implemented (immutable, dot-notation, typed getters, ~/.pcmd/config.php) |
| §11 General Commands | 🔶 Partial (engine supports them; example commands not shipped) |
| §12 Laravel Commands | ✅ Implemented (LaravelAdapter, example commands for Eloquent/Artisan/cache/queue, $ctx->laravel() returns adapter) |
| §13 Help System | ✅ Implemented (auto-generated argument/option docs, examples, usage) |
| §14 Exit Codes | ✅ Implemented (0-9, 130 all wired correctly) |
| §15 Errors | ✅ Implemented (typed exceptions, suggestion on unknown commands, --debug mode with stack traces) |
| §16 Logging | ✅ Implemented (PSR-3 style, multi-level, secret masking) |
| §17 Caching | ✅ Implemented (mtime-based file change detection, auto-invalidation) |
| §18 Security | ⚠️ Not audited; basic confirmation in place |
| §19 Examples | 🔶 Partial (commands placed in ~/.pcmd/commands work; no built-in example pack) |
| §20 Appendix | ✅ Reserved names and exit codes match implementation |

---

# 1. Introduction & Goals

## 1.1 What is pcmd?

pcmd is a standalone, environment-aware command-line application that allows developers to create and reuse commands across all projects without copying code into those projects.

Unlike framework-specific CLIs such as:

- Laravel Artisan
- Symfony Console
- WP CLI
- Drush

pcmd itself is framework-independent.

Instead of belonging to a project, it belongs to the developer.

It is installed once and used everywhere.

---

## 1.2 Motivation

Many developers repeatedly write one-time commands inside applications.

Example:

```
php artisan products:repair-images
```

After the repair is complete, the command is never used again.

Months later another project needs the exact same repair.

The command must be rewritten.

This leads to:

- duplicated code
- forgotten utilities
- inconsistent implementations
- unnecessary project pollution

pcmd solves this by moving reusable commands outside projects.

---

## 1.3 Design Philosophy

The executable should feel like a natural extension of the terminal.

The user should never think:

> "I'm using pcmd."

Instead they should think:

> "I have another command available."

The tool should disappear behind the workflow.

---

## 1.4 Primary Objectives

The project exists to achieve six primary objectives.

### Reusability

Commands should be reusable across projects.

A command written once should continue working for years.

---

### Simplicity

Writing a command should require very little boilerplate.

The developer should spend time solving problems rather than writing framework code.

---

### Environment Awareness

Commands become available automatically depending on where pcmd is executed.

Example:

```
Desktop/

General commands
```

```
Laravel Project/

General commands
Laravel commands
```

```
Symfony Project/

General commands
Symfony commands
```

---

### Portability

The executable should work on any machine capable of running PHP.

No framework installation required.

No project modification required.

---

### Discoverability

Every available command should be discoverable.

Users should never wonder:

- what commands exist
- why a command disappeared
- where commands came from

---

### Extensibility

Support for new environments should require minimal changes.

Future integrations should not require redesigning the application.

---

## 1.5 Non Goals

pcmd intentionally avoids solving unrelated problems.

It is NOT:

- a package manager
- a dependency manager
- a deployment tool
- a build system
- a task scheduler
- a shell replacement
- a terminal emulator
- an IDE

Keeping the scope small keeps the project maintainable.

---

## 1.6 Key Principles

Every feature should satisfy these principles.

### Principle A

Simple APIs.

---

### Principle B

Minimal configuration.

---

### Principle C

Predictable behavior.

---

### Principle D

Framework isolation.

---

### Principle E

Fast startup.

---

### Principle F

Clear errors.

---

## 1.7 Expected User Experience

The user installs pcmd once.

```
$ pcmd

No project detected.

Available commands:

json:pretty
git:cleanup
file:hash
```

Inside Laravel

```
$ pcmd

Laravel detected.

General Commands

...

Laravel Commands

...
```

The executable behaves naturally without requiring configuration.

---

# 2. CLI Syntax

## 2.1 Basic Invocation

The executable name is

```
pcmd
```

All interactions begin with this executable.

Examples

```
pcmd
pcmd help
pcmd list
pcmd search
pcmd json:pretty
pcmd products:reindex
```

---

## 2.2 General Syntax

The command grammar is

```
pcmd [global-options] command [arguments] [options]
```

Example

```
pcmd products:reindex --queue
```

---

## 2.3 Command Naming

Commands use namespaces.

```
group:name
```

Examples

```
json:pretty

json:minify

db:truncate

queue:retry-all

storage:clean

search:reindex
```

Nested namespaces may be supported.

```
products:images:repair
```

although excessively deep nesting is discouraged.

---

## 2.4 Reserved Commands

The following commands belong to pcmd itself.

```
help

list

version

doctor

env

config

cache:clear

cache:rebuild

self:update

self:info
```

User commands may not override these names.

---

## 2.5 Global Options

Available everywhere.

```
--help

--version

--verbose

--quiet

--ansi

--no-ansi

--yes

--no-interaction

--dry-run
```

Framework commands automatically inherit them.

---

## 2.6 Exit Codes

Unless specified otherwise

```
0

Success
```

```
1

General error
```

```
2

Command not found
```

```
3

Environment mismatch
```

```
4

Invalid arguments
```

```
5

Permission denied
```

Additional exit codes are documented later.

---

## 2.7 Help System

Every command automatically exposes help.

```
pcmd help

pcmd help json:pretty

pcmd products:reindex --help
```

Help pages include

- description
- usage
- arguments
- options
- examples

---

## 2.8 Command Suggestions

Unknown commands should produce suggestions.

Example

```
pcmd storge:clean
```

Output

```
Unknown command

storge:clean

Did you mean

storage:clean
```

---

## 2.9 Version Output

```
pcmd --version
```

Example

```
pcmd 1.2.0

PHP 8.4

Environment

Laravel

Platform

Linux
```

---

## 2.10 Quiet Mode

```
--quiet
```

Suppresses non-essential output.

Errors remain visible.

---

## 2.11 Verbose Mode

```
--verbose
```

Enables additional diagnostic output.

---

## 2.12 Dry Run

```
--dry-run
```

Commands should simulate execution whenever possible.

Nothing should be modified.

---

# 3. Command Discovery

## 3.1 Overview

Commands are discovered automatically.

No registration step exists.

No manifest needs editing.

Dropping a file into the correct directory immediately makes it available.

---

## 3.2 Root Directory

The default user directory is

```
~/.pcmd/
```

Example

```
~/.pcmd/

commands/

helpers/

cache/

config.php
```

---

## 3.3 Command Directories

Commands are grouped by environment.

```
commands/

general/

laravel/

symfony/

wordpress/
```

Additional environments may be added later.

---

## 3.4 General Commands

General commands are always available.

Regardless of project type.

Example

```
commands/general/

json/

git/

file/

text/
```

---

## 3.5 Environment Commands

Environment commands only load when their environment is active.

Example

```
commands/

laravel/

db/

search/

queue/
```

These commands are hidden outside Laravel.

---

## 3.6 Recursive Discovery

Discovery is recursive.

Example

```
commands/

general/

git/

cleanup.php

reset.php

sync/

fetch.php
```

All commands become available.

---

## 3.7 Hidden Files

Ignore

```
.*

*.bak

*.tmp

*.disabled
```

These should never become commands.

---

## 3.8 Naming

The command name is derived from its path.

Example

```
general/

json/

pretty.php
```

becomes

```
json:pretty
```

Example

```
laravel/

search/

reindex.php
```

becomes

```
search:reindex
```

The file does not need to declare its own name.

The filesystem is the source of truth.

---

## 3.9 Duplicate Commands

Duplicate command names are prohibited.

If two commands resolve to

```
db:truncate
```

pcmd reports an error.

It never chooses arbitrarily.

---

## 3.10 Discovery Order

Commands load in this order.

1.

Internal commands

↓

2.

General commands

↓

3.

Environment commands

↓

4.

Future plugin commands

---

## 3.11 Discovery Cache

Filesystem scanning should be cached.

Cache invalidation occurs when

- command added
- command removed
- command modified

---

## 3.12 Discovery Errors

Invalid command files should never crash discovery.

Instead

```
Skipped

commands/general/test.php

Reason

Syntax error
```

Other commands remain usable.

---

# 4. Environment Detection

## 4.1 Overview

Before command discovery, pcmd determines the current environment.

Environment detection is automatic.

No user configuration required.

---

## 4.2 Detection Order

Detection proceeds from the current working directory upward.

Example

```
project/

app/

Models/
```

Running

```
pcmd
```

inside

```
Models
```

should still detect the project root.

---

## 4.3 Current Supported Environments

Version 1.0 officially supports

Generic

Laravel

Future versions may add

Symfony

WordPress

Magento

Drupal

Laravel Zero

---

## 4.4 Generic Environment

If no known environment is detected

Environment

```
Generic
```

Only general commands load.

---

## 4.5 Laravel Detection

Laravel detection succeeds only if all required indicators exist.

Example indicators

```
artisan

bootstrap/app.php

vendor/autoload.php
```

Future implementation details belong in ARCHITECTURE.md.

---

## 4.6 Project Root

Environment detection also determines the project root.

Example

```
project/

artisan

bootstrap/

app/

vendor/
```

Root becomes

```
project/
```

All relative paths resolve from this location.

---

## 4.7 Environment Information

Users may inspect detection.

```
pcmd env
```

Example

```
Environment

Laravel

Root

/home/user/shop

Version

12.x
```

---

## 4.8 Detection Failure

If multiple environments appear valid

The most specific detector wins.

If ambiguity remains

pcmd reports an error rather than guessing.

---

## 4.9 Environment Isolation

Laravel commands never load inside Generic projects.

Generic commands always load.

---

## 4.10 Custom Environments

Future versions may allow user-defined detectors.

Specification deferred.

---

# 5. Command Resolution

## 5.1 Overview

Once environments are detected and commands discovered, pcmd resolves the command requested by the user.

Resolution must be deterministic.

The same input must always produce the same command.

---

## 5.2 Resolution Pipeline

Every invocation follows the same sequence.

```
User Input
        │
        ▼
Parse CLI Arguments
        │
        ▼
Detect Environment
        │
        ▼
Discover Commands
        │
        ▼
Resolve Command Name
        │
        ▼
Validate Arguments
        │
        ▼
Create Context
        │
        ▼
Execute Command
```

No command code executes before successful resolution.

---

## 5.3 Exact Match

Command lookup is based on an exact canonical name.

For example:

```
json:pretty
```

must resolve only to that command.

Partial or fuzzy matching is **not** used for execution.

---

## 5.4 Case Sensitivity

Command names are case-insensitive for user convenience, but their canonical form is lowercase.

These are equivalent:

```
pcmd JSON:Pretty
pcmd Json:Pretty
pcmd json:pretty
```

Internally, the canonical name is:

```
json:pretty
```

---

## 5.5 Aliases

A command may define one or more aliases.

Example:

```
search:reindex
```

Aliases:

```
reindex
search:index
```

Aliases must be unique across all discovered commands.

If an alias conflicts with another command or alias, discovery fails with a clear error.

---

## 5.6 Reserved Names

Reserved internal commands always take precedence.

User-defined commands may not override:

```
help
list
version
env
doctor
config
cache:clear
cache:rebuild
self:update
self:info
```

Attempting to define one of these names is a discovery error.

---

## 5.7 Unknown Commands

If no matching command is found:

1. Display an error.
2. Suggest similar commands when confidence is high.
3. Return exit code `2`.

Example:

```
$ pcmd storge:clean

Unknown command:

storge:clean

Did you mean:

storage:clean
```

---

## 5.8 Ambiguous Resolution

Resolution must never choose between multiple candidates.

If ambiguity exists due to duplicate names or aliases, execution stops and the user is informed of the conflict.

Automatic guessing is prohibited.

---

## 5.9 Pre-execution Validation

Before executing a command, pcmd validates:

- environment compatibility
- required arguments
- option syntax
- duplicate options
- unknown options
- command availability

Only after successful validation is the execution context created.

---

## 5.10 Execution Guarantee

Once a command begins execution, it is guaranteed that:

- the requested command exists,
- the current environment is compatible,
- arguments have been validated,
- options have been parsed,
- the execution context has been initialized.

Command authors should not need to repeat these checks unless they relate to command-specific business logic.

---

# 6. Command Lifecycle

## 6.1 Overview

Every command, regardless of environment, follows the same lifecycle.

The lifecycle is intentionally fixed and predictable.

```
CLI Invocation
        │
        ▼
Parse Arguments
        │
        ▼
Load Configuration
        │
        ▼
Detect Environment
        │
        ▼
Discover Commands
        │
        ▼
Resolve Command
        │
        ▼
Validate
        │
        ▼
Bootstrap Environment
        │
        ▼
Create Context
        │
        ▼
Run Before Hooks
        │
        ▼
Execute Command
        │
        ▼
Run After Hooks
        │
        ▼
Cleanup
        │
        ▼
Exit
```

Every command follows this sequence.

No environment may alter this order.

---

## 6.2 Bootstrap

General commands

Bootstrap only pcmd.

Laravel commands

Bootstrap Laravel after successful environment detection.

No framework should be bootstrapped unless it is actually needed.

---

## 6.3 Context Creation

Before execution a Context object is created.

The Context contains

- parsed arguments
- parsed options
- current working directory
- project root
- environment
- output handler
- input handler
- helper registry
- logger
- framework adapters (if available)

Commands should never manually construct context.

---

## 6.4 Before Hooks

Optional.

Executed after context creation.

Possible uses

- initialize shared services
- verify production mode
- logging
- telemetry
- loading helper files

Failure inside a before hook aborts execution.

---

## 6.5 Command Execution

Exactly one command executes.

Nested execution is prohibited unless explicitly requested.

A command should return

```
Success

or

Failure
```

Prefer explicit exceptions over silent failures.

---

## 6.6 After Hooks

Executed regardless of success.

Similar to

```
finally
```

Possible responsibilities

- cleanup
- closing resources
- timing
- logging
- restoring terminal state

---

## 6.7 Cleanup

Cleanup should release

- temporary files
- locks
- database connections
- progress bars
- terminal state

Cleanup should happen even if execution fails.

---

## 6.8 Exit

Every command exits with a defined exit code.

Returning arbitrary integers is discouraged.

---

# 7. Context API

## 7.1 Purpose

The Context object is the primary interface available to commands.

Commands should never communicate directly with the CLI implementation.

Everything goes through Context.

---

## 7.2 Design Goals

The Context API should

- remain small
- remain stable
- hide implementation details
- be framework independent

Framework-specific functionality belongs to adapters.

---

## 7.3 Core API

Every command receives

```php
function (Context $ctx)
```

The Context provides

```
cwd()

root()

home()

temp()

environment()

command()

arguments()

options()

config()

logger()

terminal()
```

---

## 7.4 Arguments

```
$ctx->arg('file')

$ctx->arg('user')

$ctx->arg(0)
```

Returns parsed positional arguments.

---

## 7.5 Options

```
$ctx->option('force')

$ctx->option('queue')

$ctx->option('timeout')
```

Returns parsed options.

---

## 7.6 Terminal API

```
$ctx->info()

$ctx->warn()

$ctx->error()

$ctx->success()

$ctx->line()

$ctx->newline()
```

These should be the preferred way of writing output.

Never use

```
echo
```

inside commands.

---

## 7.7 User Input

Interactive commands use

```
$ctx->ask()

$ctx->confirm()

$ctx->secret()

$ctx->choice()

$ctx->multichoice()
```

These methods automatically respect

```
--no-interaction
```

---

## 7.8 Progress

The Context exposes

```
progress()

spinner()

table()

tree()

```

Rendering implementation is internal.

Commands should not depend on ANSI escape sequences.

---

## 7.9 Filesystem

```
$ctx->fs()
```

Provides filesystem helpers.

Examples

```
exists()

copy()

move()

delete()

mkdir()

read()

write()

glob()
```

---

## 7.10 Process Execution

```
$ctx->process()
```

Provides

```
run()

capture()

passthrough()

timeout()

cwd()
```

Never shell out using

```
exec()

shell_exec()

system()
```

directly.

---

## 7.11 Logging

```
$ctx->log()
```

Supports

```
debug()

info()

warning()

error()
```

Implementation is configurable.

---

## 7.12 Laravel Adapter

When Laravel is available

```
$ctx->laravel()
```

returns

```
Application

Artisan

Container

Database

Config

Cache

Queue

Events

Filesystem
```

Outside Laravel

```
$ctx->laravel()
```

returns

```
null
```

or throws an explicit exception depending on implementation.

Commands should never assume Laravel exists.

---

## 7.13 Future Adapters

Examples

```
$ctx->symfony()

$ctx->wordpress()

$ctx->drupal()
```

The Context API itself never changes.

Only additional adapters are added.

---

# 8. Arguments & Options

## 8.1 Overview

Arguments and options are declared by the command.

pcmd parses and validates them before execution.

---

## 8.2 Positional Arguments

Example

```
json:pretty file.json
```

Arguments

```
file
```

Arguments preserve order.

---

## 8.3 Named Options

Example

```
--force

--queue

--dry-run
```

Long option syntax is preferred.

---

## 8.4 Short Options

Supported

```
-v

-q

-f
```

Combined short options may be supported.

Example

```
-vq
```

---

## 8.5 Boolean Options

Boolean options require no value.

```
--force
```

means

```
true
```

---

## 8.6 Value Options

Example

```
--timeout=30

--driver=mysql
```

Equivalent syntax

```
--timeout 30
```

Both forms should be accepted.

---

## 8.7 Required Arguments

Missing required arguments prevent execution.

Example

```
Missing argument

file
```

Exit code

```
4
```

---

## 8.8 Unknown Options

Unknown options are errors.

```
pcmd json:pretty --banana
```

Output

```
Unknown option

--banana
```

---

## 8.9 Repeated Options

Behavior depends on option type.

Boolean

```
--force --force
```

treated as

```
true
```

List options

```
--path=a

--path=b
```

return

```
[a,b]
```

---

## 8.10 Defaults

Arguments and options may define defaults.

Defaults are applied before command execution.

---

## 8.11 Validation

Supported validation includes

- required
- integer
- float
- boolean
- existing file
- existing directory
- writable path
- readable path
- enum
- regex

Framework-specific validation belongs elsewhere.

---

## 8.12 Help Generation

Declared arguments automatically appear in

```
pcmd help command
```

Manual duplication is discouraged.

---

# 9. Interactive Console

## 9.1 Overview

Interactive functionality should behave consistently across every command.

Commands should never implement custom readline logic.

Always use Context.

---

## 9.2 Confirmation

```
$ctx->confirm(
    "Delete database?"
)
```

Returns

```
true

false
```

---

## 9.3 Questions

```
$ctx->ask(
    "Project name"
)
```

Supports optional defaults.

---

## 9.4 Hidden Input

Passwords

API keys

Secrets

use

```
secret()
```

Input should never be echoed.

---

## 9.5 Choice

Example

```
Database

>

MySQL

SQLite

PostgreSQL
```

Returns selected value.

---

## 9.6 Multi Choice

Supports selecting multiple values.

Returns array.

---

## 9.7 Tables

Context should support

```
table(
    headers,
    rows
)
```

Rendering should automatically adapt to terminal width.

---

## 9.8 Progress Bars

Progress bars expose

```
start()

advance()

finish()
```

They should automatically redraw.

---

## 9.9 Spinners

For indeterminate operations.

```
spinner()->start()

spinner()->finish()
```

---

## 9.10 Colors

Use semantic output.

Examples

```
Info

Success

Warning

Error
```

Commands should not depend on specific ANSI color codes.

---

## 9.11 Non Interactive Mode

When

```
--no-interaction
```

is supplied

Interactive methods should

- use defaults
- fail clearly if required
- never block

---

## 9.12 Terminal Width

Output should automatically wrap.

Avoid hardcoded widths.

---

# 10. Configuration

## 10.1 Overview

Configuration controls pcmd behavior.

Configuration belongs entirely outside projects.

Projects should never require modification.

---

## 10.2 Default Location

```
~/.pcmd/config.php
```

Single configuration file.

Additional configuration formats may be supported later.

---

## 10.3 Configuration Loading

Configuration loads before

- discovery
- environment detection
- execution

Commands may read configuration through Context.

---

## 10.4 Search Order

Configuration precedence

```
Defaults

↓

Global config

↓

Environment overrides

↓

CLI flags
```

CLI flags always win.

---

## 10.5 Example

```php
return [

    'cache' => true,

    'colors' => true,

    'verbose' => false,

    'editor' => 'code',

];
```

---

## 10.6 Configuration Categories

Future configuration sections

```
cache

terminal

editor

logging

discovery

plugins

updates

performance
```

---

## 10.7 Runtime Configuration

Commands should access configuration via

```
$ctx->config()
```

Never read configuration files directly.

---

## 10.8 Missing Configuration

If configuration does not exist

pcmd automatically uses defaults.

No warning should be displayed.

---

## 10.9 Invalid Configuration

Malformed configuration should produce

```
Configuration Error

~/.pcmd/config.php

Unexpected value:

cache = "yes"

Expected:

boolean
```

Execution should stop.

---

## 10.10 Future Configuration

Configuration should remain backward compatible whenever possible.

Deprecated options should continue functioning for at least one major release before removal.

---

# 11. General Commands

## 11.1 Overview

General commands are framework-independent.

They are available in every environment, including:

- Generic directories
- Laravel projects
- Symfony projects
- WordPress projects
- Any future supported environment

General commands must never depend on a framework.

---

## 11.2 Responsibilities

General commands are intended for tasks such as:

- filesystem utilities
- JSON manipulation
- text processing
- hashing
- encoding/decoding
- Git helpers
- HTTP utilities
- archive handling
- image utilities
- CSV processing
- XML/YAML utilities
- process helpers

---

## 11.3 Prohibited Dependencies

General commands MUST NOT import:

```
Illuminate\*

Symfony\Bundle\*

WordPress APIs

Drupal APIs
```

General commands should remain executable anywhere PHP runs.

---

## 11.4 Examples

```
json:pretty

json:minify

json:validate

file:hash

file:size

file:tree

text:replace

text:grep

csv:merge

csv:split

http:get

http:download

archive:zip

archive:extract

git:cleanup

git:branches

git:sync
```

---

## 11.5 Environment Access

General commands have access to:

- Context
- Filesystem
- Process API
- Configuration
- Logger
- Terminal
- Helpers

They should never assume a project exists.

---

## 11.6 Current Working Directory

General commands always execute relative to

```
$ctx->cwd()
```

unless explicitly documented otherwise.

---

## 11.7 Project Awareness

General commands may detect a project.

Example

```
git:cleanup
```

may inspect

```
.git/
```

However, they should not require a specific framework.

---

## 11.8 Output

General commands should produce concise output.

Avoid verbose logs unless

```
--verbose
```

is enabled.

---

## 11.9 Destructive Operations

Commands capable of deleting or overwriting data should require confirmation unless

```
--yes
```

or

```
--force
```

is provided.

---

## 11.10 Reusability

General commands should be reusable outside software projects whenever practical.

For example

```
json:pretty
```

should work equally well inside

```
Downloads/
```

or

```
Laravel/
```

---

# 12. Laravel Commands

## 12.1 Overview

Laravel commands extend pcmd with Laravel-specific functionality.

They are available only when a Laravel application has been successfully detected and bootstrapped.

---

## 12.2 Purpose

Laravel commands exist to perform operations that require:

- Eloquent
- Service Container
- Artisan
- Configuration
- Cache
- Queue
- Events
- Filesystem
- Application Services

---

## 12.3 Availability

Laravel commands should not appear in

```
pcmd list
```

unless the current environment is Laravel.

---

## 12.4 Bootstrap

Before executing a Laravel command,

pcmd must:

1.

Locate project root.

↓

2.

Load Composer autoloader.

↓

3.

Bootstrap Laravel.

↓

4.

Create Context.

↓

5.

Execute command.

---

## 12.5 Application Access

Laravel commands should never bootstrap Laravel manually.

Instead they access the application through

```php
$ctx->laravel()
```

---

## 12.6 Adapter

The Laravel adapter exposes

```
app()

artisan()

db()

cache()

config()

queue()

events()

storage()

filesystem()

container()
```

Additional APIs may be added in future versions.

---

## 12.7 Models

Laravel commands may freely use application models.

Example

```php
use App\Models\Product;
```

This is one of the primary use cases of pcmd.

---

## 12.8 Artisan

Laravel commands may execute Artisan commands.

Example

```php
$ctx->laravel()
    ->artisan()
    ->call('cache:clear');
```

---

## 12.9 Database

The adapter should expose

```
connection()

transaction()

query()

schema()
```

through a simplified interface.

---

## 12.10 Production Safety

If the application environment is

```
production
```

commands marked as destructive should require explicit confirmation.

Automatic execution is prohibited.

---

## 12.11 Examples

```
db:truncate

db:seed-demo

search:reindex

queue:retry-all

queue:clear

storage:repair

products:duplicate-images

products:repair-slugs

products:sync

users:make-admin

cache:clear-tags
```

---

## 12.12 Framework Isolation

Laravel commands may depend on Illuminate.

General commands must never do so.

---

# 13. Help System

## 13.1 Philosophy

Every command should be self-documenting.

A user should never need to read source code to understand how to use a command.

---

## 13.2 Automatic Help

Every command automatically supports

```
--help
```

and

```
pcmd help
```

No command should implement its own help renderer.

---

## 13.3 Help Layout

The standard help page contains

```
Description

Usage

Arguments

Options

Examples

Notes
```

in that order.

---

## 13.4 Description

Every command should provide a concise description.

Prefer

```
Repairs duplicate product images.
```

Avoid

```
This command repairs duplicate product images by checking every product...
```

Long explanations belong in Notes.

---

## 13.5 Usage

Example

```
pcmd products:repair-images
```

Usage examples should always be executable.

---

## 13.6 Arguments

Arguments should include

- name
- description
- required/optional
- default value

when applicable.

---

## 13.7 Options

Options should include

- long name
- short name
- description
- default value
- accepted values

when applicable.

---

## 13.8 Examples

Commands should provide at least one practical example.

Example

```
pcmd json:pretty data.json
```

---

## 13.9 Notes

Optional.

Suitable for

- warnings
- performance considerations
- production advice
- compatibility notes

---

## 13.10 Search

Future versions may support

```
pcmd help search json
```

or

```
pcmd search json
```

to locate commands by keyword.

---

# 14. Exit Codes

## 14.1 Purpose

Exit codes allow automation tools and scripts to determine whether execution succeeded.

Commands should always return meaningful exit codes.

---

## 14.2 Standard Exit Codes

```
0

Success
```

```
1

General error
```

```
2

Command not found
```

```
3

Unsupported environment
```

```
4

Invalid arguments
```

```
5

Permission denied
```

```
6

Configuration error
```

```
7

Discovery error
```

```
8

Bootstrap error
```

```
9

Command execution error
```

Additional codes may be defined by future versions.

---

## 14.3 Success

Successful execution always returns

```
0
```

Even if the command produced warnings.

Warnings do not indicate failure.

---

## 14.4 Exceptions

Unhandled exceptions are converted into

```
9
```

after rendering a formatted error message.

---

## 14.5 Interruptions

User interruption

```
Ctrl+C
```

should produce a dedicated exit code.

Suggested

```
130
```

following Unix conventions.

---

## 14.6 Fatal Errors

Internal failures should never expose raw PHP stack traces unless

```
--verbose
```

or

```
--debug
```

is enabled.

---

## 14.7 Automation

CI systems should be able to depend on exit codes remaining stable across minor releases.

Changing exit code semantics is a breaking change.

---

# 15. Errors

## 15.1 Philosophy

Errors should help users solve problems.

Never output vague messages.

Bad

```
Failed.
```

Good

```
Laravel application detected.

bootstrap/app.php could not be loaded.

Verify the project is installed correctly.
```

---

## 15.2 Error Format

Every error should include

```
Title

Reason

Possible Solution
```

when practical.

---

## 15.3 Validation Errors

Validation errors should identify

- invalid argument
- invalid option
- expected type
- received value

---

## 15.4 Discovery Errors

Discovery failures should identify

- command file
- error
- reason

Other commands should continue loading whenever possible.

---

## 15.5 Bootstrap Errors

Framework bootstrap failures should include

- detected framework
- failing component
- probable cause

---

## 15.6 Runtime Errors

Runtime exceptions should be formatted consistently.

Stack traces should remain hidden by default.

---

## 15.7 Debug Mode

```
--debug
```

enables

- stack traces
- timing
- loaded services
- discovery information
- bootstrap timing

This mode is intended for developers.

---

## 15.8 User Errors

Mistyped commands should generate suggestions.

Unknown options should identify the offending option.

Missing arguments should identify exactly which argument is missing.

---

## 15.9 Logging

Unexpected internal errors should be eligible for logging through the configured logger.

Logging must never expose secrets such as passwords, API keys, or access tokens.

---

## 15.10 Stability

Error message wording may improve over time, but:

- exit code meanings,
- error categories,
- and overall formatting

should remain stable across minor releases.

---

# 16. Logging

## 16.1 Overview

pcmd provides a unified logging system for both internal operations and command authors.

Logging is intended for:

- diagnostics
- debugging
- auditing
- troubleshooting

Logging is **not** intended to replace console output.

---

## 16.2 Logging API

Commands access logging exclusively through Context.

```php
$ctx->log()
```

The logger exposes

```
debug()

info()

notice()

warning()

error()

critical()
```

The implementation should follow PSR-3 concepts where practical.

---

## 16.3 Console vs Logger

Console output

```
$ctx->info(...)
```

is intended for the user.

Logger output

```
$ctx->log()->info(...)
```

is intended for diagnostics.

These systems should remain separate.

---

## 16.4 Log Levels

Supported levels

```
DEBUG

INFO

NOTICE

WARNING

ERROR

CRITICAL
```

Future versions may support additional levels.

---

## 16.5 Default Logging

By default,

only warnings and errors should be persisted.

Verbose logs should remain disabled unless configured.

---

## 16.6 Log Location

Default log directory

```
~/.pcmd/logs/
```

Example

```
logs/

pcmd.log

2026-08.log

discovery.log
```

Implementation details are configurable.

---

## 16.7 Sensitive Data

The logger must never automatically record

- passwords
- API keys
- bearer tokens
- cookies
- session IDs
- private keys

These values should be masked whenever possible.

---

## 16.8 Verbose Logging

When

```
--verbose
```

or

```
--debug
```

is enabled,

additional diagnostic information may be written.

---

## 16.9 Command Logging

Commands may log freely,

but should avoid excessive logging during normal operation.

---

## 16.10 Future Expansion

Future versions may support

- JSON logs
- rotating logs
- remote logging
- structured logging
- OpenTelemetry

without changing the command API.

---

# 17. Caching

## 17.1 Purpose

Caching exists solely to improve startup performance.

Cache should never change command behavior.

If the cache is deleted,

pcmd should continue functioning correctly.

---

## 17.2 Cached Components

Examples

- command discovery
- parsed metadata
- environment detection
- plugin manifests
- configuration cache

---

## 17.3 Cache Directory

Default location

```
~/.pcmd/cache/
```

Example

```
cache/

commands.php

metadata.php

environments.php
```

Implementation details may evolve.

---

## 17.4 Cache Invalidation

Cache should automatically rebuild when

- command added
- command removed
- command renamed
- command modified
- configuration changes
- pcmd version changes

Manual cache clearing should rarely be necessary.

---

## 17.5 Manual Cache Commands

Reserved internal commands

```
pcmd cache:clear

pcmd cache:rebuild
```

---

## 17.6 Corrupted Cache

If cache corruption is detected,

pcmd should silently rebuild it.

Users should not be required to troubleshoot cache issues.

---

## 17.7 Cache Safety

Cache files are implementation details.

Users should not edit them manually.

---

## 17.8 Performance Goals

Command discovery should avoid recursive filesystem scanning whenever valid cache exists.

Cold start performance should remain acceptable.

Warm starts should be significantly faster.

---

## 17.9 Cache Format

Cache format is intentionally unspecified.

Future versions may change serialization formats without affecting users.

---

## 17.10 Stability

Deleting the cache must never result in data loss.

Only derived data belongs inside cache.

---

# 18. Security

## 18.1 Overview

pcmd frequently performs operations capable of modifying files, databases, and application state.

Security must be considered a first-class concern.

---

## 18.2 Principle of Least Surprise

Commands should never perform destructive actions without the user's knowledge.

Potentially dangerous operations should clearly communicate their intent.

---

## 18.3 Confirmation

Commands that may

- delete
- overwrite
- truncate
- reset
- migrate
- modify production data

should require confirmation unless

```
--yes

or

--force
```

has been supplied.

---

## 18.4 Production Detection

Framework adapters should expose whether the current application is running in production.

Commands should use this information to provide additional safeguards.

---

## 18.5 Process Execution

External processes should always use the Process API.

Commands should avoid constructing shell commands through string concatenation.

Unsafe shell execution is discouraged.

---

## 18.6 Path Handling

Filesystem paths should be normalized before use.

Avoid relying on relative paths internally.

Use the project root or current working directory explicitly.

---

## 18.7 Secrets

Secrets should never appear in

- logs
- exceptions
- progress output
- debug output

unless explicitly requested by the user.

---

## 18.8 Configuration Security

Configuration files may contain

- editor preferences
- tokens
- credentials

Future implementations should support secure secret storage where practical.

---

## 18.9 Third-Party Commands

Commands are executable code.

Users should treat third-party command collections similarly to Composer packages.

Only trusted sources should be installed.

---

## 18.10 Future Security Features

Future releases may introduce

- command signing
- trusted repositories
- permission manifests
- sandboxed execution
- capability declarations

without changing the existing command API.

---

# 19. Examples

## 19.1 Listing Commands

```
$ pcmd list
```

Output

```
General

json:pretty

json:minify

file:hash

git:cleanup

Laravel

search:reindex

queue:retry-all
```

---

## 19.2 Getting Help

```
pcmd help search:reindex
```

---

## 19.3 Pretty Printing JSON

```
pcmd json:pretty data.json
```

---

## 19.4 Running a Laravel Command

```
pcmd search:reindex
```

If Laravel is detected,

the command executes.

Otherwise

```
Environment mismatch

This command requires a Laravel application.
```

---

## 19.5 Dry Run

```
pcmd db:truncate --dry-run
```

Output

```
Dry Run

Tables that would be truncated

products

orders

carts
```

Nothing is modified.

---

## 19.6 Confirmation

```
pcmd db:truncate
```

Output

```
This operation cannot be undone.

Continue?

(y/N)
```

---

## 19.7 Verbose Output

```
pcmd search:reindex --verbose
```

Example

```
Laravel detected.

Loading application...

Loading models...

Reindexing...

Completed.
```

---

## 19.8 Unknown Command

```
pcmd storge:clean
```

Output

```
Unknown command

Did you mean

storage:clean
```

---

## 19.9 Cache

```
pcmd cache:rebuild
```

Output

```
Discovering commands...

Building cache...

Completed.
```

---

## 19.10 Environment

```
pcmd env
```

Output

```
Environment

Laravel

Root

/home/user/shop
```

---

# 20. Appendix

## 20.1 Reserved Directories

```
~/.pcmd/

commands/

helpers/

hooks/

cache/

logs/

config.php
```

---

## 20.2 Reserved Internal Commands

```
help

list

version

doctor

env

config

cache:clear

cache:rebuild

self:update

self:info
```

These names may not be reused by user commands.

---

## 20.3 Reserved Exit Codes

```
0

Success

1

General error

2

Command not found

3

Environment mismatch

4

Invalid arguments

5

Permission denied

6

Configuration error

7

Discovery error

8

Bootstrap error

9

Command execution error

130

Interrupted
```

---

## 20.4 Stability Guarantees

The following public APIs are considered stable within a major version:

- command naming
- Context API
- command discovery behavior
- environment detection semantics
- exit code meanings
- configuration precedence
- help generation

Breaking any of these requires a new major release.

---

## 20.5 Backward Compatibility

Minor releases may

- add commands
- add Context methods
- improve diagnostics
- improve performance
- introduce new adapters

Minor releases must not remove or change documented public behavior.

---

## 20.6 Deprecation Policy

Deprecated features should

- continue functioning
- emit a deprecation warning where appropriate
- remain available until the next major version

Documentation should clearly identify deprecated behavior.

---

## 20.7 Source of Truth

This specification defines the expected behavior of the pcmd executable.

If implementation, examples, or documentation conflict:

1. This specification takes precedence.
2. Architecture documents explain *how* to implement the behavior.
3. Source code must conform to this specification.
