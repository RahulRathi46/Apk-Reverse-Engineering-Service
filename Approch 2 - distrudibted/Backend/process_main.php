<?php

require 'vendor/autoload.php';

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

$cmd = "";

if (defined('STDIN')) {

    $cmd = $argv[1];

    print_r($cmd);

    $process = new Process($cmd);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    echo $process->getOutput();
    print_r("\n");
}

?>