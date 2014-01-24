<?php

namespace Starship\Content;

use Symfony\Component\Finder\SplFileInfo;

class Post extends Content
{
    public $category;
    public $date;
    public $slug;

    public $next;
    public $prev;

    public function __construct(SplFileInfo $file)
    {
        parent::__construct($file);

        // Determine post date and slug based on filename
        // Pattern: yyyy-mm-dd-slug.ext
        $name = $file->getFilename();
        $pattern = implode('', [
            '/^',
            '(?P<date>\\d{4}-\\d{2}-\\d{2})',
            '-',
            '(?P<slug>.+)',
            '\\.',
            $file->getExtension(),
            '$/',
        ]);

        if (!preg_match($pattern, $name, $matches)) {
            throw new \Exception("Failed parsing file name \"$name\"");
        }

        $sourcePath = $file->getRelativePathname();

        $this->category = $this->getCategory($sourcePath);
        $this->date = new \DateTime($matches['date']);
        $this->slug = $matches['slug'];

        $parts = $this->getTargetParts(
            $this->category,
            $this->date,
            $this->slug
        );

        $this->url = '/' . implode('/', $parts);
        $parts[] = "index.html";
        $this->target = implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * Determines post category based on it's source file path.
     *
     * Posts in /_posts will have no category.
     * Posts in /news/_posts will have the category "news"
     *
     * Others combinations throw an exception.
     */
    protected function getCategory($sourcePath)
    {
        $bits = explode(DIRECTORY_SEPARATOR, $sourcePath);
        $bitCount = count($bits);
        if ($bitCount === 2) {
            if ($bits[0] !== '_posts') {
                throw new \Exception("Cannot parse post path: \"$sourcePath\"");
            }
            $category = null;
        } elseif ($bitCount === 3) {
            if ($bits[1] !== '_posts') {
                throw new \Exception("Cannot parse post path: \"$sourcePath\"");
            }
            $category = $bits[0];
        } else {
            throw new \Exception("Cannot parse post path: \"$sourcePath\"");
        }

        return $category;
    }

    /** Determine the post URL. */
    protected function getTargetParts($category, $date, $slug)
    {
        $parts = [
            $date->format('Y'),
            $date->format('m'),
            $date->format('d'),
            $slug
        ];

        if ($category) {
            array_unshift($parts, $category);
        }

        return $parts;
    }
}
