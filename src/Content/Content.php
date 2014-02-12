<?php

namespace Starship\Content;

use Netcarver\Textile\Parser as Textile;

use Parsedown;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Abastract content class, parent for Page and Post.
 */
abstract class Content
{
    const TYPE_PAGE = 'page';
    const TYPE_POST = 'post';

    public $content;
    public $id;
    public $meta;
    public $source;
    public $sourcePath;
    public $tags = [];
    public $target;
    public $template;
    public $title;
    public $type;
    public $url;

    public function __construct(SplFileInfo $file)
    {
        $this->type = strtolower(basename(get_class($this)));
        $this->id = uniqid();

        $this->source = $file;
        $this->sourcePath = $file->getRelativePathname();

        list($content, $meta) = $this->load($file);

        $this->content = $content;
        $this->meta = $meta;

        if (isset($this->meta['template'])) {
            $this->template = $this->meta['template'];
        }

        if (isset($this->meta['title'])) {
            $this->title = $this->meta['title'];
        }

        if (isset($this->meta['tags'])) {
            $this->tags = $this->meta['tags'];
            if (!is_array($this->tags)) {
                $this->tags = [$this->tags];
            }
        }
    }

    private function load(SplFileInfo $file)
    {
        // Load the file
        $fullPath = $file->getRealPath();
        $data = $file->getContents();

        list($content, $meta) = $this->splitContentMeta($data);

        // Parse meta
        $meta = Yaml::parse($meta);

        // Parse content
        switch($file->getExtension()) {
            case 'md':
            case 'markdown':
                $content = Parsedown::instance()->parse($content);
                break;

            case 'tx':
            case 'textile':
                $parser = new Textile();
                $content =  $parser->textileThis($content);
                break;
        }

        return [$content, $meta];
    }

    private function splitContentMeta($data)
    {
        // Pattern for detecting a metadata separator (---)
        // Using ^ and $ in this way requires the PCRE_MULTILINE modifier
        $pattern = '/' // Pattern start
            . '^'       // Beginning of line
            . '---'     // Literal ---
            . '\\s*'    // Zero or more whitespace characters
            . '$'       // End of line
            . '/m';     // Pattern end, PCRE_MULTILINE modifier

        // Separate the meta-data from the content
        $data = trim($data);
        if (
            (substr($data, 0, 3) === '---') &&
            (preg_match($pattern, $data, $matches, PREG_OFFSET_CAPTURE, 3))
        ) {
            $pos = $matches[0][1];
            $len = strlen($matches[0][0]);

            $meta = trim(substr($data, 3, $pos - 3));
            $content = trim(substr($data, $pos + $len));
        } else {
            $content = $data;
            $meta = null;
        }

        return [$content, $meta];
    }
}
