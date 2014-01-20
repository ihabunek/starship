<?php

namespace Starship;

use DateTime;

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

    public function addPage(Page $page)
    {
        $this->pages[] = $page;
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
