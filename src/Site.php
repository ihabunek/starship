<?php

namespace Starship;

use DateTime;

use Starship\Content\Content;
use Starship\Content\Page;
use Starship\Content\Post;

class Site
{
    /**
     * Time of site generation.
     * @var DateTime
     */
    public $time;

    public $name;
    public $url;
    public $config;

    public $pages = [];
    public $posts = [];
    public $categories = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->time = new DateTime();

        if (isset($config->name)) {
            $this->name = $config->name;
        }

        if (isset($config->url)) {
            $this->url = $config->url;
        }
    }

    public function addContent(Content $content)
    {
        if ($content instanceof Page) {
            $this->addPage($content);
        } else if ($content instanceof Post) {
            $this->addPost($content);
        } else {
            throw new \Exception("Unknown content type.");
        }
    }

    public function addPage(Page $page)
    {
        // Since pages are indexed by path, collision should never happen
        assert(!isset($this->pages[$page->path]));

        $this->pages[$page->path] = $page;
    }

    public function addPost(Post $post)
    {
        $this->posts[] = $post;

        $cat = $post->category;
        if (!isset($this->categories[$cat])) {
            $this->categories[$cat] = [];
        }

        $this->categories[$cat][] = $post;
    }
}
