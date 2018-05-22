<?php

namespace Compolomus\FileManager;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use SplFileInfo;
use DirectoryIterator;
use Compolomus\FileManager\FileManagerException;

class FileManager
{
    private $config;

    private $chroot;

    private $totalSpace;

    private $freeSpace;

    private $listAll;

    public function __construct(array $config)
    {
        // TODO: def config
        $this->config = $config;
        $this->init();
    }

    private function init(): void
    {
        $this->chroot = realpath($this->config['chroot']);
        if (!is_readable($this->chroot) && !is_writable($this->chroot)) {
            throw new FileManagerException('Directory must be available');
        }
        $this->itemList();
    }

    private function itemList(): void
    {
        $this->listAll = $this->list($this->chroot);
        $this->totalSpace = disk_total_space($this->chroot);
        $this->freeSpace = disk_free_space($this->chroot);
    }

    private function checkItem(string $item): void
    {
//        echo '+++', \strlen($item), '+++';
//        echo '<pre>' . print_r(($this->listAll['dirs']), true) . '</pre>';die;
        if (!\array_key_exists($item, $this->listAll['files']) && !\array_key_exists($item, $this->listAll['dirs'])) {
            throw new FileManagerException('Item not found');
        }
    }

    private function checkDest(string $dest): void
    {
        if (!$this->checkRoot($dest)) {
            throw new FileManagerException('Access denied');
        }
    }

    private function checkRoot(string $root, bool $strict = false): bool
    {
        preg_match('#(' . $this->chroot . ')#', $root, $matches);

        return  \count($matches) > 1 && ($strict ? file_exists($root) : true);
    }

    private function getIterator(?string $dir): RecursiveIteratorIterator
    {
        if (!$this->checkRoot(realpath($dir), true)) {
            $dir = $this->chroot;
        }

        return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,
            FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    }

    private function list(?string $dir = null, bool $objects = false, bool $filesize = false): array
    {
        $dirs = [];
        $files = [];
        $result = [];

        foreach ($iterator = $this->getIterator($dir) as $item) {
            ${$item->getType() . 's'}[$item->getRealPath()] = $objects ? new Meta($item) : $item->getBasename();
        }

        $result['files'] = $files;
        $result['dirs'] = $dirs;
        !$filesize ?: $result['filesize'] = $this->getSizes($iterator);

        return $result;
    }

    public function ls(string $dir): array
    {
        return $this->list($dir, true, true);
    }

    private function getSizes(RecursiveIteratorIterator $iterator)
    {
        $generator = function (RecursiveIteratorIterator $iterator) {
            $total = 0;
            foreach ($iterator as $value) {
                $total += $value->getSize();
            }
            yield $total;
        };

        return current(iterator_to_array($generator($iterator)));
    }

    public function delete(SplFileInfo $item): bool
    {
        $this->{'delete' . ucfirst($item->getType())}($item->getRealPath());
        $this->itemList();
    }

    public function deleteFile(string $item): bool
    {
        $this->checkItem($item);
        return unlink($item);
    }

    public function deleteDir(string $dir): bool
    {
        $this->checkItem($dir);
        foreach (new DirectoryIterator($dir) as $item) {
            if (!$item->isDot()) {
                $this->delete($item);
            }
        }
        return rmdir($dir);
    }

    public function rename(string $oldName, string $newName): bool
    {
        $this->checkItem($oldName);
        $this->checkDest($newName);
        $return = rename($oldName, $newName);
        $this->itemList();

        return $return;
    }

    public function move(string $oldName, string $newName): bool
    {
        return $this->rename($oldName, $newName);
    }

    public function copy(string $source, string $dest): bool
    {
        $this->checkItem($source);
        $this->checkDest($dest);
        $return = copy($source, $dest);
        $this->itemList();

        return $return;
    }

    public function chmod(string $item, int $perms): bool
    {
        $this->checkItem($item);
        return chmod($item, 0 . $perms);
    }

    public function mkdir(string $dir): bool
    {
        if (!@mkdir($dir, 0777, true) && !@is_dir($dir)) {
            throw new FileManagerException('Directory is exists or not writable');
        }
        $this->checkDest($dir);
        $this->itemList();
        return true;
    }

    public function search(): ?array
    {

    }

}
