<?php

declare(strict_types=1);

namespace Pcmd\Tests\Unit\Filesystem;

use PHPUnit\Framework\TestCase;
use Pcmd\Exceptions\FilesystemException;
use Pcmd\Filesystem\Filesystem;

final class FilesystemTest extends TestCase
{
    private Filesystem $fs;
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->fs = new Filesystem();
        $this->tmpDir = sys_get_temp_dir() . '/pcmd-fs-' . bin2hex(random_bytes(4));
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tmpDir);
    }

    public function testWriteAndRead(): void
    {
        $path = $this->tmpDir . '/test.txt';
        $this->fs->write($path, 'hello');
        $this->assertSame('hello', $this->fs->read($path));
    }

    public function testReadThrowsOnMissingFile(): void
    {
        $this->expectException(FilesystemException::class);
        $this->fs->read($this->tmpDir . '/nonexistent.txt');
    }

    public function testExists(): void
    {
        $path = $this->tmpDir . '/exists.txt';
        $this->assertFalse($this->fs->exists($path));
        $this->fs->write($path, 'data');
        $this->assertTrue($this->fs->exists($path));
    }

    public function testCopy(): void
    {
        $src = $this->tmpDir . '/src.txt';
        $dst = $this->tmpDir . '/dst.txt';
        $this->fs->write($src, 'content');
        $this->fs->copy($src, $dst);
        $this->assertSame('content', $this->fs->read($dst));
    }

    public function testCopyCreatesDirectories(): void
    {
        $src = $this->tmpDir . '/src.txt';
        $dst = $this->tmpDir . '/sub/dst/dst.txt';
        $this->fs->write($src, 'content');
        $this->fs->copy($src, $dst);
        $this->assertTrue($this->fs->exists($dst));
    }

    public function testMove(): void
    {
        $src = $this->tmpDir . '/src.txt';
        $dst = $this->tmpDir . '/dst.txt';
        $this->fs->write($src, 'content');
        $this->fs->move($src, $dst);
        $this->assertFalse($this->fs->exists($src));
        $this->assertSame('content', $this->fs->read($dst));
    }

    public function testMoveCreatesDirectories(): void
    {
        $src = $this->tmpDir . '/src.txt';
        $dst = $this->tmpDir . '/sub/dst/dst.txt';
        $this->fs->write($src, 'content');
        $this->fs->move($src, $dst);
        $this->assertTrue($this->fs->exists($dst));
        $this->assertFalse($this->fs->exists($src));
    }

    public function testDeleteFile(): void
    {
        $path = $this->tmpDir . '/delete.txt';
        $this->fs->write($path, 'data');
        $this->fs->delete($path);
        $this->assertFalse($this->fs->exists($path));
    }

    public function testDeleteNonexistentIsNoOp(): void
    {
        $this->fs->delete($this->tmpDir . '/nonexistent');
        $this->assertTrue(true);
    }

    public function testDeleteDirectory(): void
    {
        $dir = $this->tmpDir . '/subdir';
        $this->fs->mkdir($dir);
        $this->fs->write($dir . '/file.txt', 'data');
        $this->fs->delete($dir);
        $this->assertFalse($this->fs->exists($dir));
    }

    public function testMkdirCreatesNestedDirectories(): void
    {
        $dir = $this->tmpDir . '/a/b/c';
        $this->fs->mkdir($dir);
        $this->assertTrue(is_dir($dir));
    }

    public function testMkdirOnExistingIsNoOp(): void
    {
        $this->fs->mkdir($this->tmpDir);
        $this->assertTrue(true);
    }

    public function testGlob(): void
    {
        $this->fs->write($this->tmpDir . '/a.php', '');
        $this->fs->write($this->tmpDir . '/b.php', '');
        $this->fs->write($this->tmpDir . '/c.txt', '');

        $result = $this->fs->glob($this->tmpDir . '/*.php');
        sort($result);
        $this->assertCount(2, $result);
    }

    public function testWalk(): void
    {
        $this->fs->write($this->tmpDir . '/a.php', '');
        $this->fs->mkdir($this->tmpDir . '/sub');
        $this->fs->write($this->tmpDir . '/sub/b.php', '');

        $walked = [];
        foreach ($this->fs->walk($this->tmpDir) as $path) {
            $walked[] = $path;
        }

        $this->assertCount(2, $walked);
    }

    public function testWalkNonexistent(): void
    {
        $walked = [];
        foreach ($this->fs->walk($this->tmpDir . '/nonexistent') as $path) {
            $walked[] = $path;
        }

        $this->assertCount(0, $walked);
    }

    public function testTempFile(): void
    {
        $path = $this->fs->tempFile('pcmd_test_');
        $this->assertFileExists($path);
        unlink($path);
    }

    public function testTempDirectory(): void
    {
        $path = $this->fs->tempDirectory('pcmd_test_');
        $this->assertTrue(is_dir($path));
        rmdir($path);
    }

    public function testWriteCreatesDirectories(): void
    {
        $path = $this->tmpDir . '/nested/dir/file.txt';
        $this->fs->write($path, 'content');
        $this->assertTrue($this->fs->exists($path));
        $this->assertSame('content', $this->fs->read($path));
    }

    public function testCopyThrowsOnMissingSource(): void
    {
        $this->expectException(FilesystemException::class);
        $this->fs->copy($this->tmpDir . '/nonexistent', $this->tmpDir . '/dest.txt');
    }

    public function testMoveThrowsOnMissingSource(): void
    {
        $this->expectException(FilesystemException::class);
        $this->fs->move($this->tmpDir . '/nonexistent', $this->tmpDir . '/dest.txt');
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($dir);
    }
}
