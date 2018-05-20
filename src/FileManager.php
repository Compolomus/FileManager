<?php

namespace Compolomus\FileManager;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FilesystemIterator;
use SplFileInfo;
use DirectoryIterator;
use InvalidArgumentException;

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
        $this->totalSpace = disk_total_space($this->chroot);
        $this->freeSpace = disk_free_space($this->chroot);
        $this->listAll = $this->list($this->chroot);
    }

    private function checkItem(string $item): void
    {
        if (!\in_array($item, $this->listAll['files'], true) && !\in_array($item, $this->listAll['dirs'], true)) {
            throw new InvalidArgumentException('Item not found');
        }
    }

    private function checkDest(string $dest): void
    {
        if (!$this->checkRoot($dest)) {
            throw new InvalidArgumentException('Access denied');
        }
    }

    private function checkRoot(string $root, bool $strict = false): bool
    {
        preg_match('/' . preg_quote($this->chroot, DIRECTORY_SEPARATOR) . '/', realpath($root), $matches);

        return ($strict ? file_exists($root) : true) && \count($matches) > 1;
    }

    private function getIterator(?string $dir): RecursiveIteratorIterator
    {
        if (!$this->checkRoot($dir, true)) {
            $dir = $this->chroot;
        }

        return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir,
            FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    }

    private function list(?string $dir = null, bool $objects = false, bool $filesize = false)
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

    public function ls(string $dir)
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
        return rename($oldName, $newName);
    }

    public function move(string $oldName, string $newName): bool
    {
        return $this->rename($oldName, $newName);
    }

    public function copy(string $source, string $dest): bool
    {
        $this->checkItem($source);
        $this->checkDest($dest);
        return copy($source, $dest);
    }

    public function chmod(SplFileInfo $item, int $perms): bool
    {
        $this->checkItem($item);
        return chmod($item->getRealPath(), 0 . $perms);
    }

    public function mkdir(string $dir): bool
    {
        $this->checkDest($dir);
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new InvalidArgumentException('Directory is exists or not writable');
        }
        return true;
    }

    public function search(): ?array
    {

    }

}
