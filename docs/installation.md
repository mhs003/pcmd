# Installation

## Requirements

- PHP 8.3 or later
- Composer

## Download

Clone the repository and install dependencies:

```bash
git clone https://github.com/your-org/pcmd.git
cd pcmd
composer install
```

## Install the Binary

Symlink the executable into your PATH:

```bash
ln -s "$PWD/bin/pcmd" ~/.local/bin/pcmd
```

Ensure `~/.local/bin` is in your PATH (it typically is on modern Linux and macOS). If not, add this line to your `~/.bashrc` or `~/.zshrc`:

```bash
export PATH="$HOME/.local/bin:$PATH"
```

Verify the installation:

```bash
pcmd --version
# pcmd 0.1.0
```

## Directory Structure

pcmd stores its data in `~/.pcmd/`:

```
~/.pcmd/
├── commands/
│   ├── general/      # Framework-independent commands
│   └── laravel/      # Laravel-specific commands
├── plugins/          # Future plugin support
├── helpers/          # Shared helper libraries
├── hooks/            # Lifecycle hooks
├── cache/            # Discovery cache
├── logs/             # Log files
└── config.php        # User configuration
```

The `commands/` directory is created on first run if it doesn't exist. You can place command files into it at any time.

## Bundled Commands

pcmd ships with example commands in `resources/commands/` (general + Laravel). These are discovered automatically on every install — no setup required. User commands in `~/.pcmd/commands/` take precedence over bundled ones.

To customize or remove bundled commands, publish them to your home directory:

```bash
pcmd publish:commands
```

After publishing, you can edit or delete any command file in `~/.pcmd/commands/`.

## Verifying

Run the built-in diagnostics to verify everything is working:

```bash
pcmd doctor
```

This checks PHP version, home directory, and temp directory availability.

## Next Steps

- [Usage Guide](usage.md) — Learn the CLI syntax
- [Writing Commands](writing-commands.md) — Create your first command
