# Security Policy

## Reporting a Vulnerability

If you discover a security vulnerability, please open a GitHub issue. Do not disclose it publicly until it has been addressed.

## Scope

- Command loading and execution
- Configuration file parsing
- Process execution through the Process API
- Filesystem operations

## Best Practices

- Commands run in-process with full user permissions
- Only install commands and plugins from trusted sources
- Review third-party commands before adding them to ~/.pcmd/commands/
- Configuration files may contain editor preferences and tokens — keep them secure
