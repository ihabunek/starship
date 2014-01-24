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
    public $postsMap = [];
    public $categories = [];

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->time = new DateTime();

        if (isset($config['name'])) {
            $this->name = $config['name'];
        }

        if (isset($config['url'])) {
            $this->url = rtrim($config['url'], '/');
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
        $this->pages[$page->id] = $page;
    }

    public function addPost(Post $post)
    {
        $this->posts[] = $post;
        $this->postsMap[$post->id] = $post;

        // Group by category
        $cat = $post->category;
        if (!isset($this->categories[$cat])) {
            $this->categories[$cat] = [];
        }
        $this->categories[$cat][] = $post;
    }
}
