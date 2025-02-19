<?php

namespace Drupal\test_score_action\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * Bulk assign Test Scores to a Test.
 *
 * @Action(
 *   id = "assign_test_score_to_test",
 *   label = @Translation("Assign Test Score to Test"),
 *   type = "node"
 * )
 */
class AssignTestScoreToTest extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'test_id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Load all available Tests
    $query = \Drupal::entityQuery('node')
    ->condition('type', 'test')
    ->sort('created', 'DESC')
    ->accessCheck(TRUE); // ✅ Ensures proper entity access check
  
  $test_ids = $query->execute();
    $tests = Node::loadMultiple($test_ids);

    $options = [];
    foreach ($tests as $test) {
      $options[$test->id()] = $test->getTitle();
    }

    $form['test_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Test'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $test_id = $form_state->getValue('test_id');

    if (!is_array($this->configuration)) {
      $this->configuration = [];
    }

    $this->configuration['test_id'] = is_array($test_id) ? reset($test_id) : $test_id;

    \Drupal::logger('test_score_action')->notice('Stored Test ID in configuration: @id', [
      '@id' => $this->configuration['test_id'],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    if (!$entity || $entity->bundle() !== 'test_score') {
      return;
    }

    // Retrieve the stored Test ID
    $test_id = $this->configuration['test_id'] ?? NULL;

    if (empty($test_id)) {
      \Drupal::logger('test_score_action')->warning('No test ID found in execute().');
      \Drupal::messenger()->addError($this->t('No test selected.'));
      return;
    }

    // Assign the test
    $entity->set('field_test', ['target_id' => $test_id]);

    try {
      $entity->save();
      \Drupal::messenger()->addStatus(
        $this->t('Assigned test to Test Score for student: @student', [
          '@student' => $entity->getTitle(),
        ])
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('Failed to assign test. Error: @error', [
        '@error' => $e->getMessage(),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $object->access('update', $account, TRUE);
  }
}
