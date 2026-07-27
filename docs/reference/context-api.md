# Context API

Every command receives a `Context` object as its sole parameter. The Context provides access to all runtime services: arguments, options, terminal output, filesystem operations, process execution, and framework integrations.

```php
return Command::make()
    ->run(function (Context $ctx) {
        // $ctx provides everything you need
    });
```

## Directory Information

```php
$ctx->cwd();    // Current working directory (string)
$ctx->root();   // Project root directory (string, detected by environment)
$ctx->home();   // User home directory (string, from $HOME)
$ctx->temp();   // System temp directory (string, from sys_get_temp_dir())
```

## Environment

```php
$env = $ctx->environment();

$env->type();        // 'generic' or 'laravel'
$env->root();        // Project root path
$env->isGeneric();   // true if no framework detected
$env->isLaravel();   // true if Laravel project detected
```

## Command Metadata

```php
$cmd = $ctx->command();

$cmd->name();              // Command name (e.g., 'json:pretty')
$cmd->description();       // Command description
$cmd->file();              // Path to command file
$cmd->environment();       // Environment ('generic', 'laravel')
$cmd->aliases();           // List of aliases
$cmd->tags();              // List of tags
$cmd->hidden();            // Whether command is hidden
```

## Arguments

```php
// By name (if defined in command metadata)
$ctx->arg('file');

// By index (0-based position)
$ctx->arg(0);

// All arguments
$ctx->arguments();   // array<int|string, mixed>
```

Arguments declared with `->argument()` in the Command builder are available by name after validation. Undeclared positional arguments are available by index.

## Options

```php
// By name
$ctx->option('force');    // bool (for boolean options)
$ctx->option('algo');     // string (for value options)

// All options
$ctx->options();          // array<string, mixed>
```

Options return their parsed and validated values. Boolean options default to `false`, value options default to their declared default or `null`.

## Terminal Output

```php
$ctx->info('Processing...');      // Green text
$ctx->success('Completed.');      // Bright green text
$ctx->warn('Already exists.');    // Yellow text
$ctx->error('Failed.');           // Red text (to stderr)

$ctx->line('Plain text');         // Plain text
$ctx->newline();                  // Blank line
```

## Interactive Input

All input methods automatically respect `--no-interaction` and return defaults when non-interactive.

```php
$name = $ctx->ask('What is your name?');
$name = $ctx->ask('Name', 'default');

$confirmed = $ctx->confirm('Continue?');
$confirmed = $ctx->confirm('Continue?', false);  // Default no

$password = $ctx->secret('Enter password');

$database = $ctx->choice('Select database', ['mysql', 'pgsql']);
$database = $ctx->choice('Select database', ['mysql', 'pgsql'], 'mysql');
```

## Progress Indicators

```php
// Progress bar (determinate)
$bar = $ctx->progress(100);
$bar->start();
for ($i = 0; $i < 100; $i++) {
    usleep(10000);
    $bar->advance();
}
$bar->finish();

// Spinner (indeterminate)
$spinner = $ctx->spinner();
$spinner->start();
// ... long operation ...
$spinner->finish();
```

## Tables

```php
$ctx->table(
    ['Name', 'Size', 'Type'],
    [
        ['file.txt', '1.2 KB', 'text'],
        ['image.png', '340 KB', 'image'],
    ],
);
```

Renders:

```
| Name      | Size   | Type  |
|-----------|--------|-------|
| file.txt  | 1.2 KB | text  |
| image.png | 340 KB | image |
```

## Filesystem

```php
$fs = $ctx->fs();

$content = $fs->read('file.txt');
$fs->write('file.txt', $content);
$fs->copy('from.txt', 'to.txt');
$fs->move('from.txt', 'to.txt');
$fs->delete('file.txt');
$fs->exists('file.txt');         // bool
$fs->mkdir('path/to/dir');

$files = $fs->glob('*.php');     // list<string>

foreach ($fs->walk('directory') as $path) {
    // Generator yielding each file path
}

$tmpFile = $fs->tempFile();
$tmpDir  = $fs->tempDirectory();
```

## Process Execution

```php
$proc = $ctx->process();

// Run and capture output
$result = $proc->capture(['git', 'status']);
echo $result->stdout();
echo $result->stderr();
echo $result->exitCode();
$result->successful();  // bool
$result->failed();      // bool

// Run with streaming output (real-time)
$proc->stream(['npm', 'install']);

// Run without capturing output
$proc->run(['git', 'branch', '-d', 'old-branch']);

// With working directory and timeout
$proc
    ->cwd('/project')
    ->timeout(300)
    ->capture(['composer', 'install']);
```

## Logging

```php
$log = $ctx->log();

$log->debug('Starting import');
$log->info('Processing 500 records');
$log->notice('Skipping empty file');
$log->warning('Deprecated API used');
$log->error('Import failed');
$log->critical('Database unavailable');

// Structured context
$log->info('Import complete', [
    'records' => 500,
    'duration' => 4.25,
]);
```

## Configuration

```php
$config = $ctx->config();

$config->get('key');              // Mixed value or null
$config->get('key', 'default');   // With default
$config->has('key');              // bool
$config->bool('cache.enabled');   // bool or null
$config->int('timeout');          // int or null
$config->string('editor');        // string or null
$config->array('plugins');        // array or null
```

Dot notation provides nested access: `config.get('cache.enabled')`.

## Laravel Integration

```php
$laravel = $ctx->laravel();

if ($laravel !== null) {
    $laravel->app();
    $laravel->artisan()->call('cache:clear');
    $laravel->db()->table('users')->get();
    $laravel->cache()->put('key', 'value', 3600);
}
```

Returns `null` when not inside a Laravel project. See [Laravel Integration](../laravel.md) for details.
