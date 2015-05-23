<?php namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {

    /**
     * We need this because ConsoleKernel doesn't have this property defined
     *
     * @var array
     */
    protected $commands = [];

}
