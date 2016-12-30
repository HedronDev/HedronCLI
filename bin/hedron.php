#!/usr/local/bin/php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Hedron\CLI\Command\CreateClientCommand;
use Hedron\CLI\Command\CreateProjectCommand;
use Hedron\CLI\Command\DeleteProjectCommand;
use Hedron\CLI\Command\InstallHedronCommand;
use Hedron\CLI\Command\UpdateHedronCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new InstallHedronCommand());
$application->add(new UpdateHedronCommand());
$application->add(new CreateClientCommand());
$application->add(new CreateProjectCommand());
$application->add(new DeleteProjectCommand());

$application->run();
