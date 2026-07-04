<?php

use Devtical\Helpers\Console\Commands\HelperListCommand;

it('can instantiate helper list command', function () {
    $command = new HelperListCommand;

    expect($command)->toBeInstanceOf(HelperListCommand::class);
});

it('has correct command name and options', function () {
    $command = new HelperListCommand;

    expect($command->getName())->toBe('helper:list');
    expect($command->getDefinition()->hasOption('loaded'))->toBeTrue();
    expect($command->getDefinition()->hasOption('details'))->toBeTrue();
    expect($command->getDefinition()->hasOption('json'))->toBeTrue();
});
