# Laravel Integration

pcmd can bootstrap a Laravel application and expose its services to commands. This allows you to write commands that use Eloquent, Artisan, the service container, cache, queue, database, and other Laravel features — without adding those commands to your Laravel project.

## How It Works

When you run a command inside a Laravel project directory, pcmd:

1. Detects the Laravel environment via `artisan`, `bootstrap/app.php`, and `vendor/autoload.php`
2. Loads the Composer autoloader from the project root
3. Bootstraps the Laravel application via the Console Kernel
4. Passes the `LaravelAdapter` to your command through `$ctx->laravel()`

Commands placed in `~/.pcmd/commands/laravel/` only appear when inside a Laravel project.

## Checking Availability

```php
$laravel = $ctx->laravel();

if ($laravel === null) {
    $ctx->error('This command requires a Laravel environment.');
    return 3;
}
```

## Accessing Laravel Services

### Application Instance

```php
$app = $laravel->app();
$app->make(\App\Services\PaymentService::class);
```

### Service Container

```php
$container = $laravel->container();
$container->make('redis');
```

### Artisan Commands

```php
$exitCode = $laravel->artisan()->call('cache:clear');
$output = $laravel->artisan()->output();
```

Call any Artisan command programmatically and retrieve its output.

### Database

```php
$db = $laravel->db();

// Query builder
$users = $db->table('users')->where('active', true)->get();

// Eloquent ORM (models are available after Laravel bootstraps)
$user = new \App\Models\User();
$user->name = 'Admin';
$user->email = 'admin@example.com';
$user->save();

// Or querying with Eloquent
$admins = \App\Models\User::where('role', 'admin')->get();

// Raw queries
$db->statement('UPDATE users SET active = ? WHERE id = ?', [false, 1]);

// Transactions
$db->transaction(function () use ($db) {
    $db->table('orders')->insert([...]);
    $db->table('inventory')->decrement('stock', 1);
});
```

### Cache

```php
$cache = $laravel->cache();

$cache->put('key', 'value', 3600);
$value = $cache->get('key');
$cache->forget('key');
$cache->flush();
```

### Configuration

```php
$config = $laravel->config();

$driver = $config->get('database.default');
$config->get('app.name');
$config->set('app.debug', false);  // Runtime only
```

### Queue

```php
$queue = $laravel->queue();

$queue->push(new ProcessPodcast($podcast));
```

### Events

```php
$events = $laravel->events();

$events->dispatch(new UserRegistered($user));
$events->listen(UserRegistered::class, function ($event) {
    // Handle event
});
```

### Storage

```php
$storage = $laravel->storage();

$storage->put('avatars/1.jpg', $contents);
$url = $storage->url('avatars/1.jpg');
```

### Environment

```php
$env = $laravel->environment();    // 'local', 'production', etc.
$laravel->isProduction();          // bool
$version = $laravel->version();    // e.g., '11.0.0'
```

## Example Command

```php
<?php

use Pcmd\Context\Context;
use Pcmd\Support\Command;

return Command::make()
    ->description('List users registered in the last 24 hours.')
    ->option('json', 'Output as JSON', fn($o) => $o->boolean())
    ->example('pcmd users:recent')
    ->example('pcmd users:recent --json')
    ->run(function (Context $ctx) {
        $laravel = $ctx->laravel();

        if ($laravel === null) {
            $ctx->error('This command requires Laravel.');
            return 3;
        }

        $users = $laravel->db()
            ->table('users')
            ->where('created_at', '>=', now()->subDay())
            ->get();

        if ($ctx->option('json')) {
            $ctx->line($users->toJson(JSON_PRETTY_PRINT));
            return 0;
        }

        foreach ($users as $user) {
            $ctx->line($user->email . ' (' . $user->created_at . ')');
        }

        return 0;
    });
```

## Production Safety

When the Laravel application environment is `production`, commands that perform destructive operations should require explicit confirmation:

```php
if ($laravel->isProduction() && !$ctx->option('force')) {
    if (!$ctx->confirm('This will modify production data. Continue?')) {
        $ctx->info('Cancelled.');
        return 0;
    }
}
```

## Troubleshooting

**"This command requires a Laravel environment"** — You are not inside a Laravel project directory, or the project is missing required files (`artisan`, `bootstrap/app.php`, `vendor/autoload.php`).

**"Failed to bootstrap Laravel"** — The Laravel application could not be loaded. Check that `vendor/autoload.php` and `bootstrap/app.php` exist and the project is properly installed.

**Use `--debug` for detailed errors:**

```bash
pcmd --debug db:truncate
```

This shows the full exception class, message, file, line, and stack trace. For SQL errors, the SQL query and bindings are also displayed.
