<?php

use Devtical\Helpers\Console\Commands\HelperMakeCommand;
use Illuminate\Filesystem\Filesystem;

it('can instantiate helper make command', function () {
    $command = new HelperMakeCommand(new Filesystem);

    expect($command)->toBeInstanceOf(HelperMakeCommand::class);
});

it('has correct command name', function () {
    $command = new HelperMakeCommand(new Filesystem);

    expect($command->getName())->toBe('make:helper');
});

it('has correct command description', function () {
    $command = new HelperMakeCommand(new Filesystem);

    expect($command->getDescription())->not->toBeEmpty();
});

it('has correct command options', function () {
    $command = new HelperMakeCommand(new Filesystem);

    expect($command->getDefinition()->hasOption('force'))->toBeTrue();
    expect($command->getDefinition()->hasOption('description'))->toBeTrue();
    expect($command->getDefinition()->hasOption('author'))->toBeTrue();
});

it('has correct command arguments', function () {
    $command = new HelperMakeCommand(new Filesystem);

    expect($command->getDefinition()->hasArgument('name'))->toBeTrue();
});
