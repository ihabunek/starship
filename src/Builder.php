<?php

namespace Starship;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Loader_Chain;

use Starship\Content\Page;
use Starship\Content\Post;

class Builder
{
    private $source;
    private $target;
    private $output;

    private $site;
    private $templateDir;

    public function __construct($source, $target, OutputInterface $output)
    {
        $this->output = $output;

        $fs = new Filesystem();
        if (!$fs->exists($source)) {
            throw new \Exception("Source folder not found at $source.");
        }
        if (!$fs->exists($target)) {
            $fs->mkdir($target);
        }

        $this->target = $target;
        $this->source = $source;

        // Load configuration, setup Site
        $config = $this->loadConfig($this->source);
        $this->site = new Site($config);

        // Setup a twig loader
        $loader = new Twig_Loader_Chain();
        $loader->addLoader(new Twig\Loader($this->site));

        // If a template directory exists, add a filesystem loader to resolve
        // templates residing within it
        $templateDir = $this->source. DIRECTORY_SEPARATOR . "_template";
        if (is_dir($templateDir)) {
            $loader->addLoader(new Twig_Loader_Filesystem($templateDir));
            $this->templateDir = $templateDir;
        }

        $this->twig = new Twig_Environment($loader);
        $this->twig->addExtension(new Twig\Extension([
            'site' => $this->site
        ]));
    }

    /** Renders the site. */
    public function build()
    {
        $this->addPages();
        $this->addPosts();

        $this->output->writeln("");
        foreach($this->site->pages as $page) {
            $this->render($page);
        }
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

    /** Renders a page. */
    private function render(Page $page)
    {
        $this->output->writeln("Rendering: $page->target");

        if (isset($page->template)) {
            $html = $this->twig->render($page->path, ['page' => $page]);
        } else {
            $html = $page->content;
        }

        $target = $this->target . DIRECTORY_SEPARATOR . $page->target;

        $fs = new Filesystem();
        $fs->dumpFile($target, $html);
    }

    private function addPosts()
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->source)
            ->path('_posts')
            ->name('*.md')
            ->name('*.html');

        foreach ($finder as $file) {
            $post = new Post($file);
            $this->output->writeln("Adding: $post->path");
            $this->site->addPost($post);
        }
    }

    private function addPages()
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->source)
            ->notPath('_')
            ->name('*.md')
            ->name('*.html');

        foreach ($finder as $file) {
            $page = new Page($file);
            $this->output->writeln("Adding: $page->path");
            $this->site->addPage($page);
        }
    }
}
