<?php

namespace Starship\Twig;

use Parsedown;

class Extension extends \Twig_Extension
{
    private $globals;

    public function __construct(array $globals)
    {
        $this->globals = $globals;
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
