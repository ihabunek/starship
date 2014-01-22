<?php

namespace Starship\Content;

use Symfony\Component\Finder\SplFileInfo;

class Post extends Content
{
    public $category;

    public function __construct(SplFileInfo $file)
    {
        parent::__construct($file);

        // Determine the target file
        $ext = $file->getExtension();
        $target = substr($this->path, 0, -strlen($ext));
        $target .= $this->extension;

        // Remove _posts from path
        $target = str_replace("_posts" . DIRECTORY_SEPARATOR, "", $target);

        // Determine category based on path (name of the folder under _posts)
        $bits = explode(DIRECTORY_SEPARATOR, $target);
        $bitCount = count($bits);
        if ($bitCount === 1) {
            $category = null;
        } else {
            $pos = $bitCount - 2;
            $category = $bits[$pos];
        }

        $this->target = $target;
        $this->category = $category;
    }
}
