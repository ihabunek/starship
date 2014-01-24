<?php

namespace Starship\Twig;

use Starship\Content\Page;
use Starship\Site;

/**
 * Custom Twig loader which generates page templates.
 */
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
        if (isset($this->site->pages[$name])) {
            $content = $this->site->pages[$name];
        } elseif (isset($this->site->postsMap[$name])) {
            $content = $this->site->postsMap[$name];
        } else {
            throw new \Exception("Cannot find content \"$name\".");
        }

        if (empty($content->template)) {
            throw new \Exception("Content does not have a template");
        }

        if ($content->template !== 'none') {
            $template =  "{% extends \"$content->template\" %}";
            $template .= "{% block content %}";
            $template .= $content->content;
            $template .= "{% endblock %}";
        } else {
            $template = $content->content;
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
        return isset($this->site->pages[$name]) || isset($this->site->postsMap[$name]);
    }
}
