<?php

namespace sonfd\composer_unpack\Composer;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use sonfd\composer_unpack\Commands\UnpackCommand;

/**
 * List of all commands provided by this package.
 *
 * @internal
 */
class CommandProvider implements CommandProviderCapability {

  /**
   * {@inheritdoc}
   */
  public function getCommands() {
    return [new UnpackCommand()];
  }

}
