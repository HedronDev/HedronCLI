<?php

require __DIR__ . '/../vendor/autoload.php';

use Hedron\CLI\Command\CreateClientCommand;
use Hedron\CLI\Command\CreateProjectCommand;
use Hedron\CLI\Command\DeleteProjectCommand;
use Hedron\CLI\Command\DockerDownCommand;
use Hedron\CLI\Command\DockerPSCommand;
use Hedron\CLI\Command\DockerRebuildCommand;
use Hedron\CLI\Command\DockerRemoveCommand;
use Hedron\CLI\Command\DockerUpCommand;
use Hedron\CLI\Command\InstallHedronCommand;
use Hedron\CLI\Command\UpdateHedronCommand;
use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new InstallHedronCommand());
$application->add(new UpdateHedronCommand());
$application->add(new CreateClientCommand());
$application->add(new CreateProjectCommand());
$application->add(new DeleteProjectCommand());
$application->add(new DockerPSCommand());
$application->add(new DockerUpCommand());
$application->add(new DockerRebuildCommand());
$application->add(new DockerRemoveCommand());
$application->add(new DockerDownCommand());

$application->run();
