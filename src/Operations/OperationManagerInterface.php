<?php

namespace sonfd\composer_unpack\Operations;

use sonfd\composer_unpack\Operations\OperationInterface;

/**
 * Interface OperationManagerInterface.
 */
interface OperationManagerInterface {

  /**
   * Add an operation.
   *
   * @param sonfd\composer_unpack\Operations\OperationInterface $operation
   *   The operation to add.
   *
   * @return bool
   *  TRUE if the operation was added, FALSE otherwise.
   */
  public function addOperation(OperationInterface $operation): bool;

  /**
   * Bulk add operations.
   *
   * @param sonfd\composer_unpack\Operations\OperationInterface[] $operations
   *
   * @return void
   */
  public function addOperations(array $operations): void;

  /**
   * Check if an operation exists with a given id.
   *
   * @param string $id
   *   The id of the operation to check.
   */
  public function hasOperation(string $id): bool;

  /**
   * Remove an operation with a given id.
   *
   * @param string $id
   *   The id of the operation to remove.
   */
  public function removeOperation(string $id): bool;

  /**
   * Get all operations.
   *
   * @return sonfd\composer_unpack\Operations\OperationInterface[]
   *   An array of all operations.
   */
  public function getOperations(): array;

  /**
   * Get an operation by id.
   *
   * @param string $id
   *  The id of the operation to get.
   *
   * @return sonfd\composer_unpack\Operations\OperationInterface|null
   *   The operation if it exists, otherwise NULL.
   */
  public function getOperation(string $id): ?OperationInterface;

  /**
   * Execute all operations.
   *
   * @return bool
   *   TRUE if all operations were executed, FALSE otherwise.
   */
  public function executeOperations(): bool;

}
