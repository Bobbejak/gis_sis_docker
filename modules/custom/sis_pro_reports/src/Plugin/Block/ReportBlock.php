<?php

namespace Drupal\sis_pro_reports\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Provides a 'ReportBlock' block.
 *
 * @Block(
 *   id = "report_block",
 *   admin_label = @Translation("Report Block"),
 *   category = @Translation("Custom")
 * )
 */
class ReportBlock extends BlockBase {

/**
 * {@inheritdoc}
 */
/**
 * {@inheritdoc}
 */
public function build() {
    $route_name = \Drupal::routeMatch()->getRouteName();
  
    // Only show the block on /report/{category} pages.
    if ($route_name !== 'sis_pro_reports.report') {
      return [];
    }
  
    $category = \Drupal::routeMatch()->getParameter('category') ?? 'science';
    $students = $this->fetchReportData($category);
  
    return [
      '#theme' => 'block__report__' . $category,
      '#report_category' => $category,
      '#students' => $students,
    ];
  }
  
  

  /**
   * Fetch report data.
   */
  private function fetchReportData($category) {
    $subjects = ($category === 'science')
      ? ['Physics', 'Biology', 'Chemistry']
      : ['English Literature', 'Grammar', 'Writing'];

    $query = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', 'test_score')
      ->condition('field_subject', $subjects, 'IN')
      ->execute();

    $test_scores = \Drupal\node\Entity\Node::loadMultiple($query);
    
    $students = [];
    foreach ($test_scores as $score) {
      $student_id = $score->get('field_student')->target_id;
      $subject = $score->get('field_subject')->entity->label();
      $raw_score = $score->get('field_score')->value;
      $weight = 1.2;

      $final_score = $raw_score * $weight;
      $students[$student_id]['name'] = $score->get('field_student')->entity->label();
      $students[$student_id]['scores'][$subject] = $final_score;
    }

    return $students;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0; // Prevents caching during development
  }
}
