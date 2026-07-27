# Filesystem API

The filesystem API provides safe, consistent file operations with typed exceptions. All operations throw `Pcmd\Exceptions\FilesystemException` on failure instead of generating PHP warnings.

```php
$fs = $ctx->fs();
```

## Reading and Writing

```php
$content = $fs->read('file.txt');
$fs->write('file.txt', 'content');
```

The `write()` method automatically creates parent directories if they don't exist.

## Copying and Moving

```php
$fs->copy('from.txt', 'to.txt');
$fs->move('from.txt', 'to.txt');
```

Both methods create parent directories for the destination path.

## Deleting

```php
$fs->delete('file.txt');       // Deletes a file
$fs->delete('directory/');     // Recursively deletes a directory
```

Directory deletion is recursive. Non-existent paths are silently ignored.

## Checking Existence

```php
if ($fs->exists('file.txt')) {
    // Path exists
}
```

## Directories

```php
$fs->mkdir('path/to/directory');
```

Creates the directory and any missing parent directories (equivalent to `mkdir -p`).

## Glob

```php
$files = $fs->glob('src/**/*.php');
```

Returns a list of paths matching the glob pattern.

## Walk

```php
foreach ($fs->walk('directory') as $path) {
    echo $path . "\n";
}
```

Recursively yields every file path in a directory tree.

## Temporary Files and Directories

```php
$tmpFile = $fs->tempFile('prefix_');          // /tmp/prefix_abc123
$tmpDir  = $fs->tempDirectory('build_');       // /tmp/build_def456
```

Temporary files are created in the system temp directory with a unique name. They are not automatically cleaned up — commands should remove them when done.

## Error Handling

All methods throw `FilesystemException` on failure:

```php
use Pcmd\Exceptions\FilesystemException;

try {
    $content = $fs->read('/nonexistent/file.txt');
} catch (FilesystemException $e) {
    $ctx->error($e->getMessage());
    return 1;
}
```
