<?php

namespace Starship;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use Twig_Environment;
use Twig_Loader_Filesystem;

class Generator
{
    private $source;
    private $target;
    private $config;
    private $site;
    private $templateDir;

    public function __construct($source, $target)
    {
        $this->target = realpath($target);
        if ($this->target === false) {
            throw new \Exception("Invalid target dir");
        }

        $this->source = realpath($source);
        if ($this->source === false) {
            throw new \Exception("Invalid source dir");
        }

        $this->templateDir = $this->source. DIRECTORY_SEPARATOR . "_template";
        if (!is_dir($this->templateDir)) {
            throw new \Exception("Template dir not found at: $templateDir");
        }

        $loader = new Twig_Loader_Filesystem($this->templateDir);
        $this->twig = new Twig_Environment($loader);

        $config = $this->loadConfig($this->source);
        $this->site = new Site($config);
    }

    /** Loads and parses the config file. */
    private function loadConfig($source)
    {
        $path = $source . DIRECTORY_SEPARATOR . "_config.yml";
        if (!file_exists($path)) {
            throw new \Exception("Configuration not found at: $path");
        }

        $data = file_get_contents($path);
        if ($data === false) {
            throw new \Exception("Unable to load configuration from: $path");
        }

        $yaml = new Parser();
        $config = $yaml->parse($data);

        return $config ? $config : [];
    }

    /** Renders the site. */
    public function generate()
    {
        $this->findPages($this->source, $this->target);

        foreach($this->site->pages as $page) {
            $this->render($page);
        }
    }

    /** Renders a page. */
    private function render(Page $page)
    {
        if (isset($page->template)) {

            $templateID = uniqid();

            $path = $this->templateDir . DIRECTORY_SEPARATOR . $page->template;
            if (!file_exists($path)) {
                throw new \Exception("Template [$page->template] not found at: $path");
            }

            $data = [
                'content' => $page->content,
                'page' => $page,
                'site' => $this->site
            ];

            $template =  "{% extends $page->template %}";
            $template .= "{% block content %}";
            $template .= $page->content;
            $template .= "{% endblock %}";

            $html = $this->twig->render($template, $data);
        } else {
            $html = $page->content;
        }

        $target = $this->target . DIRECTORY_SEPARATOR . $page->target;
        $this->createDirectory(dirname($target));

        if (file_put_contents($target, $html) === false) {
            throw new \Exception("Failed saving renderd page.");
        }

        echo "Rendered: $target\n";
    }

    private function createDirectory($dir)
    {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new \RuntimeException("Failed creating target directory: $dir");
            }
        }
    }

    private function findPosts($source, $target)
    {
        $finder = new Finder();
        $finder->files()
            ->in($source)
            ->path('/_posts')
            ->name('*.md');

        foreach ($finder as $file) {
            $this->site->addPost(new Post($file));
        }
    }

    private function findPages($source, $target)
    {
        $finder = new Finder();
        $finder->files()
            ->in($source)
            ->notPath('/_')
            ->name('*.md');

        foreach ($finder as $file) {
            $this->site->addPage(new Page($file));
        }
    }
}
