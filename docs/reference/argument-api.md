# Argument API

The `Argument` builder defines a positional command argument with validation rules.

## Creating an Argument

Arguments are created via the `Command::argument()` method:

```php
return Command::make()
    ->argument('file', 'Path to input file', function (Pcmd\Support\Argument $arg) {
        $arg->file()->required();
    })
    ->run(function (Context $ctx) {
        $file = $ctx->arg('file');
    });
```

The callback receives an `Argument` instance for configuration.

## Methods

### name()
```php
$arg->name();  // string
```
Returns the argument name.

### description()
```php
$arg->description();  // string
```
Returns the argument description.

### optional() / required()
```php
$arg->optional();
$arg->required();        // default
$arg->isRequired();      // bool
```
Arguments are required by default. Call `optional()` to make them optional.

### default()
```php
$arg->default('output.json');
$arg->getDefault();
```
Set a default value for optional arguments. The default is used when the argument is not provided.

### array()
```php
$arg->array();
$arg->isArray();  // bool
```
Accept multiple values for this argument.

### integer()
```php
$arg->integer();
```
Validate that the argument value is an integer.

### float()
```php
$arg->float();
```
Validate that the argument value is a float.

### boolean()
```php
$arg->boolean();
```
Validate that the argument value is a boolean.

### file() / directory()
```php
$arg->file();        // Must be an existing file
$arg->directory();   // Must be an existing directory
```

### readable() / writable()
```php
$arg->readable();    // Must be readable
$arg->writable();    // Must be writable
```

### regex()
```php
$arg->regex('/^[a-z_]+$/');
```
Validate the argument against a regex pattern.

### validate()
```php
$arg->validate(function (string $value): void {
    if (strlen($value) < 3) {
        throw new \InvalidArgumentException('Too short.');
    }
});
```
Custom validator callback. Throw an exception to reject the value.
