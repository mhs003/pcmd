# Debugging

## Debug Mode

Use `--debug` (or `-d`) to enable detailed error output:

```bash
pcmd --debug replace-user
```

Without debug mode, errors show only the message:

```
Error: SQLSTATE[42S02]: Base table or view not found:...
```

With `--debug`, the full exception details are displayed:

```
RuntimeException: SQLSTATE[42S02]: Base table or view not found:...
  File: /project/vendor/laravel/framework/src/Illuminate/Database/Connection.php:432

SQL: SELECT * FROM `non_existent_table` WHERE `id` = ?
Bindings: [1]

Stack trace:
  #0 /project/vendor/laravel/framework/src/Illuminate/Database/Connection.php:339  PDO->query
  #1 /project/vendor/laravel/framework/src/Illuminate/Database/Connection.php:432  Illuminate\Database\Connection->runQueryCallback
  #2 ...
```

For Laravel `QueryException` errors, the SQL query and bindings are shown automatically in debug mode.

## Error Categories

pcmd uses typed exceptions for different failure categories:

| Exception | Cause |
|-----------|-------|
| `ConfigurationException` | Invalid config file |
| `DiscoveryException` | Command discovery failure |
| `ValidationException` | Invalid arguments or options |
| `BootstrapException` | Framework bootstrap failure |
| `ExecutionException` | Command execution error |
| `EnvironmentException` | Environment detection failure |
| `FilesystemException` | File operation failure |
| `ProcessException` | External process failure |

## Exit Codes

| Code | Meaning | When |
|------|---------|------|
| 0 | Success | Command completed normally |
| 1 | General error | Unspecified command failure |
| 2 | Command not found | Unknown command name |
| 3 | Environment mismatch | Command requires different environment |
| 4 | Invalid arguments | Missing required arg, unknown option, type error |
| 5 | Permission denied | File permission issues |
| 6 | Configuration error | Malformed config file |
| 7 | Discovery error | Command discovery failed |
| 8 | Bootstrap error | Framework could not be loaded |
| 9 | Command execution error | Unhandled exception in command |
| 130 | Interrupted | User pressed Ctrl+C |

## Verbose Mode

`--verbose` enables additional diagnostic output. Unlike `--debug`, it does not show stack traces.

## Common Issues

### "Command not found"

```bash
pcmd storge:clean
Unknown command: storge:clean

Did you mean:
  storage:clean
```

The command was not found. Suggestions are shown when a close match exists.

### "This command requires a Laravel environment"

You are not inside a Laravel project, or the project is missing required files. Run `pcmd env` to check what environment was detected.

### "Failed to bootstrap Laravel"

The Laravel application could not be loaded. Common causes:

- Missing vendor directory (run `composer install`)
- Missing or broken `bootstrap/app.php`
- PHP version incompatibility

Run with `--debug` to see the full error.

### Commands not appearing in list

Ensure command files are in the correct directory:

```
~/.pcmd/commands/general/your-command.php
```

Files must:
- Have a `.php` extension
- Not start with a dot (`.`)
- Not end with `.bak`, `.tmp`, or `.disabled`
- Return a `Command::make()` object

### Cache issues

If changes to command files are not reflected:

```bash
pcmd cache:clear
```

This forces a fresh filesystem scan on the next run.
