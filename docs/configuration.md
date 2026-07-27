# Configuration

pcmd is configured through a single file at `~/.pcmd/config.php`. If the file does not exist, sensible defaults are used.

## Config File

Create `~/.pcmd/config.php` returning an array:

```php
<?php

return [
    'colors' => true,
    'verbose' => false,
    'editor' => 'code',
    'cache' => [
        'enabled' => true,
    ],
    'logging' => [
        'enabled' => false,
        'level' => 'warning',
    ],
];
```

## Configuration Precedence

Values are resolved in this order (later overrides earlier):

1. **Defaults** — Built-in default values
2. **User config** — `~/.pcmd/config.php`
3. **Environment overrides** — Future support
4. **CLI flags** — `--verbose`, `--quiet`, etc. (always win)

## Available Options

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `colors` | `bool` | `true` | Enable ANSI color output |
| `verbose` | `bool` | `false` | Enable verbose output by default |
| `editor` | `string` | `'code'` | Default editor command |
| `cache.enabled` | `bool` | `true` | Enable command discovery caching |
| `cache.directory` | `string` | `~/.pcmd/cache` | Cache storage directory |
| `logging.enabled` | `bool` | `false` | Enable file logging |
| `logging.directory` | `string` | `~/.pcmd/logs` | Log storage directory |
| `logging.level` | `string` | `'warning'` | Minimum log level (debug, info, notice, warning, error, critical) |

## Accessing Configuration in Commands

```php
$editor = $ctx->config()->get('editor', 'code');
$cacheEnabled = $ctx->config()->bool('cache.enabled');

if ($ctx->config()->has('custom.setting')) {
    $setting = $ctx->config()->get('custom.setting');
}
```

Typed getters are available:

```php
$ctx->config()->bool('colors');      // ?bool
$ctx->config()->int('timeout');      // ?int
$ctx->config()->string('editor');    // ?string
$ctx->config()->array('plugins');    // ?array
```

Dot notation provides nested access:

```php
$ctx->config()->get('logging.level');
```

## Configuration Errors

If the config file contains malformed data, pcmd reports the error and stops:

```
Configuration Error

/home/user/.pcmd/config.php

Unexpected value: cache = "yes"

Expected: boolean
```

## Default Values

Without a config file, pcmd uses these defaults:

```php
[
    'cache' => [
        'enabled' => true,
        'directory' => '~/.pcmd/cache',
    ],
    'colors' => true,
    'verbose' => false,
    'editor' => 'code',
    'logging' => [
        'enabled' => false,
        'directory' => '~/.pcmd/logs',
        'level' => 'warning',
    ],
]
```
