<?php

namespace Drupal\test_score_action\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;

/**
 * Create test score entries.
 *
 * @Action(
 *   id = "create_test_score_action",
 *   label = @Translation("Create test scores"),
 *   type = "node"
 * )
 */
class CreateTestScoreAction extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'test_id' => '',
      'score' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['score'] = [
      '#type' => 'number',
      '#title' => $this->t('Score'),
      '#step' => '0.01',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['score'] = $form_state->getValue('score');
    // Get test ID from URL parameter
    $current_path = \Drupal::service('path.current')->getPath();
    $path_args = explode('/', $current_path);
    $this->configuration['test_id'] = end($path_args);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $access = $object->access('view', $account, TRUE);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\node\NodeInterface $entity */
    if ($entity === NULL || $entity->bundle() !== 'student') {
      return;
    }

    // Create test score node
    $test_score = Node::create([
      'type' => 'test_score',
      'title' => 'Score for ' . $entity->getTitle(),
      'field_student' => ['target_id' => $entity->id()],
      'field_test' => ['target_id' => $this->configuration['test_id']],
      'field_score' => $this->configuration['score'],
    ]);

    $test_score->save();
  }

}