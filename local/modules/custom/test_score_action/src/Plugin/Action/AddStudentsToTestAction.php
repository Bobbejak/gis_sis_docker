<?php

namespace Drupal\test_score_action\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Add students to a test by creating Test Score nodes.
 *
 * @Action(
 *   id = "add_students_to_test_action",
 *   label = @Translation("Add students to test"),
 *   type = "node"
 * )
 */
class AddStudentsToTestAction extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'test_id' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $current_url = \Drupal::request()->getRequestUri(); // Get full URL path
    parse_str(parse_url($current_url, PHP_URL_QUERY), $query_params); // Extract query parameters
  
    $test_id = $query_params['test_id'] ?? NULL; // Manually get test_id
  
    \Drupal::logger('test_score_action')->notice('Extracted Test ID from URL Manually: @id', [
      '@id' => $test_id,
    ]);
  
    $form['test_id'] = [
      '#type' => 'hidden',
      '#value' => $test_id,
    ];
  
    return $form;
  }
  
  
  
  
  

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['test_id'] = $form_state->getValue('test_id');
  
    \Drupal::logger('test_score_action')->notice('Stored Test ID in submitConfigurationForm: @id', [
      '@id' => $this->configuration['test_id'],
    ]);
  }
  
  
  

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    // Allow action if the user can view the node.
    $access = $object->access('view', $account, TRUE);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */

   
   public function execute($entity = NULL) {
    if (!$entity || $entity->bundle() !== 'student') {
      return;
    }
  
    // Try to get test_id from a Views field instead
    $view_display = $entity->get('view_display');
    $test_id = $view_display->getValue()[0]['value'] ?? NULL;
  
    \Drupal::logger('test_score_action')->notice('Extracted Test ID from Hidden Views Field: @id', [
      '@id' => $test_id,
    ]);
  
    if (!$test_id) {
      \Drupal::messenger()->addError($this->t('No test ID found.'));
      return;
    }
  
    // Validate the test ID
    $test = Node::load($test_id);
    if (!$test) {
      \Drupal::messenger()->addError($this->t('Invalid test ID: @id', ['@id' => $test_id]));
      return;
    }
  
    // Create Test Score entity
    $test_score = Node::create([
      'type' => 'test_score',
      'title' => 'Test Score for ' . $entity->getTitle(),
      'field_student' => ['target_id' => $entity->id()],
      'field_test' => ['target_id' => $test_id],
    ]);
  
    try {
      $test_score->save();
      \Drupal::messenger()->addStatus(
        $this->t('Test score created for student: @student', ['@student' => $entity->getTitle()])
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('Failed to create test score for student: @student. Error: @error', [
        '@student' => $entity->getTitle(),
        '@error' => $e->getMessage(),
      ]));
    }
  }
  
   
  

}
