<?php

namespace Starship;

class TwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [];
    }

    public function getName()
    {
        return 'starship';
    }
}
