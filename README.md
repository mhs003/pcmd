# pcmd

**Portable, environment-aware command toolkit for PHP developers.**

pcmd is a standalone CLI that lets you write reusable commands once and use them across every project — regardless of framework.

Unlike framework-specific CLIs (Artisan, Symfony Console, WP-CLI), pcmd belongs to *you*, not your project. Drop a command file into `~/.pcmd/commands/` and it's instantly available everywhere.

```bash
$ pcmd list

Environment: generic

General
  json:pretty     Pretty print JSON
  file:hash       Compute file hash
  git:cleanup     Clean up merged branches
```

Inside a Laravel project:

```bash
~/Projects/shop $ pcmd list

Environment: laravel

General
  json:pretty     Pretty print JSON
  file:hash       Compute file hash

Laravel
  db:truncate     Truncate all tables
  search:reindex  Rebuild search indices
```

---

## Features

- **Framework-agnostic** — Works with Laravel, Symfony, or plain PHP. Not tied to any framework.
- **Environment-aware** — Commands appear automatically based on the current project.
- **Declarative validation** — Define arguments and options with type, file, regex, and custom validators.
- **Laravel integration** — Commands can access Eloquent, Artisan, DB, cache, queue, and more via `$ctx->laravel()`.
- **Self-documenting** — Every command exposes help with arguments, options, and examples automatically.
- **Hook system** — Run code before/after every command via `~/.pcmd/hooks/`.
- **Cached discovery** — Filesystem scans are cached with mtime-based invalidation.
- **Debug mode** — `--debug` shows full stack traces and SQL context for errors.

## Installation

```bash
# Clone the repository
git clone https://github.com/your-org/pcmd.git
cd pcmd

# Install dependencies
composer install

# Symlink to your PATH
ln -s "$PWD/bin/pcmd" ~/.local/bin/pcmd
```

Ensure `~/.local/bin` is in your `PATH` (it typically is on modern Linux and macOS).

## Quick Start

```bash
# See available commands
pcmd list

# Get help for a specific command
pcmd help json:pretty

# Run a command
pcmd json:pretty data.json

# Check environment detection
pcmd env

# Run diagnostics
pcmd doctor
```

## Writing Commands

Commands are self-contained PHP files placed in `~/.pcmd/commands/<environment>/<group>/<name>.php`.

```php
<?php

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Pretty print JSON content.')
    ->run(function (Context $ctx) {
        $file = $ctx->arguments()[0] ?? null;

        if ($file === null) {
            $ctx->error('Usage: pcmd json:pretty <file>');
            return 1;
        }

        $decoded = json_decode(file_get_contents($file), true);

        $ctx->line(json_encode($decoded, JSON_PRETTY_PRINT));

        return 0;
    });
```

The filename determines the command name:
- `~/.pcmd/commands/general/json/pretty.php` → `json:pretty`
- `~/.pcmd/commands/laravel/db/truncate.php` → `db:truncate`

### Command API

| Method | Description |
|--------|-------------|
| `->description(string)` | Command description for help output |
| `->alias(string)` | Alternative name for the command |
| `->aliases(string[])` | Multiple aliases |
| `->argument(name, desc?, callback?)` | Define a positional argument with validation |
| `->option(name, desc?, callback?)` | Define a named option with validation |
| `->hidden()` | Hide from command listings |
| `->tags(string[])` | Categorization tags |
| `->example(usage, description?)` | Usage example for help output |
| `->run(callable)` | The command callback receiving `Context $ctx` |

### Context API

The `Context` object provides everything a command needs:

```php
$ctx->arg('file');        // Argument by name
$ctx->arg(0);             // Argument by index
$ctx->option('algo');     // Named option value
$ctx->option('force');    // Boolean option (true/false)

// Environment
$ctx->cwd();              // Current working directory
$ctx->root();             // Project root (detected)
$ctx->environment();      // Environment type + root

// Output
$ctx->info('...');
$ctx->success('...');
$ctx->warn('...');
$ctx->error('...');
$ctx->line('...');
$ctx->newline();

// Interactive input
$ctx->ask('Name?');
$ctx->confirm('Continue?');
$ctx->secret('Password?');
$ctx->choice('DB?', ['mysql', 'pgsql']);

// Progress indicators
$ctx->progress(100);
$ctx->spinner();
$ctx->table($headers, $rows);

// Services
$ctx->config();           // Immutable configuration
$ctx->fs();               // Filesystem operations
$ctx->process();          // External process execution
$ctx->log();              // PSR-3 compatible logger
$ctx->laravel();          // Laravel adapter (null outside Laravel)
$ctx->helper('name');     // Load a helper from ~/.pcmd/helpers/
```

## Environment Detection

pcmd automatically detects the current project by walking up from the working directory.

| Environment | Markers |
|-------------|--------|
| **Generic** | Always available (fallback) |
| **Laravel** | `artisan`, `bootstrap/app.php`, `vendor/autoload.php` |

Commands placed in `~/.pcmd/commands/laravel/` only appear when inside a Laravel project. General commands are always visible.

## Configuration

Optional configuration file at `~/.pcmd/config.php`:

```php
<?php

return [
    'colors' => true,
    'editor' => 'code',
    'cache' => ['enabled' => true],
    'logging' => ['enabled' => false],
];
```

## Built-in Commands

| Command | Description |
|---------|-------------|
| `help` | Display help for a command |
| `list` | List available commands |
| `version` | Display version information |
| `env` | Show detected environment |
| `doctor` | Run system diagnostics |
| `cache:clear` | Clear the discovery cache |
| `cache:rebuild` | Rebuild the discovery cache |

## Debugging

Use `--debug` (or `-d`) to get full error details:

```bash
pcmd --debug replace-user
```

Instead of a plain error message, you get the exception class, file, line, stack trace, and — for Laravel SQL errors — the query and bindings.

## Documentation

Full documentation is available in the `docs/` directory:

- [Installation](docs/installation.md)
- [Usage](docs/usage.md)
- [Writing Commands](docs/writing-commands.md)
- [Laravel Integration](docs/laravel.md)
- [Configuration](docs/configuration.md)
- [Hooks](docs/hooks.md)
- [Debugging](docs/debugging.md)
- [Context API Reference](docs/reference/context-api.md)

## Project Structure

```
~/.pcmd/
├── commands/
│   ├── general/      # Framework-independent commands
│   └── laravel/      # Laravel-specific commands
├── plugins/          # Future plugin support
├── helpers/          # Shared helper libraries
├── hooks/            # Lifecycle hooks
├── cache/            # Discovery cache
├── logs/             # Log files
└── config.php        # User configuration
```

## Development

```bash
# Run tests
composer test

# Static analysis
composer analyse

# Run both
composer check
```

## Philosophy

- **Simple.** Everything should be understandable after reading it once.
- **Fast.** Startup time should be extremely low. Discovery is cached.
- **Portable.** Works anywhere PHP 8.3+ runs. No framework dependency.
- **Framework-aware.** Commands activate automatically based on the current project.
- **Your commands, your way.** One install. Every project. No copying.

---

**License:** MIT
