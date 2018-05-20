<?php

namespace Compolomus\FileManager;

use InvalidArgumentException;
use SplFileInfo;

class Meta
{

    private $meta = [];

    public function __construct(SplFileInfo $file)
    {
        if (!file_exists($file)) {
            throw new InvalidArgumentException('File not found');
        }
        $this->setMeta($file);
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    private function setMeta(SplFileInfo $meta): void
    {
        $result = [];
        $result['name'] = $meta->getBasename();
        $result['ext'] = $meta->getExtension();
        $result['path'] = $meta->getPath();
        $result['permissions'] = substr(sprintf('%o', $meta->getPerms()), -4);
        $result['size'] = $meta->getSize();
        $result['type'] = $meta->getType();
        $result['execute'] = (int)$meta->isExecutable();
        $result['read'] = (int)$meta->isReadable();
        $result['write'] = (int)$meta->isWritable();
        $this->meta = $result;
    }

}
