<?php

namespace sonfd\composer_unpack\Operations;

/**
 * Interface OperationInterface.
 */
interface OperationInterface {

  /**
   * Get the ID of the operation.
   *
   * @return string
   *   The ID of the operation.
   */
  public function getId(): string;

  /**
   * Perform the operation.
   *
   * @return bool
   *   TRUE if the operation was executed, otherwise FALSE.
   */
  public function execute(): bool;

}
