<?php

namespace Starship\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InitCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Creates a scaffold for a web site.')
            ->addArgument(
                'target', InputArgument::REQUIRED,
                'Path to the target directory.'
            )
            ->addOption(
               'force', 'f', InputOption::VALUE_NONE,
               'Force creation even if target directory already exists.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $target = $input->getArgument('target');
        $force = $input->getOption('force');

        $target = $this->parseTarget($target, $force);

        // Copy scaffolding
        $options = $force ? ['override' => true] : [];
        $source = realpath(__DIR__ . "/../../scaffolding");

        $fs = new Filesystem();
        $fs->mirror($source, $target, null, $options);

        $output->writeln("New site initialized in: <info>$target</info>");
    }

    private function parseTarget($target, $force)
    {
        $fs = new Filesystem();

        if (!$fs->exists($target)) {
            // Target dir does not exist, create it
            $fs->mkdir($target);
        } else {
            // Target dir exists, abort if it's not empty (unless --force)
            if (!$force) {
                $finder = new Finder();
                $finder->in($target);
                if (count($finder) > 0) {
                    throw new \Exception("Target folder exists and is not empty.");
                }
            }
        }

        return realpath($target);
    }
}
