<?php

namespace Starship;

use Parsedown;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml;

abstract class Content
{
    public $content;
    public $extension;
    public $meta;
    public $path;
    public $tags = [];
    public $target;
    public $template;

    private $filters = [
        'md' => 'markdown'
    ];

    public function __construct(SplFileInfo $file)
    {
        list($content, $meta) = $this->load($file);

        $this->content = $content;
        $this->meta = $meta;

        // Determine file extension based on the template
        if (isset($meta['template'])) {
            $this->template = $meta['template'];
            $this->extension = pathinfo($this->template, PATHINFO_EXTENSION);
        }

        // If no template, default to html
        if (empty($this->extension)) {
            $this->extension = 'html';
        }

        // Read tags
        if (isset($meta['tags'])) {
            $this->tags = $meta['tags'];
            if (!is_array($this->tags)) {
                $this->tags = [$this->tags];
            }
        }
    }

    public function load(SplFileInfo $file)
    {
        // Load the file
        $fullPath = $file->getRealPath();
        $data = file_get_contents($fullPath);
        if ($data === false) {
            throw new \Exception("Failed loading data.");
        }

        // Separate the meta-data from the content
        $pattern = '/\\n---\\s*\\r?\\n/';
        if (preg_match($pattern, $data, $matches, PREG_OFFSET_CAPTURE)) {
            $pos = $matches[0][1];
            $len = strlen($matches[0][0]);

            $meta = substr($data, 0, $pos);
            $content = substr($data, $pos + $len);
        } else {
            $content = $data;
            $meta = null;
        }

        $yaml = new Yaml\Parser();
        $meta = $yaml->parse($meta);

        $content = Parsedown::instance()
            ->parse($content);

        return array($content, $meta);
    }
}
