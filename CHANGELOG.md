# Changelog

## 0.1.0 (unreleased)

Initial development release.

### Added

- Project foundation: repository structure, CI, coding standards
- CLI bootstrap with argument parsing and output handling
- Immutable configuration system with dot-notation access
- Dependency injection container
- Terminal I/O: output methods, progress bar, spinner, table
- Filesystem abstraction (read/write/copy/move/delete/mkdir/glob/walk/temp)
- Process execution (run/capture/stream with timeout support)
- Logging system with PSR-3-style levels and secret masking
- Environment detection (Generic + Laravel)
- Command discovery with mtime-based caching
- Command metadata parsing without executing command code
- Command registry with alias index and duplicate detection
- Command resolution with normalization, env validation, fuzzy suggestions
- Lazy command loading (Command::make(), callables, objects)
- Runtime Context API (arg/option/terminal/fs/process/log/helper)
- Command execution with before/after hooks and exception handling
- Lifecycle hooks (before/after/shutdown via ~/.pcmd/hooks/)
- Built-in commands: help, list, version, env, doctor, cache:clear, cache:rebuild
- Declarative argument/option validation (type/file/regex/custom)
- Laravel adapter with Artisan, DB, cache, queue, events, storage access
- 6 bundled Laravel example commands (db:truncate, search:reindex, etc.)
- 3 bundled general example commands (json:pretty, file:hash, git:cleanup)
- publish:commands built-in for exporting bundled commands
- Helper library system ($ctx->helper('name') from ~/.pcmd/helpers/)
- Plugin architecture (PluginManager, PluginLoader, PluginManifest)
- Debug mode with stack traces and SQL context
- Install script (curl-pipe-to-shell)
- Composer global installation support
- 139 tests across 22 test files (unit, integration, E2E)
