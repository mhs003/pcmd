# COMMAND_API.md

> **Project:** pcmd
>
> **Version:** 1.0
>
> This document defines the public API for writing pcmd commands.
>
> Everything described here is considered stable within a major version.
>
> Command authors should only rely on APIs documented in this file.

---

# 1. Introduction

## Overview

A pcmd command is a self-contained PHP file that returns a `CommandDefinition`.

Commands are **discovered automatically**.

No registration.

No service providers.

No configuration.

No composer package required.

Simply placing a command inside the correct directory makes it available.

---

## Design Goals

The Command API is designed around these principles.

- Minimal boilerplate
- Easy to learn
- IDE friendly
- Framework independent
- Easily testable
- Explicit over magic
- Lazy loaded

---

## Example

```php
<?php

use Pcmd\Command;

return Command::make()

    ->description('Pretty print JSON.')

    ->argument('file')

    ->run(function (Context $ctx) {

        //

    });
```

---

Every command follows the same structure regardless of environment.

---

# 2. Command File Structure

## File Layout

A command file contains exactly one command.

Example

```
commands/general/json/pretty.php
```

---

The file should return a `CommandDefinition`.

Example

```php
<?php

use Pcmd\Command;

return Command::make()

    ->description('Pretty print JSON.')

    ->run(function (Context $ctx) {

    });
```

---

## Why return?

Returning a definition instead of extending a base class allows

- lazy loading
- smaller API
- fewer objects
- less inheritance

---

## One Command Per File

Allowed

```
pretty.php
```

↓

```
json:pretty
```

---

Not allowed

```
pretty.php

↓

returns 5 commands
```

---

## File Naming

The filename determines the command name.

```
pretty.php

↓

pretty
```

---

The parent directories determine namespaces.

```
json/

pretty.php

↓

json:pretty
```

---

Nested directories

```
products/

images/

repair.php
```

↓

```
products:images:repair
```

---

## File Requirements

Every command file

must

- return CommandDefinition
- be valid PHP
- contain no side effects

Avoid

```php
echo "Loading...";
```

during file loading.

---

## Lazy Loading

Command files are loaded only when executed.

Discovery should never execute user code.

---

# 3. Command Metadata

Metadata describes a command.

It is not executable logic.

---

## Description

Every command should provide one.

```php
->description(
    'Repairs duplicate product images.'
)
```

---

Descriptions should

- be concise
- begin with a verb
- avoid implementation details

Good

```
Repair duplicate images.
```

Bad

```
This command repairs duplicate images by checking...
```

---

## Aliases

Commands may define aliases.

```php
->alias('reindex')
```

Multiple aliases

```php
->aliases([

    'reindex',

    'search:index'

])
```

Aliases must be unique.

---

## Hidden Commands

Some commands may be hidden.

```php
->hidden()
```

Hidden commands

- execute normally
- do not appear in list

---

## Examples

Commands should provide examples.

```php
->example(

    'pcmd json:pretty file.json'

)
```

Multiple examples

```php
->examples([

    ...

])
```

---

## Notes

Optional.

Used for

- warnings
- compatibility
- production notes

---

## Tags

Commands may define tags.

Example

```php
->tags([

    'json',

    'utility',

    'formatting'

])
```

Useful for future search.

---

# 4. Arguments

Arguments are positional.

Example

```
pcmd json:pretty file.json
```

Argument

```
file
```

---

## Defining Arguments

```php
->argument('file')
```

---

With description

```php
->argument(

    'file',

    'JSON file.'

)
```

---

## Required

Arguments are required by default.

---

Optional

```php
->argument(

    'file'

)->optional()
```

---

Default

```php
->argument(

    'driver'

)->default(

    'mysql'

)
```

---

## Multiple Arguments

```php
->argument('source')

->argument('destination')
```

---

## Array Arguments

Future support

```php
->argument('files')

->array()
```

Result

```php
[
    ...
]
```

---

## Access

```php
$ctx->arg('file');
```

or

```php
$ctx->arg(0);
```

---

# 5. Options

Options are named parameters.

Example

```
--force
```

---

Definition

```php
->option('force')
```

---

With description

```php
->option(

    'force',

    'Overwrite files.'

)
```

---

Shortcut

```php
->shortcut('f')
```

Produces

```
-f

--force
```

---

Boolean

```php
->boolean()
```

---

Value

```php
->value()
```

Example

```
--driver=mysql
```

---

Default

```php
->default(

    'mysql'

)
```

---

Enum

```php
->allowed([

    'mysql',

    'pgsql'

])
```

---

Multiple

```php
->multiple()
```

Example

```
--path=a

--path=b
```

↓

```php
[
    'a',

    'b'
]
```

---

Access

```php
$ctx->option('force');
```

---

# 6. Validation

Validation occurs before execution.

Commands should receive valid data.

---

Required

```php
->required()
```

---

Integer

```php
->integer()
```

---

Float

```php
->float()
```

---

Boolean

```php
->boolean()
```

---

Existing file

```php
->file()
```

---

Directory

```php
->directory()
```

---

Readable

```php
->readable()
```

---

Writable

```php
->writable()
```

---

Regex

```php
->regex(

    '/^[a-z]+$/'

)
```

---

Custom Validator

```php
->validate(

    function (

        $value

    ) {

        ...

    }

)
```

Return

```
true
```

or throw ValidationException.

---

# 7. Context API

Every command receives one Context.

```php
function (

    Context $ctx

) {

}
```

---

Never use

Globals

Singletons

Static service locators

---

## Basic Information

Current directory

```php
$ctx->cwd();
```

---

Project root

```php
$ctx->root();
```

---

Home

```php
$ctx->home();
```

---

Temp

```php
$ctx->temp();
```

---

Environment

```php
$ctx->environment();
```

---

Command metadata

```php
$ctx->command();
```

---

Arguments

```php
$ctx->arguments();
```

---

Options

```php
$ctx->options();
```

---

Configuration

```php
$ctx->config();
```

---

# 8. Terminal API

Commands should never use

```php
echo
```

Prefer

Terminal API.

---

Information

```php
$ctx->info(

    'Done.'

);
```

---

Success

```php
$ctx->success(

    'Completed.'

);
```

---

Warning

```php
$ctx->warn(

    'Already exists.'

);
```

---

Error

```php
$ctx->error(

    'Failed.'

);
```

---

Plain output

```php
$ctx->line(

    '...'

);
```

---

Blank line

```php
$ctx->newline();
```

---

Progress

```php
$progress

=

$ctx->progress(

    500

);
```

---

Spinner

```php
$spinner

=

$ctx->spinner();
```

---

Table

```php
$ctx->table(

    $headers,

    $rows

);
```

---

Tree

```php
$ctx->tree(

    $nodes

);
```

---

# 9. Input API

Questions

```php
$name

=

$ctx->ask(

    'Name'

);
```

---

Default

```php
$ctx->ask(

    'Driver',

    'mysql'

);
```

---

Confirmation

```php
$ctx->confirm(

    'Continue?'

);
```

---

Secret

```php
$ctx->secret(

    'Password'

);
```

---

Choice

```php
$ctx->choice(

    'Database',

    [

        'mysql',

        'pgsql'

    ]

);
```

---

Multiple Choice

```php
$ctx->multichoice(

    ...

);
```

---

Behavior

All methods automatically respect

```
--no-interaction
```

---

# 10. Filesystem API

Filesystem operations should go through Context.

Never call raw PHP filesystem functions unless absolutely necessary.

---

Filesystem

```php
$fs

=

$ctx->fs();
```

---

Read

```php
$fs->read(

    'file.txt'

);
```

---

Write

```php
$fs->write(

    'file.txt',

    $content

);
```

---

Exists

```php
$fs->exists(

    'file.txt'

);
```

---

Copy

```php
$fs->copy(

    $from,

    $to

);
```

---

Move

```php
$fs->move(

    $from,

    $to

);
```

---

Delete

```php
$fs->delete(

    $path

);
```

---

Create Directory

```php
$fs->mkdir(

    $dir

);
```

---

Walk

```php
$fs->walk(

    $directory
);
```

---

Glob

```php
$fs->glob(

    '*.php'
);
```

---

Temporary File

```php
$temp

=

$fs->tempFile();
```

---

Temporary Directory

```php
$temp

=

$fs->tempDirectory();
```

---

Design Goals

The Filesystem API should:

- normalize paths
- provide consistent exceptions
- be fully mockable
- remain platform-independent
- avoid exposing raw PHP warnings

---

# 11. Process API

The Process API provides a safe and consistent way to execute external programs.

Commands should avoid using

```php
exec()

system()

shell_exec()

passthru()
```

---

Obtain the process manager

```php
$process = $ctx->process();
```

---

Run

```php
$result = $process->run([

    'git',

    'status'

]);
```

---

Capture Output

```php
$result = $process->capture([

    'php',

    '--version'

]);
```

---

Stream Output

```php
$process->stream([

    'npm',

    'install'

]);
```

---

Working Directory

```php
$process

    ->cwd('/project')

    ->run([...]);
```

---

Timeout

```php
$process

    ->timeout(300)

    ->run([...]);
```

---

Environment Variables

```php
$process

    ->env([

        'APP_ENV' => 'production'

    ]);
```

---

Exit Code

```php
$result->exitCode();
```

---

Stdout

```php
$result->stdout();
```

---

Stderr

```php
$result->stderr();
```

---

Success

```php
$result->successful();
```

---

Failed

```php
$result->failed();
```

---

Commands should inspect the result instead of assuming success.

---

# 12. Logger API

Every command has access to a logger.

```php
$log = $ctx->log();
```

---

Debug

```php
$log->debug(

    'Loaded.'

);
```

---

Info

```php
$log->info(

    'Started.'

);
```

---

Notice

```php
$log->notice(

    'Skipped.'

);
```

---

Warning

```php
$log->warning(

    'Already exists.'

);
```

---

Error

```php
$log->error(

    'Failed.'

);
```

---

Critical

```php
$log->critical(

    'Database unavailable.'

);
```

---

Structured Context

```php
$log->info(

    'Import complete.',

    [

        'products' => 514,

        'duration' => 4.25

    ]

);
```

---

Loggers should never expose secrets.

---

# 13. Configuration API

Commands access configuration through Context.

Never load configuration files manually.

---

Get Config

```php
$config = $ctx->config();
```

---

Read

```php
$config->get(

    'cache.enabled'

);
```

---

Default

```php
$config->get(

    'editor',

    'code'

);
```

---

Exists

```php
$config->has(

    'cache.enabled'

);
```

---

Boolean

```php
$config->bool(

    'colors'
);
```

---

Integer

```php
$config->int(

    'timeout'
);
```

---

String

```php
$config->string(

    'editor'
);
```

---

Array

```php
$config->array(

    'plugins'
);
```

---

Configuration is immutable.

Commands must never modify it.

---

# 14. Laravel Adapter API

Laravel commands gain access to the adapter through

```php
$laravel = $ctx->laravel();
```

Outside Laravel

```php
null
```

or an adapter exception.

---

Application

```php
$laravel->app();
```

---

Container

```php
$laravel->container();
```

---

Artisan

```php
$laravel

    ->artisan()

    ->call(

        'cache:clear'

    );
```

---

Database

```php
$db = $laravel->db();
```

---

Transaction

```php
$db->transaction(

    function () {

    }

);
```

---

Cache

```php
$laravel

    ->cache()

    ->put(...);
```

---

Config

```php
$laravel

    ->config()

    ->get(...);
```

---

Queue

```php
$laravel

    ->queue();
```

---

Events

```php
$laravel

    ->events();
```

---

Filesystem

```php
$laravel

    ->storage();
```

---

Environment

```php
$laravel

    ->environment();
```

---

Production

```php
$laravel

    ->isProduction();
```

---

Commands should use the adapter instead of bootstrapping Laravel manually.

---

# 15. Future Adapter API

Future adapters should mirror the Laravel adapter.

Example

```php
$ctx->symfony();

$ctx->wordpress();

$ctx->drupal();

$ctx->magento();
```

---

Core APIs should remain identical.

Only framework-specific services change.

---

Every adapter should expose

```
Application

Configuration

Filesystem

Environment

Container
```

where applicable.

---

Adding adapters must not require changing existing command code.

---

# 16. Hook API

Commands may register lifecycle hooks.

---

Before

```php
->before(

    function (

        Context $ctx

    ) {

    }

)
```

---

After

```php
->after(

    function (

        Context $ctx

    ) {

    }

)
```

---

Boot

Runs after Context creation.

---

Shutdown

Runs before application exit.

---

Hook failures abort execution unless documented otherwise.

---

Hooks should be lightweight.

Avoid business logic.

---

# 17. Return Values

Commands may simply finish.

```php
return;
```

---

Explicit success

```php
return 0;
```

---

Failure

```php
return 1;
```

---

Preferred failure

```php
throw new RuntimeException(

    'Import failed.'

);
```

---

Avoid returning arbitrary integers.

Prefer exceptions for exceptional situations.

---

# 18. Error Handling

Commands should throw typed exceptions whenever possible.

Examples

```php
ValidationException

FilesystemException

ProcessException

RuntimeException
```

---

Avoid

```php
die();

exit();

trigger_error();
```

---

Terminal output should remain user-friendly.

Detailed diagnostics belong in logs or debug mode.

---

# 19. Best Practices

## Keep Commands Small

One command should solve one problem.

---

## Prefer Context

Do not construct your own services.

Use Context.

---

## Avoid Global State

Never rely on globals or static mutable variables.

---

## Validate Early

Use declarative validation instead of manual checks whenever possible.

---

## Use Progress Indicators

Long-running commands should display progress.

---

## Support Dry Run

Whenever practical, destructive commands should implement

```
--dry-run
```

---

## Confirm Dangerous Operations

Require confirmation before destructive changes.

---

## Respect Non-Interactive Mode

Never block when

```
--no-interaction
```

is enabled.

---

## Log Meaningful Information

Log events that help diagnose failures.

Avoid excessive logging.

---

## Write Framework-Independent Commands

If a command doesn't need Laravel, place it in

```
general/
```

not

```
laravel/
```

---

## Test Commands

Business logic should be testable independently of CLI execution.

---

# 20. Complete Examples

## General Command

```php
<?php

use Pcmd\Command;
use Pcmd\Context;

return Command::make()

    ->description('Pretty print a JSON file.')

    ->argument('file')

    ->run(function (Context $ctx) {

        $file = $ctx->arg('file');

        $json = json_decode(

            $ctx->fs()->read($file),

            true,

            flags: JSON_THROW_ON_ERROR

        );

        $ctx->fs()->write(

            $file,

            json_encode(

                $json,

                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES

            )

        );

        $ctx->success('Done.');

    });
```

---

## Laravel Command

```php
<?php

use App\Models\Product;
use Pcmd\Command;
use Pcmd\Context;

return Command::make()

    ->description(

        'Reindex products.'

    )

    ->option('queue')

    ->boolean()

    ->run(function (

        Context $ctx

    ) {

        Product::query()

            ->chunk(

                100,

                function (

                    $products

                ) {

                    //

                }

            );

        $ctx->success(

            'Completed.'

        );

    });
```

---

# 21. API Versioning

The public Command API follows semantic versioning.

---

Patch Releases

May

- fix bugs
- improve performance
- improve diagnostics

Must not change public behavior.

---

Minor Releases

May

- add Context methods
- add validation rules
- add helper APIs
- add adapter methods

Must remain backward compatible.

---

Major Releases

May

- remove deprecated APIs
- redesign interfaces
- change command definitions

Only after a documented migration path.

---

## Stability Promise

The following APIs are considered stable within a major version:

- Command builder
- Context API
- Terminal API
- Filesystem API
- Process API
- Logger API
- Configuration API
- Framework adapter contracts
- Validation API
- Hook API

Command authors should rely only on documented APIs.

Internal implementation details may change without notice.

---

# 22. Deprecation Policy

Deprecated APIs remain available until the next major release.

Deprecated methods should:

- continue functioning,
- emit deprecation notices in debug mode,
- include migration guidance in the documentation.

---

# 23. Compatibility Guidelines

Commands should target the documented API rather than implementation details.

Do not rely on:

- internal namespaces,
- undocumented classes,
- filesystem layout,
- cache formats,
- bootstrap order beyond what is documented.

---

# 24. Final Notes

This document defines the **only supported API** for command authors.

Anything not documented here should be considered an internal implementation detail.

The design goals of the API are:

- Minimal learning curve
- Strong IDE support
- Predictable behavior
- Excellent testability
- Framework independence
- Long-term backward compatibility

Future versions should prefer **adding** capabilities rather than changing existing APIs.

When in doubt:

- depend on `Context`,
- keep commands small,
- avoid framework coupling,
- use the provided abstractions,
- and write commands as if they may be reused for years across many different projects.