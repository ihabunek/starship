<?php

namespace Starship;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Parser;

use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_Loader_Chain;

use Starship\Content\Page;
use Starship\Content\Post;
use Starship\Content\Content;


/**
 * Builds a Site object from content on disk.
 */
class Builder
{
    /** Path to the source folder. */
    private $source;

    /** Path to the target folder. */
    private $target;

    /**
     * Output object for writing to console.
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Site objects holds the compiled site data.
     * @var Site
     */
    private $site;

    public function __construct($source, $target, OutputInterface $output)
    {
        $this->output = $output;

        $fs = new Filesystem();
        if (!$fs->exists($source)) {
            throw new \Exception("Source folder not found at \"$source\".");
        }
        if (!$fs->exists($target)) {
            throw new \Exception("Target folder not found at \"$target\".");
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
        }

        $includesDir = $this->source. DIRECTORY_SEPARATOR . "_includes";
        if (is_dir($includesDir)) {
            $loader->addLoader(new Twig_Loader_Filesystem($includesDir));
        }

        $this->twig = new Twig_Environment($loader, ['debug' => true]);
        $this->twig->addExtension(new Twig_Extension_Debug());
        $this->twig->addExtension(new Twig\Extension([
            'site' => $this->site
        ]));
    }

    /** Renders the site. */
    public function build()
    {
        $this->addPages();
        $this->addPosts();
        $this->sortPosts();

        $this->writeln("\n<comment>Rendering pages</comment>");
        foreach($this->site->pages as $page) {
            $this->renderContent($page);
        }

        $this->writeln("\n<comment>Rendering posts</comment>");
        foreach($this->site->posts as $post) {
            $this->renderContent($post);
        }

        $this->writeln("\n<comment>Copying statics</comment>");
        $this->copyStatics();
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
    private function renderContent(Content $content)
    {
        $tpl = $content->template ? " <comment>($content->template)</comment>" : "";
        $this->writeln("Rendering: <info>{$content->target}</info>{$tpl}");

        // Only templated files are run through Twig (template can be "none")
        if (isset($content->template)) {
            $html = $this->twig->render($content->id, ['page' => $content]);
        } else {
            $html = $content->content;
        }

        $target = $this->target . DIRECTORY_SEPARATOR . $content->target;

        $fs = new Filesystem();
        $fs->dumpFile($target, $html);
    }

    private function addPosts()
    {
        $this->writeln("\n<comment>Adding posts</comment>");

        $finder = new Finder();
        $finder->files()
            ->in($this->source)
            ->path('_posts')
            ->name('/\\d{4}-\\d{2}-\\d{2}-.+\\.(md|textile|html)/');

        foreach ($finder as $file) {
            $post = new Post($file);
            $this->writeln("Adding: <info>$post->sourcePath</info>");
            $this->site->addPost($post);
        }
    }

    private function addPages()
    {
        $this->writeln("\n<comment>Adding pages</comment>");

        $finder = new Finder();
        $finder->files()
            ->in($this->source)
            ->notPath('_')
            ->name('/\\.(md|textile|html|xml)$/');

        foreach ($finder as $file) {
            $page = new Page($file);
            $this->writeln("Adding: <info>$page->sourcePath</info>");
            $this->site->addPage($page);
        }
    }

    private function copyStatics()
    {
        $extensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
        $pattern = '/\\.(' . implode("|", $extensions) . ')$/';

        $finder = new Finder();
        $finder->files()
            ->in($this->source)
            ->notPath('_site')
            ->notPath('_template')
            ->notPath('_includes')
            ->notPath('_posts')
            ->name($pattern);

        $fs = new Filesystem();

        foreach ($finder as $file) {
            $path = $file->getRelativePathname();

            $source = $file->getRealPath();
            $target = $this->target . DIRECTORY_SEPARATOR . $path;

            $fs->copy($source, $target);

            $this->writeln("Copied: <info>$path</info>");
        }
    }

    /** Sorts posts by date (descending). Assigns post.next and posts.prev. */
    private function sortPosts()
    {
        $this->writeln("\n<comment>Sorting</comment>");

        $cmpFn = function(Post $one, Post $other) {
            if ($one->date == $other->date) {
                return 0;
            }
            return ($one->date > $other->date) ? -1 : 1;
        };

        usort($this->site->posts, $cmpFn);

        foreach($this->site->categories as $cat => &$posts) {
            usort($posts, $cmpFn);

            // Assign next and previous post within the category
            foreach($posts as $key => $post) {
                if (isset($posts[$key - 1])) {
                    $post->next = $posts[$key - 1];
                }
                if (isset($posts[$key + 1])) {
                    $post->prev = $posts[$key + 1];
                }
            }
        }
    }

    /** Writes to o*/
    private function writeln($msg)
    {
        if ($this->output->isVerbose()) {
            $this->output->writeln($msg);
        }
    }
}
