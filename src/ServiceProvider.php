<?php

namespace Kristories\Helpers;

use Kristories\Helpers\Console\Commands\HelperMakeCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/helpers.php';
}
