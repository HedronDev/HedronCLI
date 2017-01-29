<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CoreComposer extends HedronCommand {

  protected function configure() {
    $this->setName('core:composer')
      ->setDescription('Add composer-able dependencies to Hedron core.')
      ->setHelp('Installs new composer-able dependencies into Hedron\'s install directory.')
      ->addArgument('package', InputArgument::REQUIRED)
      ->addArgument('version', InputArgument::OPTIONAL);
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $install_dir = $this->getHedronDir('hedron');
    $commands = [];
    $commands[] = "cd $install_dir";
    $package = $input->getArgument('package');
    $this->validatePackage($package);
    if ($version = $input->getArgument('version')) {
      $commands[] = "composer require $package $version";
    }
    else {
      $commands[] = "composer require $package";
    }
    shell_exec(implode('; ', $commands));
  }

  protected function validatePackage(string $package) {
    list($owner, $project) = explode('/', $package);
    if (!$owner || !$project) {
      throw new RuntimeException("Package must be in owner/project format per normal composer syntax.");
    }
  }

}
