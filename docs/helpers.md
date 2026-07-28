# Helpers

Helpers are reusable PHP files placed in `~/.pcmd/helpers/`. Any command can load them via `$ctx->helper('name')`. This lets you share logic — database queries, Git operations, JSON utilities, file processing — across multiple commands without duplication.

## Directory Structure

```
~/.pcmd/
├── commands/
│   ├── general/
│   └── laravel/
├── helpers/           # <-- helper files go here
│   ├── git.php
│   ├── json.php
│   ├── database.php
│   └── http.php
├── hooks/
├── cache/
└── config.php
```

Helper files are plain PHP files (no command metadata, no `Command::make()`). The filename without `.php` becomes the name used to load the helper.

## Creating a Helper

Create a `.php` file in `~/.pcmd/helpers/`. The file may return any value: an object, an array, a callable, or a class-string.

### Object Return

Best for encapsulating multiple related functions:

```php
<?php
// ~/.pcmd/helpers/git.php

return new class {
    public function currentBranch(): string
    {
        return trim(shell_exec('git rev-parse --abbrev-ref HEAD') ?: '');
    }

    public function isDirty(): bool
    {
        return trim(shell_exec('git status --porcelain') ?? '') !== '';
    }

    public function shortHash(): string
    {
        return trim(shell_exec('git rev-parse --short HEAD') ?: '');
    }
};
```

### Array Return

Simpler helpers, constants, or configuration:

```php
<?php
// ~/.pcmd/helpers/database.php

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: 3306,
    'connection' => function () {
        return new PDO(getenv('DB_DSN') ?: 'sqlite::memory:');
    },
];
```

### Callable Return

Single-function helpers:

```php
<?php
// ~/.pcmd/helpers/format-json.php

return function (mixed $data): string {
    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
};
```

## Using Helpers in Commands

Load a helper by its filename (without `.php`):

```php
<?php

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('Deploy current branch.')
    ->run(function (Context $ctx) {
        $git = $ctx->helper('git');

        if ($git->isDirty()) {
            $ctx->error('Working tree is dirty. Commit or stash changes first.');
            return 1;
        }

        $branch = $git->currentBranch();
        $ctx->info("Deploying {$branch}...");
        // ... deploy logic ...
        return 0;
    });
```

```php
$db = $ctx->helper('database');
$pdo = $db['connection']();
```

```php
$format = $ctx->helper('format-json');
$output = $format(['foo' => 'bar']);
```

## Listing Loaded Helpers

```php
$loaded = $ctx->helpers();  // list<string>, e.g. ['git', 'json']
```

Returns the names of helpers that have been loaded so far in the current command execution.

## Best Practices

- Keep helpers **framework-agnostic** unless they are specific to a project
- Helpers should **not contain command metadata** — no `Command::make()`, no `run()` callbacks
- Helpers may import Composer classes or vendor packages installed globally
- One file per concern (e.g., `git.php`, `json.php`, `http.php`, `images.php`)
- Helpers are **loaded lazily** — the file is `require`d only when `$ctx->helper()` is first called for that name
- Results are **cached in memory** for the lifetime of the command — subsequent `$ctx->helper('git')` calls return the same instance
- A missing helper throws `HelperNotFoundException`

## When to Use Helpers vs Commands

| Use Case | Create a... |
|----------|------------|
| An executable task the user runs | Command in `~/.pcmd/commands/` |
| Shared logic used by multiple commands | Helper in `~/.pcmd/helpers/` |
| A utility with no CLI arguments | Helper (callable form) |
| Framework-specific business logic | Command in `~/.pcmd/commands/laravel/` |

## Example: JSON Helper

```php
<?php
// ~/.pcmd/helpers/json.php

return new class {
    public function pretty(string $data): string
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function minify(string $data): string
    {
        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON: ' . json_last_error_msg());
        }

        return json_encode($decoded, JSON_UNESCAPED_SLASHES);
    }

    public function validate(string $data): bool
    {
        json_decode($data);

        return json_last_error() === JSON_ERROR_NONE;
    }
};
```

```php
$json = $ctx->helper('json');
$ctx->line($json->pretty($raw));
```

## Reference

- [Context API](reference/context-api.md#helpers) — `$ctx->helper()` and `$ctx->helpers()` documentation
- [Writing Commands](writing-commands.md) — Creating commands that use helpers
