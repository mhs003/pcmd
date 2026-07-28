# Contributing

## Development Setup

```bash
git clone https://github.com/your-org/pcmd.git
cd pcmd
composer install
```

## Running Tests

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Code Style

PSR-12 with strict types. Every PHP file starts with `declare(strict_types=1)`.

## Pull Request Process

1. Ensure tests pass (`composer check`)
2. Update documentation if behavior changes
3. Update ROADMAP.md status tables if a phase is completed
4. One feature per pull request

## Project Structure

See `DIRECTORY_STRUCTURE.md` for repository layout. See `ARCHITECTURE.md` for subsystem responsibilities.

## Development Workflow

The project follows the roadmap in `ROADMAP.md`. Phases are implemented sequentially. Each phase includes tests and documentation updates.

## Commit Messages

Write concise commit messages that describe what changed and why. No strict format.

## Reporting Issues

Report bugs by opening a GitHub issue. Include the command you ran, the output, and the expected behavior.
