<?php

namespace Starship;

class Loader extends \Twig_LoaderInterface
{
    /**
     * Gets the source code of a template, given its name.
     */
    public function getSource($name)
    {

    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     */
    public function getCacheKey($name)
    {
        return $name;
    }

    /**
     * Returns true if the template is still fresh.
     */
    public function isFresh($name, $time)
    {
        return true;
    }
}