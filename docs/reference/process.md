# Process API

The process API provides safe, consistent external process execution with proper error handling. Never use PHP's raw `exec()`, `system()`, `shell_exec()`, or `passthru()` directly — always use the process API.

```php
$proc = $ctx->process();
```

## Running Commands

### run()

Execute a command and discard its output:

```php
$result = $proc->run(['git', 'branch', '-d', 'old-branch']);
```

### capture()

Execute a command and return its output:

```php
$result = $proc->capture(['git', 'status']);
echo $result->stdout();
echo $result->stderr();
```

### stream()

Execute a command with real-time output streaming:

```php
$exitCode = $proc->stream(['npm', 'install']);
```

Output is printed to stdout/stderr as it arrives, line by line.

## Process Results

`run()` and `capture()` return a `ProcessResult` object:

```php
$result = $proc->capture(['php', '--version']);

$result->exitCode();      // int (0 = success)
$result->stdout();        // string (standard output)
$result->stderr();        // string (standard error)
$result->successful();    // bool (exit code === 0)
$result->failed();        // bool (exit code !== 0)
```

Always check the result:

```php
$result = $proc->capture(['git', 'status']);

if ($result->failed()) {
    $ctx->error('Git command failed: ' . $result->stderr());
    return 1;
}
```

## Configuration

All configuration methods return a clone, leaving the original unchanged:

### cwd()

Set the working directory for the process:

```php
$proc->cwd('/project')->run(['composer', 'install']);
```

### timeout()

Set a timeout in seconds:

```php
$proc->timeout(300)->run(['composer', 'install']);
```

If the process exceeds the timeout, it is terminated with SIGKILL and a `RuntimeException` is thrown.

### env()

Set additional environment variables:

```php
$proc->env([
    'APP_ENV' => 'production',
    'DEBUG' => '0',
])->run(['php', 'artisan', 'migrate']);
```

The current process environment is inherited automatically.

## Error Handling

`ProcessException` is thrown when the process cannot be started (e.g., command not found):

```php
use Pcmd\Exceptions\ProcessException;

try {
    $result = $proc->capture(['nonexistent-command']);
} catch (ProcessException $e) {
    $ctx->error('Command not found.');
    return 1;
}
```

## Argument Safety

Always pass command arguments as separate array elements. Never use string interpolation or concatenation:

```php
// Safe
$proc->run(['git', 'commit', '-m', $message]);

// Unsafe — shell injection risk
$proc->run(['sh', '-c', "git commit -m '$message'"]);
```
