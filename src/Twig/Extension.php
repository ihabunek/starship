<?php

namespace Starship\Twig;

use Parsedown;

/**
 * A twig extension for Starship which provides for templates:
 * - access to Starship globals (provided in constructor)
 * - custom escapers
 * - custom filters
 */
class Extension extends \Twig_Extension
{
    private $globals;

    public function __construct(array $globals)
    {
        $this->globals = $globals;
    }

    public function initRuntime(\Twig_Environment $env)
    {
        // Add an escaper for XML
        $env->getExtension('core')->setEscaper('xml', function($env, $content) {
            return htmlentities($content, ENT_COMPAT | ENT_XML1);
        });
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('markdown', [$this, 'filterMarkdown'], ['is_safe' => ['html']])
        ];
    }

    public function getGlobals()
    {
        return $this->globals;
    }

    public function getName()
    {
        return 'starship';
    }

    public function filterMarkdown($string)
    {
        return Parsedown::instance()->parse($string);
    }
}
