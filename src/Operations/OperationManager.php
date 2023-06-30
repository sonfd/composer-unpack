<?php

namespace sonfd\composer_unpack\Operations;

use sonfd\composer_unpack\Operations\OperationInterface;

/**
 * OperationManager class to manage a list of operations.
 */
class OperationManager implements OperationManagerInterface {

  /**
   * @var sonfd\composer_unpack\Operations\OperationInterface[]
   */
  protected array $operations = [];

  /**
   * {@inheritdoc}
   */
  public function addOperation(OperationInterface $operation): bool {
    if ($this->hasOperation($operation->getId())) {
      return FALSE;
    }

    $this->operations[$operation->getId()] = $operation;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function removeOperation(string $id): bool {
    if ($this->hasOperation($id)) {
      unset($this->operations[$id]);
    }

    return !$this->hasOperation($id);
  }

  /**
   * {@inheritdoc}
   */
  public function executeOperations(): bool {
    array_walk($this->operations, fn(OperationInterface $operation) =>
      $operation->execute() && $this->removeOperation($operation->getId())
    );

    return empty($this->operations);
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(): array {
    return $this->operations;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperation($id): ?OperationInterface {
    return $this->operations[$id] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasOperation(string $id): bool {
    return isset($this->operations[$id]);
  }

}
