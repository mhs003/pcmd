# Usage

## CLI Syntax

```
pcmd [global-options] command [arguments] [options]
```

Arguments are positional values passed to the command. Options are named flags or key-value pairs.

### Examples

```bash
pcmd list
pcmd help json:pretty
pcmd json:pretty data.json
pcmd file:hash --algo=md5 document.pdf
pcmd db:truncate --force
pcmd --debug search:reindex
```

## Global Options

These options are available for every command:

| Option | Short | Description |
|--------|-------|-------------|
| `--help` | `-h` | Display help for the command |
| `--version` | | Display version information |
| `--verbose` | `-v` | Enable verbose output |
| `--quiet` | `-q` | Suppress non-essential output |
| `--debug` | `-d` | Enable debug mode (full error details) |
| `--yes` | `-y` | Automatic yes to prompts |
| `--no-interaction` | | Disable interactive prompts |
| `--no-ansi` | | Disable ANSI color output |
| `--dry-run` | | Simulate execution without making changes |

Global options can appear anywhere in the command:

```bash
pcmd --verbose json:pretty data.json
pcmd json:pretty --verbose data.json
```

## Built-in Commands

pcmd ships with these built-in commands. They are always available regardless of environment.

### help

Display help for a command:

```bash
pcmd help
pcmd help json:pretty
pcmd help file:hash
```

Help output includes description, usage, arguments, options, aliases, and examples.

### list

List all available commands, grouped by environment:

```bash
pcmd list
```

Output groups commands into "General" and the current environment section.

### version

Display version information:

```bash
pcmd --version
```

Shows pcmd version, PHP version, and platform.

### env

Show detected environment information:

```bash
pcmd env
```

Output includes the environment type (generic, laravel) and the project root directory.

### doctor

Run system diagnostics:

```bash
pcmd doctor
```

Checks PHP version, home directory existence, and temp directory availability.

### cache:clear

Clear the discovery cache:

```bash
pcmd cache:clear
```

Forces a fresh filesystem scan on the next command execution.

### cache:rebuild

Rebuild the discovery cache:

```bash
pcmd cache:rebuild
```

Clears and prepares for a fresh cache rebuild.

## Exit Codes

pcmd commands return the following exit codes:

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | General error |
| 2 | Command not found |
| 3 | Environment mismatch |
| 4 | Invalid arguments |
| 5 | Permission denied |
| 6 | Configuration error |
| 7 | Discovery error |
| 8 | Bootstrap error |
| 9 | Command execution error |
| 130 | Interrupted (Ctrl+C) |

Commands may return additional codes for command-specific conditions.

## Command Suggestions

If you mistype a command name, pcmd suggests corrections using fuzzy matching:

```bash
$ pcmd storge:clean
Unknown command: storge:clean

Did you mean:
  storage:clean
```

## Environment Awareness

Commands are automatically enabled or disabled based on the current working directory:

- **General commands** are always available everywhere.
- **Laravel commands** only appear when inside a Laravel project.

## Next Steps

- [Writing Commands](writing-commands.md) — Create your own commands
- [Configuration](configuration.md) — Configure pcmd behavior
- [Debugging](debugging.md) — Error handling and troubleshooting
