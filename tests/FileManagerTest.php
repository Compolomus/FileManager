<?php

namespace Compolomus\FileManager\Tests;

use Compolomus\FileManager\FileManager;
use Compolomus\FileManager\Meta;
use SplFileInfo;
use PHPUnit\Framework\TestCase;
use Exception;
use Compolomus\FileManager\FileManagerException;

class FileManagerTest extends TestCase
{

    private $manager;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        @mkdir(__DIR__ . '/test', 0777);
        file_put_contents(__DIR__ . '/test/text.txt', __FILE__);
        $this->manager = new FileManager(['chroot' => __DIR__ . '/test']);
    }

    public function __destruct()
    {
        @unlink(__DIR__ . '/test/text.txt');
        @rmdir(__DIR__ . '/test/mkdir');
        @rmdir(__DIR__ . '/test');
    }

    public function test__construct(): void
    {
        try {
            $manager = new FileManager(['chroot' => __DIR__ . '/test']);
            $this->assertInternalType('object', $manager);
            $this->assertInstanceOf(FileManager::class, $manager);
        } catch (Exception $e) {
            $this->assertContains('Must be initialized ', $e->getMessage());
        }
    }

    public function test_ls(): void
    {
        $array = $this->manager->ls('.');
        $this->assertCount(3, $array);
        $this->assertCount(9, current($array['files'])->getMeta());
    }

    public function test_mkdir(): void
    {
        $this->manager->mkdir(__DIR__ . '/test/mkdir');
        $this->assertDirectoryExists(__DIR__ . '/test/mkdir');
    }

    public function test_mkdirException(): void
    {
        $this->expectException(FileManagerException::class);
        $this->manager->mkdir('/usr/mkdir');
    }

//    public function test_chmod(): void
//    {
//        echo '<pre>' . print_r($this->manager, true) . '</pre>';die;
//        $this->manager->chmod(__DIR__ . '/test/mkdir', 775);
//        $perms = substr(sprintf('%o', fileperms(__DIR__ . '/test/mkdir')), -4);
//        $meta = new Meta(new SplFileInfo(__DIR__ . '/test/mkdir'));
//        $this->assertTrue($meta->getMeta()['permissions'], $perms);
//    }
}
