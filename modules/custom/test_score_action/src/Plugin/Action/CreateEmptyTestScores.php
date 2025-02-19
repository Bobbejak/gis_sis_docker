<?php

namespace Drupal\test_score_action\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * Creates empty Test Score entities for selected students.
 *
 * @Action(
 *   id = "create_empty_test_scores",
 *   label = @Translation("Create Empty Test Scores"),
 *   type = "node"
 * )
 */
class CreateEmptyTestScores extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity || $entity->bundle() !== 'student') {
      return;
    }

    // Create a new Test Score entity with an empty score and test field
    $test_score = Node::create([
      'type' => 'test_score',
      'title' => 'Test Score for ' . $entity->getTitle(),
      'field_student' => ['target_id' => $entity->id()],
      'field_test' => [], // Empty field (to be assigned later)
      'field_score' => [], // Empty field (to be assigned later)
    ]);

    try {
      $test_score->save();
      \Drupal::messenger()->addStatus(
        $this->t('Test Score created for student: @student', ['@student' => $entity->getTitle()])
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('Failed to create Test Score for student: @student. Error: @error', [
        '@student' => $entity->getTitle(),
        '@error' => $e->getMessage(),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('view', $account, TRUE);
  }
}
