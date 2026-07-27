# AGENTS.md

> **Project:** pcmd
>
> **Purpose:** A portable, environment-aware command line toolkit for developers.
>
> **Status:** Greenfield Project
>
> This document is written for AI coding agents (OpenCode, Claude Code, Cursor, GPT, etc.) and human contributors.
>
> This document is the PRIMARY source of truth regarding how development should happen.
>
> Architecture details belong in `ARCHITECTURE.md`.
> Public CLI behavior belongs in `SPECIFICATION.md`.

---

# 1. Project Vision

pcmd is a standalone executable that provides reusable developer commands.

Unlike Artisan, Symfony Console, WP CLI, etc., pcmd is **not tied to a framework**.

Instead it detects the current project and enables additional commands automatically.

Example:

```
~/Projects/shop
$ pcmd

Environment:
✓ Laravel

Available Commands

General
    json:pretty
    file:hash
    git:cleanup

Laravel
    db:truncate
    search:reindex
    queue:retry-all
```

Inside another directory

```
~/Desktop

$ pcmd

Environment:
✓ Generic

Available Commands

General
    json:pretty
    file:hash
```

The executable itself never belongs inside projects.

It is installed once.

Commands are installed once.

Every compatible project automatically gains those commands.

---

# 2. Philosophy

The project values simplicity above everything.

If two implementations provide the same functionality:

Choose the simpler one.

If two APIs are equally capable:

Choose the smaller one.

If a feature increases complexity significantly:

Do not implement it unless it provides substantial value.

---

# 3. Primary Goals

The goals are ordered by importance.

## Goal 1

Simple.

Everything should be understandable after reading it once.

No magic.

No hidden behavior.

No surprising APIs.

---

## Goal 2

Fast.

Startup time should be extremely low.

Command discovery should be cached.

Filesystem operations should be minimized.

Avoid unnecessary allocations.

Avoid unnecessary object graphs.

---

## Goal 3

Predictable.

Commands should always behave consistently.

Error messages should always follow the same format.

Every command should support the same interaction model.

---

## Goal 4

Portable.

The executable should work anywhere PHP works.

No framework dependency.

No composer project required.

---

## Goal 5

Framework aware.

Framework support is optional.

General commands always work.

Framework commands only activate when appropriate.

---

# 4. Non Goals

The following are intentionally out of scope.

Do NOT attempt to turn pcmd into:

- another Composer
- another package manager
- another shell
- another task runner
- another build system
- another Docker wrapper
- another deployment framework
- another CI tool

It is a command runner.

Nothing more.

---

# 5. Guiding Principles

## Principle 1

Everything should be discoverable.

Avoid hidden configuration.

Avoid convention without documentation.

---

## Principle 2

Everything should be replaceable.

Avoid global state.

Avoid static registries.

Avoid singleton abuse.

---

## Principle 3

Every subsystem should have one responsibility.

Example:

Environment detector

↓

Only detects environment.

NOT command discovery.

NOT bootstrapping.

NOT execution.

---

## Principle 4

Composition over inheritance.

Avoid inheritance trees.

Prefer small composable services.

---

## Principle 5

Prefer functions over classes when appropriate.

Not everything needs to be a class.

Simple utilities should remain simple.

---

# 6. Coding Standards

## PHP Version

Always target the latest stable PHP.

Never intentionally support obsolete versions.

---

## Strict Types

Every PHP file begins with

```php
declare(strict_types=1);
```

Always.

---

## Type Safety

Everything should be typed.

Avoid mixed.

Avoid implicit behavior.

Prefer explicit interfaces.

---

## Nullability

Avoid nullable values when possible.

Prefer dedicated objects.

---

## Exceptions

Throw meaningful exceptions.

Never silently ignore errors.

Never return false when an exception is appropriate.

---

## Naming

Use meaningful names.

Good

```
EnvironmentDetector
CommandRegistry
CommandLoader
CommandExecutor
```

Bad

```
Manager
Helper
Utils
Processor
ServiceManager
```

---

## Functions

Functions should be small.

Prefer under 30 lines.

If larger:

Extract.

---

## Methods

Every method should have one job.

---

## Classes

Prefer many small classes over large ones.

Avoid god objects.

---

## Comments

Comments explain WHY.

Never WHAT.

Bad

```php
// increment i

$i++;
```

Good

```php
// Skip hidden files because command discovery ignores them.
```

---

# 7. Formatting

Follow PSR-12 unless the project intentionally specifies otherwise.

Use:

- 4 spaces
- Unix line endings
- UTF-8

---

# 8. Architecture Rules

AI agents MUST NOT create circular dependencies.

Dependency direction always flows downward.

CLI

↓

Application

↓

Environment

↓

Discovery

↓

Execution

↓

Framework Adapter

↓

Command

Never the reverse.

---

# 9. Dependency Injection

Avoid service locators.

Avoid globals.

Avoid facades.

Dependencies should be injected.

---

# 10. Command System Rules

Commands are plugins.

Commands are not compiled into pcmd.

pcmd discovers them.

Loads them.

Executes them.

Commands remain independent.

---

# 11. Framework Isolation

General commands must never depend on Laravel.

General commands must never import Illuminate.

Framework commands may depend on their framework.

---

# 12. Public APIs

Public APIs should remain stable.

Breaking changes require:

- documentation update
- migration notes
- version bump

---

# 13. Internal APIs

Internal APIs may evolve.

Keep them clean.

Avoid exposing internals unnecessarily.

---

# 14. Performance Rules

Avoid unnecessary filesystem scans.

Discovery should be cached.

Reflection should be minimized.

Avoid expensive recursive scans every execution.

---

# 15. Memory Rules

Avoid keeping entire registries in memory unnecessarily.

Load lazily whenever possible.

---

# 16. Error Messages

Errors should explain

- what failed
- why
- how to fix it

Bad

```
Error.
```

Good

```
Laravel project detected.

bootstrap/app.php was not found.

Expected:

project/
    bootstrap/
        app.php
```

---

# 17. Console Output

Output should be clean.

No excessive emojis.

No unnecessary colors.

Prefer concise information.

---

# 18. Testing Philosophy

Everything important should be tested.

Tests should be deterministic.

Avoid network.

Avoid external dependencies.

Avoid timing assumptions.

---

# 19. AI Development Workflow

Every implementation follows this order.

## Step 1

Read every documentation file.

Never skip documentation.

---

## Step 2

Understand architecture.

Never invent architecture.

---

## Step 3

Design.

Think before coding.

---

## Step 4

Implement.

Small commits.

Small features.

---

## Step 5

Test.

---

## Step 6

Refactor.

---

## Step 7

Document.

Documentation is part of implementation.

---

# 20. What AI MUST NOT Do

Never:

Invent undocumented features.

Guess APIs.

Guess command behavior.

Change architecture silently.

Introduce breaking changes.

Mix framework-specific logic into generic code.

Skip tests.

Leave TODOs for completed work.

Duplicate logic.

Create utility classes without clear purpose.

Over-engineer.

---

# 21. Preferred Development Style

Prefer

```
Small feature

↓

Tests

↓

Review

↓

Next feature
```

Instead of

```
Huge implementation

↓

Hope it works
```

---

# 22. Preferred Project Structure

Every directory should have a purpose.

Avoid dumping unrelated files together.

Prefer feature-oriented organization.

---

# 23. Documentation Rules

Whenever implementation changes behavior:

Update documentation immediately.

Documentation must never lag behind implementation.

---

# 24. Versioning

Semantic Versioning.

Major

Breaking changes.

Minor

New features.

Patch

Bug fixes.

---

# 25. Future Expansion

The architecture should make it easy to add:

- Symfony
- WordPress
- Drupal
- Magento
- Laravel Zero
- Generic PHP projects

without changing existing code.

Framework integrations should be adapters, not special cases.

---

# 26. Code Quality Checklist

Before considering a feature complete, verify:

- Code follows project architecture.
- No duplicated logic.
- No circular dependencies.
- Tests pass.
- Documentation updated.
- Naming is clear.
- Error messages are useful.
- Performance impact considered.
- Public API remains stable.
- No dead code.
- No commented-out code.
- No debugging statements.

---

# 27. Project Mindset

This project is intended to become a developer tool that is installed once and used for years.

Every design decision should optimize for:

- long-term maintainability
- simplicity
- portability
- extensibility
- predictable behavior

Never optimize for short-term convenience at the expense of architectural quality.

---

# 28. Commit Policy

Never commit changes unless the user explicitly asks you to commit.

Even if the user asks once, treat that as a one-time instruction.

Do not assume future commits are desired.

Only commit when the user says "commit" or "commit the changes" in the current session.

If the working tree is clean when asked, report that nothing has changed.

---

# 29. Final Rule

If this document conflicts with implementation:

Assume the implementation is wrong.

Fix the implementation.

Documentation is the source of truth until intentionally revised.
