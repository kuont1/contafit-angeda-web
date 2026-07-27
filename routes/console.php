<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('test', function () {
    $binary = base_path('vendor/bin/phpunit' . (PHP_OS_FAMILY === 'Windows' ? '.bat' : ''));

    $process = Process::fromShellCommandline('"' . $binary . '"', base_path());
    $process->setTimeout(null);
    $process->run(function (string $type, string $buffer): void {
        $this->output->write($buffer);
    });

    return $process->getExitCode();
})->purpose('Run the application test suite.');

Schedule::command('notifications:process-pending')->everyMinute();
