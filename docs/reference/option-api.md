# Option API

The `Option` builder defines a named command option with validation rules.

## Creating an Option

Options are created via the `Command::option()` method:

```php
return Command::make()
    ->option('algo', 'Hash algorithm', function (Pcmd\Support\Option $opt) {
        $opt->value()->default('sha256');
    })
    ->option('force', 'Override existing files', function ($opt) {
        $opt->boolean()->shortcut('f');
    })
    ->run(function (Context $ctx) {
        $algo = $ctx->option('algo');
        $force = $ctx->option('force');
    });
```

The callback receives an `Option` instance for configuration.

## Methods

### name()
```php
$opt->name();  // string
```
Returns the option name.

### description()
```php
$opt->description();  // string
```
Returns the option description.

### shortcut()
```php
$opt->shortcut('f');
$opt->getShortcut();  // ?string
```
Set a short option alias. Enables `-f` alongside `--force`.

### boolean() / value()
```php
$opt->boolean();    // Flag option, no value required (default)
$opt->value();      // Expects a value (--name=val or --name val)
$opt->valueType();  // 'boolean' or 'value'
```

Boolean options default to `false` when not provided.

### default()
```php
$opt->default(30);
$opt->getDefault();
```
Set a default value. Applied when the option is not provided.

### allowed()
```php
$opt->allowed(['mysql', 'pgsql', 'sqlite']);
$opt->getAllowed();  // ?string[]
```
Restrict option values to an enumerated list.

### multiple()
```php
$opt->multiple();
$opt->isMultiple();  // bool
```
Accept the option multiple times. Values are collected into an array:

```bash
pcmd sync --path=/src --path=/lib
```

### integer()
```php
$opt->integer();
```
Validate that the option value is an integer.

### float()
```php
$opt->float();
```
Validate that the option value is a float.

### file() / directory()
```php
$opt->file();        // Must be an existing file
$opt->directory();   // Must be an existing directory
```

### readable() / writable()
```php
$opt->readable();    // Must be readable
$opt->writable();    // Must be writable
```

### regex()
```php
$opt->regex('/^[a-z_]+$/');
```
Validate the option value against a regex pattern.

### validate()
```php
$opt->validate(function (string $value): void {
    if (!is_dir($value)) {
        throw new \InvalidArgumentException('Not a directory.');
    }
});
```
Custom validator callback.

## Unknown Options

Unknown options (not declared in the command definition and not global options) produce a validation error:

```bash
$ pcmd json:pretty --banana
Unknown option: --banana
```

## Global Options

These options are always recognized and never produce validation errors:

- `--help`, `-h`
- `--version`
- `--verbose`, `-v`
- `--quiet`, `-q`
- `--debug`, `-d`
- `--yes`, `-y`
- `--no-interaction`
- `--no-ansi`
- `--dry-run`
- `--force`, `-f`
