<?php

namespace Starship\Content;

use Parsedown;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

class Page extends Content
{
    public function __construct(SplFileInfo $file)
    {
        parent::__construct($file);

        // Determine the target file
        $ext = $file->getExtension();
        $this->target = substr($this->path, 0, -strlen($ext));
        $this->target .= $this->extension;
    }
}
