# XConsole

PHP Development helper with:
- Local development server
- File watcher 
- Process runner/manager with support for multiple processes and their output (through symfony process) 
- Process manager with multiple windows tasks (wip)
- Laravel local dev server 
- Some slight color output variations (wip)
- Helpers for setting up easier command line debug tools
- Logger helper for better console and phpstorm log watcher. (wip)

## Install
- Only `composer require patrikgrinsvall/xconsole` should be needed, anything else is a bug
   

## Usage
- Show built in commands with `./x x:help`, `x.bat x:help`, `x.sh x:help`, `php artisan x:help`, `php x:help`
- Meaning, the `x` is a helper that can be runned in several ways
- Create Installation, migration, and other customizable setup helpers with `x:install`
- Cusomize dev server by extending Commands/SrvCommand (to be improved with recepies)


## Why?
After starting some frontend experiments, like quasar and electron where there is a separate frontend build process, which is quite complex, i didnt want to have to run multiple tasks. This project aims to fix that small issue, together with the fact that artisan console command is not detecting any changes except for .env changes. I wanted a way to clear all laravel caches and reoptimize when files are changed.
https://climate.thephpleague.com/styling/customizing/
https://event.thephpleague.com/3.0/
https://pipeline.thephpleague.com/usage/

#!/usr/bin/env php
<?php

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

require __DIR__.'/../vendor/autoload.php';

function cloneRepository(SymfonyStyle $ui, string $directory, string $branch, string $repository): void
{
    $filesystem = new Filesystem();
    if ($filesystem->exists($directory)) {
        $filesystem->remove($directory);
    }

    $ui->section(\sprintf('Cloning repository "%s":', $repository));
    (Process::fromShellCommandline(
        \sprintf(
            'git clone git@github.com:%s.git --branch %s %s',
            $repository,
            $branch,
            $directory
        )
    ))
        ->setTimeout(3600) // increase timeout as default timeout is 60 seconds in symfony process component
        ->run();

    $ui->text(\sprintf('Finished cloning repository "%s" into "%s".', $repository, $directory));
}

function getNewTag(SymfonyStyle $ui, string $directory, string $branch, string $previousTag): ?string
{
    $newTag = null;
    if ($previousTag) {
        $parts = \explode('.', $previousTag, 3);

        $newTag = $parts[0] . '.' . $parts[1] . '.' . (++$parts[2]);
    }

    do {
        $newTag = $ui->ask(\sprintf('What is the new tag for branch "%s"?', $branch), $newTag);
    } while(!$newTag);

    return $newTag;
}

function getPreviousTag(SymfonyStyle $ui, string $directory, string $branch): ?string
{
    $process = Process::fromShellCommandline('git tag --list', $directory);
    $process->run();
    $tags = explode(\PHP_EOL, $process->getOutput());

    $equalTags = [];
    foreach ($tags as $tag) {
        if (0 === \strpos($tag, $branch)) {
            $equalTags[] = $tag;
        }
    }

    if (empty($equalTags)) {
        $equalTags = $tags;
    }

    $previousTag = null;
    foreach ($equalTags as $equalTag) {
        if ($previousTag === null || version_compare(trim($previousTag, 'v'), trim($equalTag, 'v'), '<')) {
            $previousTag = $equalTag;
        }
    }

    if ($previousTag) {
        do {
            $previousTag = $ui->ask(\sprintf('What is the previous tagged version?'), $previousTag);
        } while(!$previousTag);
    }

    return $previousTag;
}

function setTag(SymfonyStyle $ui, string $directory, string $branch, $tag): void
{
    $ui->section(\sprintf('Set tag "%s" on %s:', $tag, $branch));
    $process = Process::fromShellCommandline(\sprintf('git tag %s', $tag), $directory);
    $process->run();
    $ui->text(\sprintf('Use tag "%s".', $tag));
}

function pushTag(SymfonyStyle $ui, string $directory, string $branch, $tag): void
{
    $ui->section(\sprintf('Push tag "%s" on %s:', $tag, $branch));
    $process = Process::fromShellCommandline(\sprintf('git push origin %s', $tag), $directory);
    $process->run();
    $ui->success(\sprintf('Pushed tag "%s".', $tag));
}

(new SingleCommandApplication())
    ->setVersion('1.0.0')
    ->setName('Release Script')
    ->addArgument('repository', InputArgument::REQUIRED)
    ->addArgument('branch', InputArgument::REQUIRED)
    ->addOption('--dry-run', null, InputOption::VALUE_NONE)
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $ui = new SymfonyStyle($input, $output);
        $repository = $input->getArgument('repository');
        $branch = $input->getArgument('branch');
        $dryRun = $input->getOption('dry-run');

        $ui->section('Configuration');

        if ('y' !== \strtolower($ui->ask(\sprintf('Create tags for branch "%s"?', $branch), 'y'))) {
            $ui->text('Canceled by user.');

            return 0;
        }

        $ui->text(\sprintf('Start tagging "%s" branch.', $branch));

        $directory = dirname(__DIR__) . '/var/releases';
        $suluDirectory = $directory . '/' . $repository . '/' . $branch;

        cloneRepository($ui, $suluDirectory, $branch, $repository);

        if ('y' !== \strtolower($ui->ask(\sprintf('Are all previous releases merged into the current branch?'), 'y'))) {
            $ui->text('Canceled by user.');

            return 0;
        }

        if ('y' !== \strtolower($ui->ask(\sprintf('Is the UPGRADE.md up to date and does not contain an unreleased section?'), 'y'))) {
            $ui->text('Canceled by user.');

            return 0;
        }

        if ('y' !== \strtolower($ui->ask(\sprintf('Is the composer.json up to date and does not contain @dev dependencies?'), 'y'))) {
            $ui->text('Canceled by user.');

            return 0;
        }

        if ($repository === 'sulu/skeleton') {
            if ('y' !== \strtolower($ui->ask(\sprintf('Does the composer.json contain the correct version of the "sulu/sulu" package?'), 'y'))) {
                $ui->text('Canceled by user.');

                return 0;
            }

            if ('y' !== \strtolower($ui->ask(\sprintf('Is the "public/build/admin" directory up to date?'), 'y'))) {
                $ui->text('Canceled by user.');

                return 0;
            }
        }

        $ui->section(\sprintf('Tagging branch %s:', $branch));
        $previousTag = getPreviousTag($ui, $suluDirectory, $branch);
        $newTag = getNewTag($ui, $suluDirectory, $branch, $previousTag);
        setTag($ui, $suluDirectory, $branch, $newTag);

        if ('y' !== \strtolower($ui->ask(\sprintf('Push tag "%s" on "%s" to remote?', $newTag, $branch), 'y'))) {
            $ui->text('Canceled by user.');

            return 0;
        }

        $ui->info(\sprintf(
            'To generate the changelog run:' . \PHP_EOL . \PHP_EOL . 'bin/generate-changelog %s %s...%s',
            $repository,
            $previousTag,
            $dryRun ? $branch : $newTag
        ));

        if ($dryRun) {
            $ui->writeln('Tag created but not pushed.');

            return 0;
        }

        pushTag($ui, $suluDirectory, $branch, $newTag);
    })->run();