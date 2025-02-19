<?php

namespace Drupal\sis_pro_reports\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Handles routing for report pages.
 */
class ReportRouteController extends ControllerBase {

  /**
   * Handles report page display.
   */
  public function handleReportPage($category) {
    return [
      '#markup' => '', // Prevents "Page Not Found" but does not override block output
    ];
  }

  /**
   * Sets the page title dynamically.
   */
  public function getTitle($category) {
    return ucfirst($category) . ' Report';
  }
}
