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
    return $this->hedronDir . $subdir;
  }

  /**
   * Set the absolute path to the current hedron directory.
   *
   * @param string $dir
   */
  public function setHedronDir(string $dir) {
    $this->hedronDir = $dir;
  }

}