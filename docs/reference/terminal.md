# Terminal API

The terminal API provides styled output, interactive input, progress indicators, and table rendering. All terminal operations are accessed through the `Context` object.

## Styled Output

```php
$ctx->info('Processing...');     // Green text
$ctx->success('Completed.');     // Bright green text
$ctx->warn('Already exists.');   // Yellow text
$ctx->error('Failed.');          // Red text, written to stderr

$ctx->line('Plain text');        // Plain text with newline
$ctx->newline();                 // Blank line
```

ANSI color output is automatically detected and can be disabled with `--no-ansi`.

## Interactive Input

All interactive methods automatically respect `--no-interaction` and return defaults when non-interactive.

### ask()
```php
$name = $ctx->ask('What is your name?');
$name = $ctx->ask('Name', 'default_value');
```
Prompts the user for text input. Returns the entered value or the default if empty.

### confirm()
```php
if ($ctx->confirm('Continue?')) {
    // User said yes
}

if ($ctx->confirm('Delete database?', false)) {
    // Default is no
}
```
Prompts for yes/no confirmation. Returns `true` for `y`/`yes`/`true`, `false` otherwise.

### secret()
```php
$password = $ctx->secret('Enter password');
```
Prompts for hidden input. Uses `stty -echo` on Unix to hide typed characters.

### choice()
```php
$db = $ctx->choice('Select database', ['mysql', 'pgsql']);
```
Presents a numbered list of options. Returns the selected value.

### multichoice()
```php
$databases = $ctx->multichoice('Select databases', ['mysql', 'pgsql', 'sqlite']);
// Returns ['mysql', 'pgsql'] (list of selected values)
```
Presents a numbered list of options. Accepts comma-separated numbers or values. Returns an array of selected values. Returns an empty array in non-interactive mode.

## Progress Bar

```php
$total = 500;
$bar = $ctx->progress($total);
$bar->start();

for ($i = 0; $i < $total; $i++) {
    // process item
    $bar->advance();
}

$bar->finish();
```

Renders as:

```
100% [==================================================>] 500/500
```

Advanced usage:

```php
$bar->advance(5);   // Advance by multiple steps
```

## Spinner

For indeterminate operations:

```php
$spinner = $ctx->spinner();
$spinner->start();

// ... long operation ...

$spinner->finish();
```

Renders an animated braille spinner: `⠋`, `⠙`, `⠹`, `⠸`, `⠼`, `⠴`, `⠦`, `⠧`, `⠇`, `⠏`

## Tables

```php
$ctx->table(
    ['Name', 'Size', 'Type'],
    [
        ['document.pdf', '2.4 MB', 'PDF'],
        ['image.png', '340 KB', 'PNG'],
        ['archive.zip', '1.1 GB', 'ZIP'],
    ],
);
```

Renders:

```
| Name          | Size   | Type |
|---------------|--------|------|
| document.pdf  | 2.4 MB | PDF  |
| image.png     | 340 KB | PNG  |
| archive.zip   | 1.1 GB | ZIP  |
```

Column widths are calculated automatically based on content.

## Terminal Detection

```php
$ctx->terminal()->isAnsi();         // bool - ANSI colors supported
$ctx->terminal()->isInteractive();  // bool - interactive mode
$ctx->terminal()->isVerbose();      // bool - --verbose is set
$ctx->terminal()->isDebug();        // bool - --debug is set
$ctx->terminal()->width();          // int - terminal columns
```
