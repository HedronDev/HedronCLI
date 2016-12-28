<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class UpdateHedronCommand extends HedronCommand {

  protected function configure() {
    $this->setName('core:update')
      ->setDescription('Update Hedron core to the newest available code.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $user_directory = trim(shell_exec("cd ~; pwd"));
    $this->setHedronDir($user_directory . DIRECTORY_SEPARATOR . '.hedron');
    $hedron_dir = $this->getHedronDir('hedron');
    if (!file_exists($hedron_dir . DIRECTORY_SEPARATOR . 'composer.json')) {
      throw new RuntimeException("The hedron directory or its composer.json file appear to be missing. Try running core:install first.");
    }
    $commands = [];
    $commands[] = "cd $hedron_dir";
    $commands[] = "composer update";
    shell_exec(implode('; ', $commands));
    // Rewrite existing project hooks
    $file = $this->getHedronDir('hedron.yml');
    $yaml = Yaml::parse(file_get_contents($file));
    $hedron_hooks = $this->getHedronDir('hedron', 'hooks');
    $files = [];
    foreach (array_diff(scandir($hedron_hooks), array('..', '.')) as $file_name) {
      $file = "#!{$yaml['php']}\n";
      $file .= file_get_contents($hedron_hooks . DIRECTORY_SEPARATOR . $file_name);
      $files[$file_name] = $file;
    }
    $projects_dir = $this->getHedronDir('project');
    foreach (array_diff(scandir($projects_dir), array('..', '.')) as $project) {
      if (file_exists($this->getHedronDir('repositories', $project, 'hooks'))) {
        foreach ($files as $file_name => $data) {
          $file_name = $this->getHedronDir('repositories', $project, 'hooks', $file_name);
          file_put_contents($file_name, $data);
        }
      }
    }
  }

}