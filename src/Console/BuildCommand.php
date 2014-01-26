<?php

namespace Starship\Console;

use Starship\Builder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Renders the web site')
            ->addOption(
               'source', 's', InputOption::VALUE_REQUIRED,
               'Source directory.', './'
            )
            ->addOption(
               'target', 't', InputOption::VALUE_REQUIRED,
               'Target directory.', './_site'
            )
            ->addOption(
               'config', 'c', InputOption::VALUE_REQUIRED,
               'Configuration file.', './_config.yml'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getOption('source');
        $target = $input->getOption('target');
        $config = $input->getOption('config');

        $fs = new Filesystem();
        if (!$fs->exists($source)) {
            throw new Exception("Source dir not found at \"$source\"");
        }
        if (!$fs->exists($target)) {
            $fs->mkdir($target);
        }
        if (!$fs->exists($config)) {
            throw new Exception("Config file not found at \"$config\"");
        }

        $source = realpath($source);
        $config = realpath($config);
        $target = realpath($target);

        $output->writeln("");
        $output->writeln("Source: <info>$source</info>");
        $output->writeln("Target: <info>$target</info>");
        $output->writeln("Config: <info>$config</info>");
        $output->writeln("");

        $start = microtime(true);

        $builder = new Builder($source, $target, $output);
        $builder->build();

        $end = microtime(true);
        $duration = round($end - $start, 3);

        $output->writeln("<comment>Done!</comment>");
        $output->writeln("");
        $output->writeln("Time taken: <info>$duration s</info>");
    }
}
