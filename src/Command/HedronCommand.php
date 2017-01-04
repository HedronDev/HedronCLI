<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Command\Command;

abstract class HedronCommand extends Command {

  /**
   * The absolute path to the hedron directory.
   *
   * @var string
   */
  protected $hedronDir;

  /**
   * Gets the current hedron directory or subdirectories within it.
   *
   * @param \string[] ...$subdirs
   *
   * @return string
   */
  public function getHedronDir(string ...$subdirs) {
    $subdir = '';
    foreach ($subdirs as $dir) {
      $subdir .= DIRECTORY_SEPARATOR . $dir;
    }
    if (empty($this->hedronDir)) {
      $user_directory = trim(shell_exec("cd ~; pwd"));
      $this->hedronDir = $user_directory . DIRECTORY_SEPARATOR . '.hedron';
    }
    return $this->hedronDir . $subdir;
  }

}