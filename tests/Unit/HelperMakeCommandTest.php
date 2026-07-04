<?php

use Devtical\Helpers\Console\Commands\HelperMakeCommand;

it('can instantiate helper make command', function () {
    $command = new HelperMakeCommand;

    expect($command)->toBeInstanceOf(HelperMakeCommand::class);
});

it('has correct command name and options', function () {
    $command = new HelperMakeCommand;

    expect($command->getName())->toBe('make:helper');
    expect($command->getDefinition()->hasOption('force'))->toBeTrue();
    expect($command->getDefinition()->hasOption('description'))->toBeTrue();
    expect($command->getDefinition()->hasOption('author'))->toBeTrue();
    expect($command->getDefinition()->hasArgument('name'))->toBeTrue();
});
