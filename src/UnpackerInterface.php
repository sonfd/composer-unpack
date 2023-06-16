<?php

namespace sonfd\composer_unpack;

use Composer\Package\CompletePackageInterface;

/**
 * Interface UnpackerInterface.
 */
interface UnpackerInterface {

  /**
   * Unpack a package.
   *
   * Unpacking a package means to:
   * - merge all package requirements into composer.json.
   *
   * @param CompletePackageInterface $package
   *   The package to unpack.
   *
   * @return bool
   *   TRUE if the package was unpacked, otherwise FALSE.
   */
  public function unpack(CompletePackageInterface $package): bool;

}
