# Hooks

Hooks allow you to run code before and after every command execution. They are useful for initialization, logging, metrics, and cleanup.

## Hook Files

Hooks are loaded from `~/.pcmd/hooks/`:

| File | Timing | Method |
|------|--------|--------|
| `~/.pcmd/hooks/before.php` | Before every command | `HookRunner::loadBeforeHooks()` |
| `~/.pcmd/hooks/after.php` | After every command | `HookRunner::loadAfterHooks()` |
| `~/.pcmd/hooks/shutdown.php` | On shutdown | `HookRunner::loadShutdownHooks()` |

## Format

### Single Callable

Return a single closure from the hook file:

```php
<?php

use Pcmd\Context\Context;

return function (Context $ctx) {
    if ($ctx->command()->name() === 'db:truncate') {
        $ctx->warn('Running destructive command!');
    }
};
```

### Multiple Callables

Return an array of closures:

```php
<?php

use Pcmd\Context\Context;

return [
    function (Context $ctx) {
        // Log command execution
        $ctx->log()->info('Running: ' . $ctx->command()->name());
    },
    function (Context $ctx) {
        // Verify environment
        if ($ctx->environment()->isProduction()) {
            $ctx->warn('Production environment detected.');
        }
    },
];
```

## Execution Order

```
Before Hooks (in order)
    ↓
Command callback
    ↓
After Hooks (in order, always runs)
    ↓
Shutdown Hooks (always runs)
```

- Before hooks execute in the order they are defined.
- If a before hook throws an exception, execution stops and the command is not invoked.
- After hooks run even if the command succeeds (similar to a `finally` block).
- After hooks do not run if a before hook throws.
- Shutdown hooks run after the command and after hooks, regardless of success or failure.

## Example: Execution Timer

```php
<?php

use Pcmd\Context\Context;

return function (Context $ctx) {
    $start = microtime(true);

    // Register shutdown to report timing
    register_shutdown_function(function () use ($start, $ctx) {
        $elapsed = (microtime(true) - $start) * 1000;
        $ctx->log()->info(sprintf(
            'Command "%s" completed in %.2f ms',
            $ctx->command()->name(),
            $elapsed,
        ));
    });
};
```

## Example: Production Guard

```php
<?php

use Pcmd\Context\Context;

return function (Context $ctx) {
    $dangerous = ['db:truncate', 'migrate:fresh', 'cache:clear-all'];

    if (in_array($ctx->command()->name(), $dangerous, true)) {
        if ($ctx->environment()->isProduction()) {
            $ctx->error('This command cannot run in production.');
            throw new \RuntimeException('Blocked by production guard.');
        }
    }
};
```
