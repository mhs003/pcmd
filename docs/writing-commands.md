# Writing Commands

A pcmd command is a PHP file that returns a `Command` object using the fluent builder API. Commands are discovered automatically from the filesystem — no registration or configuration is needed.

## File Structure

Command files are placed in `~/.pcmd/commands/`, organized by environment and namespace:

```
~/.pcmd/commands/
├── general/
│   ├── json/
│   │   ├── pretty.php       -> json:pretty
│   │   └── validate.php     -> json:validate
│   └── file/
│       ├── hash.php         -> file:hash
│       └── size.php         -> file:size
└── laravel/
    ├── db/
    │   └── truncate.php     -> db:truncate
    └── search/
        └── reindex.php      -> search:reindex
```

The command name is derived from the file path:

| File Path | Command Name |
|-----------|--------------|
| `general/json/pretty.php` | `json:pretty` |
| `general/file/hash.php` | `file:hash` |
| `laravel/db/truncate.php` | `db:truncate` |
| `products/images/repair.php` | `products:images:repair` |

Only `.php` files are scanned. Files starting with `.`, or ending in `.bak`, `.tmp`, or `.disabled` are ignored.

## Basic Command

The simplest command returns a `Command::make()` object with a `run()` callback:

```php
<?php

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Say hello to someone.')
    ->run(function (Context $ctx) {
        $ctx->success('Hello, world!');
        return 0;
    });
```

The `run()` callback receives a `Context` object and should return an integer exit code (0 for success, non-zero for failure).

## Adding Arguments

Use the `argument()` method with a callback to configure each argument:

```php
return Command::make()
    ->description('Pretty print JSON content.')
    ->argument('file', 'Path to JSON file', function ($arg) {
        $arg->file();  // Must be an existing file
    })
    ->run(function (Context $ctx) {
        $file = $ctx->arg('file');
        // ...
    });
```

Arguments are required by default. Make them optional with `$arg->optional()`:

```php
->argument('output', 'Output file', function ($arg) {
    $arg->optional()->default('output.json');
})
```

## Adding Options

Use the `option()` method with a callback:

```php
return Command::make()
    ->description('Compute file hash.')
    ->argument('file', 'Path to file', function ($arg) {
        $arg->file();
    })
    ->option('algo', 'Hash algorithm', function ($opt) {
        $opt->value()->default('sha256');
    })
    ->option('verbose', 'Show detailed output', function ($opt) {
        $opt->boolean();
    })
    ->run(function (Context $ctx) {
        $file = $ctx->arg('file');
        $algo = $ctx->option('algo');
        $verbose = $ctx->option('verbose');
        // ...
    });
```

Options are boolean (flags) by default. Use `$opt->value()` to accept a value.

### Option Shortcuts

```php
->option('force', 'Override existing files', function ($opt) {
    $opt->boolean()->shortcut('f');
})
```

This enables both `--force` and `-f`.

### Allowed Values

```php
->option('driver', 'Database driver', function ($opt) {
    $opt->value()->allowed(['mysql', 'pgsql', 'sqlite']);
})
```

### Multiple Values

```php
->option('path', 'Source paths', function ($opt) {
    $opt->value()->multiple();
})
```

Usage: `--path=/src --path=/lib`

## Argument Validation

Arguments support the following validation chain:

| Method | Description |
|--------|-------------|
| `optional()` | Mark as optional |
| `required()` | Mark as required (default) |
| `default($value)` | Set default value |
| `array()` | Accept multiple values |
| `integer()` | Must be an integer |
| `float()` | Must be a float |
| `boolean()` | Must be a boolean |
| `file()` | Must be an existing file |
| `directory()` | Must be an existing directory |
| `readable()` | Must be readable |
| `writable()` | Must be writable |
| `regex($pattern)` | Must match regex pattern |
| `validate($callback)` | Custom validator function |

## Option Validation

Options support the same validation chain plus:

| Method | Description |
|--------|-------------|
| `shortcut($char)` | Set short option name (e.g., `'f'` for `-f`) |
| `boolean()` | Flag option (no value, default) |
| `value()` | Expects a value (`--name=val`) |
| `allowed($values)` | Enumerate allowed values |
| `multiple()` | Accept multiple occurrences |

## Hidden Commands

Hide a command from `pcmd list`:

```php
return Command::make()
    ->description('Internal maintenance task.')
    ->hidden()
    ->run(function (Context $ctx) {
        // ...
    });
```

Hidden commands can still be executed directly.

## Examples

Provide usage examples for the help output:

```php
return Command::make()
    ->description('Replace text in a file.')
    ->argument('file', 'Path to file', fn($a) => $a->file())
    ->argument('search', 'Text to search for')
    ->argument('replace', 'Replacement text')
    ->option('regex', 'Treat search as regex pattern', fn($o) => $o->boolean())
    ->example('pcmd text:replace file.txt "foo" "bar"')
    ->example('pcmd text:replace --regex file.txt "\d+" "NUMBER"')
    ->run(function (Context $ctx) {
        // ...
    });
```

## Before / After Hooks

Commands can register lifecycle hooks that run before and after the command callback:

```php
return Command::make()
    ->description('Process a file.')
    ->before(function (Context $ctx) {
        $ctx->info('Starting...');
    })
    ->after(function (Context $ctx) {
        $ctx->info('Done.');
    })
    ->run(function (Context $ctx) {
        // Command logic
        return 0;
    });
```

Before hooks run after argument validation but before the command callback. After hooks run after the command callback completes. Multiple `before()` and `after()` calls can be chained — they execute in registration order.

See [Hooks](hooks.md) for details on execution order and file-based hooks.

## Tags

Add tags for future search and categorization:

```php
return Command::make()
    ->description('Pretty print JSON.')
    ->tags(['json', 'formatting', 'utility'])
    ->run(function (Context $ctx) {
        // ...
    });
```

## Complete Example

Here is a complete, production-quality command:

```php
<?php

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Compute file hash using the specified algorithm.')
    ->argument('file', 'Path to the file to hash', function ($arg) {
        $arg->file()->readable();
    })
    ->option('algo', 'Hash algorithm to use', function ($opt) {
        $opt->value()->default('sha256');
    })
    ->option('verbose', 'Display additional information', function ($opt) {
        $opt->boolean()->shortcut('v');
    })
    ->example('pcmd file:hash document.pdf')
    ->example('pcmd file:hash --algo=md5 document.pdf')
    ->example('pcmd file:hash --verbose document.pdf')
    ->tags(['file', 'hash', 'checksum'])
    ->run(function (Context $ctx) {
        $file = $ctx->arg('file');
        $algo = $ctx->option('algo');

        if (!in_array($algo, hash_algos(), true)) {
            $ctx->error('Unknown algorithm: ' . $algo);
            return 1;
        }

        $hash = hash_file($algo, $file);

        if ($hash === false) {
            $ctx->error('Failed to compute hash.');
            return 1;
        }

        $ctx->line($hash . '  ' . $file);

        if ($ctx->option('verbose')) {
            $ctx->info('Algorithm: ' . $algo);
        }

        return 0;
    });
```

## Using Helpers

Helpers are reusable PHP files in `~/.pcmd/helpers/` that any command can load via `$ctx->helper('name')`. See the [Helpers](helpers.md) guide for creating and using helper files.

## Reference

- [Context API](reference/context-api.md) — Full Context reference
- [Argument API](reference/argument-api.md) — Argument builder reference
- [Option API](reference/option-api.md) — Option builder reference
