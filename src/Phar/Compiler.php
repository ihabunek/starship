<?php

namespace Starship\Phar;

use DateTime;
use Phar;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

class Compiler
{
    private $version;

    public function __construct()
    {
        $this->version = trim(`git describe`);
        if (empty($this->version)) {
            throw new \Exception("Unable to detect version.");
        }

        $date = trim(`git log -n1 --pretty=%ci HEAD`);
        if (empty($date)) {
            throw new \Exception("Unable to detect release date.");
        }
        $dt = new DateTime($date);
        $this->releaseDate = $dt->format("Y-m-d");
    }

    public function compile($target = "starship.phar")
    {
        $this->output = new ConsoleOutput();

        $this->output->writeln("<comment>Compiling Starship PHAR</comment>");
        $this->output->writeln("Version: <info>{$this->version}</info>");
        $this->output->writeln("Release date: <info>{$this->releaseDate}</info>");
        $this->output->writeln("");

        if (file_exists($target)) {
            unlink($target);
        }

        $phar = new Phar($target);
        $phar->startBuffering();
        $this->addFiles($phar);
        $phar->setStub("<?php
            Phar::mapPhar('starship.phar');
            require 'phar://starship.phar/bin/starship';
            __HALT_COMPILER();
        ?>");

        $phar->stopBuffering();

        $path = realpath($target);
        $this->output->writeln("");
        $this->output->writeln("Compiled PHAR at <info>$path</info>");
    }

    private function addFiles($phar)
    {
        $base = realpath(__DIR__ . "/../../");

        // Only PHP files in src and vendors
        $finder1 = (new Finder())
            ->files()
            ->name('*.php')
            ->in($base)
            ->path('/^src/')
            ->path('/^vendor/')

            // Skip tests
            ->notPath('vendor/symfony/console/Symfony/Component/Console/Tests')
            ->notPath('vendor/symfony/filesystem/Symfony/Component/Filesystem/Tests')
            ->notPath('vendor/symfony/finder/Symfony/Component/Finder/Tests')
            ->notPath('vendor/symfony/yaml/Symfony/Component/Yaml/Tests')
            ->notPath('vendor/twig/twig/test')
        ;

        // All files in scaffolding
        $finder2 = (new Finder())
            ->files()
            ->in($base)
            ->path('/^scaffolding/');

        // Init progress indicator
        $count = count($finder1) + count($finder2);
        $progress = new ProgressHelper();
        $progress->start($this->output, $count);

        // Path to Application.php
        $appPath = implode(DIRECTORY_SEPARATOR, ['src','Console','Application.php']);

        foreach ([$finder1, $finder2] as $finder) {
            foreach ($finder as $file) {
                $path = $file->getRelativePathname();
                $realPath = $file->getRealPath();

                if ($file->getExtension() === 'php') {
                    $contents = php_strip_whitespace($realPath);
                } else {
                    $contents = $file->getContents();
                }

                // Add version and release date to Application.php
                if ($path === $appPath) {
                    $contents = str_replace('@starship_version@', $this->version, $contents);
                    $contents = str_replace('@starship_release_date@', $this->releaseDate, $contents);
                }

                $phar->addFromString($path, $contents);
                $progress->advance();
            }
        }

        // Add the executable
        $path = "bin/starship";
        $contents = file_get_contents("$base/$path");

        // Remove shebang which interferes
        $contents = preg_replace('/^#!\/usr\/bin\/env php\s*/', '', $contents);
        $phar->addFromString($path, $contents);

        $progress->finish();
    }
}
