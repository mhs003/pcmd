# pcmd

**Version 0.1.0** — Portable, environment-aware command toolkit for PHP developers.

pcmd is a standalone CLI that lets you write reusable commands once and use them across every PHP project — regardless of framework. Commands are discovered automatically from `~/.pcmd/commands/`. No registration, no configuration, no project modification.

## Features

- **Framework-agnostic** — Works with Laravel, Symfony, WordPress, or plain PHP. Not tied to any framework.
- **Environment-aware** — Commands enable or disable automatically based on the current project.
- **Zero configuration** — Drop a PHP file into `~/.pcmd/commands/` and it's immediately available.
- **Self-documenting** — Every command exposes help automatically from its metadata.
- **Lazy loaded** — Command files are included only when executed, not at startup.
- **Cached discovery** — Filesystem scans are cached with mtime-based invalidation.
- **Bundled commands** — Common utilities (JSON, file, git, Laravel) ship with the app and are discovered automatically.
- **Shared helpers** — Reusable utility libraries loaded on demand via `$ctx->helper('name')`.

## Quick Start

```bash
# Install
curl -fsSL https://raw.githubusercontent.com/your-org/pcmd/main/install.sh | bash

# See what's available
pcmd list

# Get help
pcmd help json:pretty

# Run a command
pcmd json:pretty data.json
```

## Next Steps

- [Installation](installation.md) — Full setup guide
- [Usage](usage.md) — CLI syntax, options, built-in commands
- [Writing Commands](writing-commands.md) — Create your own commands
- [Laravel Integration](laravel.md) — Use pcmd with Laravel projects
- [Configuration](configuration.md) — Configure pcmd behavior
- [Debugging](debugging.md) — Error handling and troubleshooting
- [Helpers](helpers.md) — Shared helper libraries
- [Bundled Commands](usage.md#bundled-commands) — Example commands shipped with pcmd

## Requirements

- PHP 8.3 or later
- Composer (for installation)
