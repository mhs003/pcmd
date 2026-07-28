# DIRECTORY_STRUCTURE.md

> **Project:** pcmd
>
> **Version:** 1.0
>
> This document defines the official repository layout, runtime directory structure, user home directory structure, naming conventions, and ownership responsibilities of every directory in the project.
>
> This document is authoritative.
>
> If implementation differs from this document, the implementation should be considered incorrect.

---

# 1. Philosophy

Directory structures exist for humans.

A developer should be able to open the repository and immediately understand:

- where code belongs
- where commands belong
- where tests belong
- where plugins belong
- where documentation belongs

The directory tree should communicate architecture.

---

## Core Rules

### Rule 1

Directories represent responsibilities.

---

### Rule 2

Avoid catch-all directories.

Bad:

```
Helpers/

Utils/

Misc/

Stuff/

Common/
```

---

### Rule 3

Prefer explicit names.

Good:

```
Discovery/

Execution/

Registry/

Filesystem/
```

---

### Rule 4

One subsystem owns one directory.

---

### Rule 5

Directory names should remain stable.

Changing directory layouts is a breaking contributor experience.

---

# 2. Repository Layout

The repository root.

```
pcmd/

├── src/
├── tests/
├── docs/
├── resources/
├── bin/
├── install.sh
├── composer.json
├── composer.lock
├── phpunit.xml
├── README.md
├── LICENSE
└── .gitignore
```

---

# 3. Root Directory Responsibilities

## src/

Contains application source code.

No tests.

No documentation.

No examples.

---

## tests/

Contains all automated tests.

---

## docs/

Contains project documentation.

---

## resources/

Contains templates and bundled assets.

---

## bin/

Contains executable entrypoints.

---

## install.sh

Standalone installer script. Supports `install`, `update`, and `uninstall`.

Usage:

```bash
curl -fsSL https://raw.githubusercontent.com/your-org/pcmd/main/install.sh | bash
```

---

## scripts/

Contains development tooling.

---

# 4. Source Tree

Official source structure.

```
src/

├── Application/
├── CLI/
├── Configuration/
├── Context/
├── Contracts/
├── Discovery/
├── Environment/
├── Execution/
├── Filesystem/
├── Framework/
├── Logging/
├── Process/
├── Registry/
├── Resolution/
├── Support/
├── Terminal/
└── Exceptions/
```

---

# 5. Application Directory

```
src/Application/
```

Contains application orchestration.

Examples

```
Application.php

Kernel.php

Bootstrapper.php
```

---

## Responsibilities

Owns lifecycle.

Coordinates subsystems.

---

## Must Not Contain

Filesystem logic.

Laravel logic.

Discovery logic.

Command logic.

---

# 6. CLI Directory

```
src/CLI/
```

Contains command line bootstrap code.

Examples

```
ArgvParser.php

Input.php

Output.php

ConsoleApplication.php
```

---

## Responsibilities

Read terminal input.

Render terminal output.

Pass control to Application.

---

## Must Not Contain

Business logic.

Framework logic.

---

# 7. Configuration Directory

```
src/Configuration/
```

Examples

```
Config.php

ConfigLoader.php

Defaults.php
```

---

## Responsibilities

Load configuration.

Validate configuration.

Provide immutable config access.

---

# 8. Context Directory

```
src/Context/
```

Examples

```
Context.php

RuntimeContext.php

ContextFactory.php
```

---

## Responsibilities

Expose APIs to commands.

---

## Must Not Contain

Discovery.

Registry.

Environment detection.

---

# 9. Contracts Directory

```
src/Contracts/
```

Contains interfaces.

Examples

```
FilesystemInterface.php

LoggerInterface.php

ProcessInterface.php

EnvironmentDetectorInterface.php

FrameworkAdapterInterface.php
```

---

## Rule

High-level systems depend on Contracts.

Not implementations.

---

# 10. Discovery Directory

```
src/Discovery/
```

Examples

```
CommandDiscovery.php

CommandScanner.php

MetadataParser.php

DiscoveryCache.php
```

---

## Responsibilities

Locate command files.

Build metadata.

Create registry entries.

---

## Must Not Contain

Execution.

Framework bootstrap.

---

# 11. Environment Directory

```
src/Environment/
```

Contains environment detection.

---

Examples

```
Environment.php

EnvironmentManager.php

Detectors/
```

---

# 12. Detector Layout

```
src/Environment/

├── Environment.php
├── EnvironmentManager.php
└── Detectors/

    ├── GenericDetector.php
    ├── LaravelDetector.php
    ├── SymfonyDetector.php
    └── WordPressDetector.php
```

---

## Rule

Every environment receives its own detector.

---

# 13. Execution Directory

```
src/Execution/
```

Examples

```
CommandExecutor.php

CommandLoader.php

HookRunner.php

ExitCodeResolver.php
```

---

## Responsibilities

Execute commands.

Run hooks.

Handle failures.

---

# 14. Filesystem Directory

```
src/Filesystem/
```

Examples

```
Filesystem.php

Path.php

TemporaryFiles.php
```

---

## Responsibilities

Filesystem abstraction.

Path normalization.

Temporary resources.

---

# 15. Framework Directory

```
src/Framework/
```

Contains framework adapters.

---

Structure

```
src/Framework/

├── Contracts/
├── Laravel/
├── Symfony/
├── WordPress/
└── Generic/
```

---

# 16. Laravel Adapter Layout

```
src/Framework/Laravel/

├── LaravelAdapter.php
├── LaravelBootstrapper.php
├── LaravelDatabase.php
├── LaravelCache.php
├── LaravelQueue.php
└── LaravelStorage.php
```

---

## Rule

All Illuminate dependencies stay here.

Never leak into core.

---

# 17. Logging Directory

```
src/Logging/
```

Examples

```
Logger.php

FileLogger.php

NullLogger.php
```

---

## Responsibilities

Logging infrastructure.

Nothing else.

---

# 18. Process Directory

```
src/Process/
```

Examples

```
ProcessManager.php

ProcessResult.php

ProcessBuilder.php
```

---

## Responsibilities

External process execution.

---

# 19. Registry Directory

```
src/Registry/
```

Examples

```
CommandRegistry.php

CommandMetadata.php

AliasIndex.php
```

---

## Responsibilities

Store command metadata.

Lookup commands.

---

# 20. Resolution Directory

```
src/Resolution/
```

Examples

```
CommandResolver.php

ResolvedCommand.php
```

---

## Responsibilities

Resolve commands.

Validate commands.

---

# 21. Support Directory

```
src/Support/
```

Contains small reusable helpers.

---

Allowed

```
Strings.php

Arrays.php

Collections.php
```

---

Not Allowed

Business logic.

Framework logic.

---

# 22. Terminal Directory

```
src/Terminal/
```

Examples

```
Terminal.php

Table.php

Tree.php

ProgressBar.php

Spinner.php
```

---

## Responsibilities

Terminal rendering.

---

# 23. Exceptions Directory

```
src/Exceptions/
```

Examples

```
ConfigurationException.php

DiscoveryException.php

BootstrapException.php

ValidationException.php

CommandException.php
```

---

## Rule

All custom exceptions live here.

---

# 24. Test Layout

```
tests/

└── Unit/
    ├── CLI/
    ├── Configuration/
    ├── Context/
    ├── Discovery/
    ├── Environment/
    ├── Execution/
    ├── Framework/
    ├── Registry/
    ├── Resolution/
    └── Support/
```

Future directories (Integration/, EndToEnd/, Fixtures/, Helpers/) will be added in subsequent phases.

---

# 25. Unit Tests

```
tests/Unit/
```

Mirror source structure.

Example

```
tests/Unit/

Discovery/

Registry/

Resolution/
```

---

# 26. Integration Tests

```
tests/Integration/
```

Verify subsystem interaction.

Examples

```
DiscoveryRegistryTest.php

ResolverExecutorTest.php
```

---

# 27. End To End Tests

```
tests/EndToEnd/
```

Execute real CLI commands.

Example

```
ListCommandTest.php

HelpCommandTest.php
```

---

# 28. Fixtures

```
tests/Fixtures/
```

Contains test projects.

---

Example

```
Fixtures/

laravel-app/

generic/

invalid-config/

duplicate-commands/
```

---

# 29. Documentation Layout

```
docs/

├── configuration.md
├── debugging.md
├── helpers.md
├── hooks.md
├── index.md
├── installation.md
├── laravel.md
├── usage.md
├── writing-commands.md
├── reference/
│   ├── argument-api.md
│   ├── context-api.md
│   ├── filesystem.md
│   ├── option-api.md
│   ├── process.md
│   └── terminal.md
└── adr/             (future)
```

Root-level documentation files:

```
AGENTS.md
ARCHITECTURE.md
COMMAND_API.md
DIRECTORY_STRUCTURE.md
ROADMAP.md
SPECIFICATION.md
```

---

# 30. ADR Directory (Future)

```
docs/adr/
```

Planned location for architectural decision records.

---

Example

```
ADR-0001-Context.md

ADR-0002-Lazy-Loading.md
```

Not yet created. ADRs will be added as architectural changes are made.

---

# 31. Resources Layout

```
resources/

└── commands/
    ├── general/
    │   ├── file/
    │   ├── git/
    │   └── json/
    └── laravel/
        ├── cache/
        ├── db/
        ├── job/
        ├── search/
        └── users/
```

---

# 32. Stub Directory

```
resources/stubs/
```

Contains generated file templates.

---

Examples

```
command.php.stub

plugin.php.stub

config.php.stub
```

---

# 33. Example Directory

```
resources/examples/
```

Contains sample commands.

---

Examples

```
json-pretty/

laravel-reindex/

file-hash/
```

---

# 34. Binary Directory

```
bin/
```

Contains executable entrypoints.

---

Example

```
bin/pcmd
```

---

Rule

Exactly one primary executable.

---

# 35. Scripts Directory

```
scripts/
```

Development tooling only.

---

Examples

```
release.php

build.php

benchmark.php
```

---

Never loaded in production.

---

# 36. Runtime Home Directory

User installation directory.

```
~/.pcmd/
```

---

Structure

```
~/.pcmd/

├── commands/
├── plugins/
├── helpers/
├── hooks/
├── cache/
├── logs/
├── config.php
└── state.json
```

---

# 37. Commands Directory

```
~/.pcmd/commands/
```

Contains user commands.

---

Structure

```
commands/

general/

laravel/

symfony/

wordpress/
```

---

# 38. General Commands Layout

```
commands/general/

json/

file/

git/

http/
```

---

Example

```
commands/general/json/pretty.php
```

↓

```
json:pretty
```

---

# 39. Laravel Commands Layout

```
commands/laravel/
```

Example

```
commands/laravel/products/reindex.php
```

↓

```
products:reindex
```

---

# 40. Plugins Directory

```
~/.pcmd/plugins/
```

Future plugin storage.

---

Structure

```
plugins/

docker/

github/

aws/
```

---

# 41. Helpers Directory

```
~/.pcmd/helpers/
```

Contains reusable helper libraries.

---

Example

```
helpers/

Database.php

Json.php

Images.php
```

---

Helpers are shared across commands.

---

# 42. Hooks Directory

```
~/.pcmd/hooks/
```

Contains user hooks.

---

Example

```
before.php

after.php

shutdown.php
```

---

# 43. Cache Directory

```
~/.pcmd/cache/
```

Contains generated cache files.

---

Example

```
commands.php

metadata.php

plugins.php
```

---

Users should not edit cache.

---

# 44. Logs Directory

```
~/.pcmd/logs/
```

Example

```
pcmd.log

2026-08.log

errors.log
```

---

# 45. Naming Conventions

Directories

```
PascalCase
```

for source code.

Examples

```
Discovery/

Environment/

Execution/
```

---

User command directories

```
lowercase
```

Examples

```
json/

products/

storage/
```

---

Command files

```
lowercase.php
```

Examples

```
pretty.php

repair.php

reindex.php
```

---

# 46. Ownership Rules

Each subsystem owns its directory.

Examples

```
Discovery/

owned by Discovery subsystem
```

```
Registry/

owned by Registry subsystem
```

No subsystem should write files into another subsystem's directory.

---

# 47. Forbidden Directories

Never introduce

```
Misc/

Helpers2/

Temp/

New/

Old/

Random/
```

These names communicate nothing.

---

# 48. Future Expansion

Future additions should follow existing structure.

Example

Adding Docker support.

Good

```
Framework/Docker/

DockerAdapter.php
```

Bad

```
Misc/DockerStuff/
```

---

# 49. Stability Guarantees

The following layouts are considered public:

```
~/.pcmd/

commands/

plugins/

helpers/

hooks/
```

Future versions should preserve them.

---

# 50. Final Notes

This directory structure exists to support:

- discoverability
- maintainability
- scalability
- contributor friendliness

A developer opening the repository for the first time should be able to locate any subsystem within seconds.

If a new feature cannot fit naturally into this structure, the feature design should be reconsidered before implementation.