<?php

use Devtical\Helpers\Console\Commands\HelperListCommand;

it('can instantiate helper list command', function () {
    $command = new HelperListCommand;

    expect($command)->toBeInstanceOf(HelperListCommand::class);
});

it('has correct command name', function () {
    $command = new HelperListCommand;

    expect($command->getName())->toBe('helper:list');
});

it('has correct command description', function () {
    $command = new HelperListCommand;

    expect($command->getDescription())->not->toBeEmpty();
});

it('has correct command options', function () {
    $command = new HelperListCommand;

    expect($command->getDefinition()->hasOption('loaded'))->toBeTrue();
    expect($command->getDefinition()->hasOption('details'))->toBeTrue();
});
