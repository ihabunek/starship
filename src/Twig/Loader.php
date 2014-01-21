<?php

namespace Starship\Twig;

use Starship\Content\Page;
use Starship\Site;

class Loader implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
    private $site;

    public function  __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Generates the template for a given page path.
     */
    function getSource($name)
    {
        $page = $this->site->pages[$name];
        if ($page->template) {
            $template =  "{% extends \"$page->template\" %}";
            $template .= "{% block content %}";
            $template .= $page->content;
            $template .= "{% endblock %}";
        } else {
            $template = $page->content;
        }

        return $template;
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     */
    function getCacheKey($name)
    {
        return $name;
    }

    /**
     * Returns true if the template is still fresh.
     */
    function isFresh($name, $time)
    {
        return true;
    }

    function exists($name)
    {
        return isset($this->site->pages[$name]);
    }
}
