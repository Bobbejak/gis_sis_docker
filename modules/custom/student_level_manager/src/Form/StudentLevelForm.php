<?php

namespace Drupal\student_level_manager\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

class StudentLevelForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'student_level_manager_form';
  }

  /**
 * AJAX callback to update the table when the reference year changes.
 */
public function updateTable(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['student_levels_wrapper']; // Return only the wrapper, ensuring no duplication
  }


  protected function buildStudentTable(array &$form, FormStateInterface $form_state, $reference_year, $filter_level) {
    $students = $this->getStudentsWithLevels($reference_year, $filter_level);

    // Table headers
    $table_headers = [
        'select' => [
            'data' => [
                '#type' => 'checkbox',
                '#attributes' => ['id' => 'select-all-students'],
            ],
        ],
        $this->t('Korean Name'),
        $this->t('English Name'),
        $this->t('Current Level'),
    ];

    for ($i = 0; $i < 5; $i++) {
        $table_headers[] = (string) ($reference_year - $i);
    }

    // Define the table
    $table = [
        '#type' => 'table',
        '#header' => $table_headers,
        '#attributes' => ['id' => 'student-level-table'],
    ];

    // Populate rows
    foreach ($students as $student_id => $student) {
        $row = [
            'select' => [
                '#type' => 'checkbox',
                '#attributes' => ['class' => ['student-checkbox']],
            ],
            'korean_name' => ['#markup' => $student['korean_name']],
            'english_name' => ['#markup' => $student['english_name']],
            'current_level' => ['#markup' => $student['current_level']],
        ];

        // Add academic year dropdowns
        for ($i = 0; $i < 5; $i++) {
            $year = $reference_year - $i;
            $default_level = $this->getDefaultLevel(
                $student['current_level'],
                $i,
                $student['graduation_year'],
                $reference_year,
                $student['enrolment_year']
            );
            $row[$year] = [
                '#type' => 'select',
                '#options' => $this->getLevelOptions(),
                '#default_value' => $student['levels'][$year] ?? $default_level,
            ];
        }

        $table[$student_id] = $row;
    }

    return $table;
  }







  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_year = '2030';
    $reference_year = $form_state->getValue('reference_year', $current_year);
    $filter_level = $form_state->getValue('current_level_filter', 'all');
    
    $form['#attached']['library'][] = 'student_level_manager/select_all';
    $form['#attached']['library'][] = 'sis_pro/datatables'; // Ensure DataTables is loaded

    // Container to wrap the student table and controls
    $form['student_levels_wrapper'] = [
        '#type' => 'container',
        '#attributes' => ['id' => 'student-level-wrapper'],
    ];

    // Dropdown for selecting the reference year.
    $form['student_levels_wrapper']['reference_year'] = [
        '#type' => 'select',
        '#title' => $this->t('Reference Year'),
        '#options' => array_combine(range($current_year, $current_year - 10), range($current_year, $current_year - 10)),
        '#default_value' => $reference_year,
        '#ajax' => [
            'callback' => '::updateTable',
            'wrapper' => 'student-level-wrapper',
        ],
    ];

    // Fetch available levels for the filter dropdown
    $form['student_levels_wrapper']['current_level_filter'] = [
        '#type' => 'select',
        '#title' => $this->t('Filter by Current Level'),
        '#options' => ['all' => $this->t('All Levels')] + $this->getLevelOptions(),
        '#default_value' => $form_state->getValue('current_level_filter', 'all'),
        '#ajax' => [
            'callback' => '::updateTable',
            'wrapper' => 'student-level-wrapper',
            'event' => 'change',
        ],
    ];

    // Table structure
    $form['student_levels_wrapper']['student_levels'] = $this->buildStudentTable($form, $form_state, $reference_year, $filter_level);
    
    // Submit button
    $form['student_levels_wrapper']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save Records'),
    ];

    return $form;
  }






  /**
   * Fetch students and their level history.
   */
  protected function getStudentsWithLevels($reference_year, $filter_level = 'all') {
    $students = [];

    $query = \Drupal::entityQuery('node')
        ->condition('type', 'student')
        ->accessCheck(FALSE);

    if ($filter_level !== 'all') {
        $query->condition('field_student_current_level.target_id', $this->getLevelTermId($filter_level));
    }

    $student_ids = $query->execute();
    foreach (Node::loadMultiple($student_ids) as $student) {
        $student_id = $student->id();
        $students[$student_id] = [
            'korean_name' => $student->get('field_student_korean_name')->value,
            'english_name' => $student->get('field_student_english_name')->value,
            'current_level' => $student->get('field_student_current_level')->entity->label(),
            'levels' => $this->getStudentHistory($student_id, $reference_year),
        ];
    }
    return $students;
  }






/**
 * Retrieve student level history.
 */
protected function getStudentHistory($student_id, $reference_year) {
  $history = [];
  $query = \Drupal::entityQuery('node')
      ->condition('type', 'student_level_history')
      ->condition('field_historical_student', $student_id)
      ->accessCheck(FALSE);

  $history_ids = $query->execute();
  foreach (Node::loadMultiple($history_ids) as $record) {
      $year = $record->get('field_historical_academic_year')->entity ? $record->get('field_historical_academic_year')->entity->label() : NULL;
      $level = $record->get('field_historical_level')->entity ? $record->get('field_historical_level')->entity->label() : NULL;

      // If level is NULL, set it to "N/A" instead of crashing
      $history[$year] = $level ?? 'N/A';
  }
  return $history;
}


  /**
   * Auto-fill level based on default progression.
   */
  protected function getDefaultLevel($current_level, $years_back, $graduation_year, $reference_year, $enrolment_year) {
    $progression = ['IG 1', 'IG 2', 'IG 3', 'Pre-BMC', 'BMC', 'Pre-GMC'];
    $year_of_interest = $reference_year - $years_back;

    // If the year is before the student enrolled, mark as "N/A"
    if ($enrolment_year && $year_of_interest < $enrolment_year) {
        return 'N/A';
    }

    // If the student graduated, handle graduation cases
    if ($current_level === 'Graduated from GIS' && $graduation_year) {
        if ($year_of_interest >= $graduation_year) {
            return 'Graduated from GIS';
        }
        if ($year_of_interest == ($graduation_year - 1)) {
            return 'Pre-GMC';
        }
        $index = array_search('Pre-GMC', $progression);
        $years_before_graduation = $graduation_year - $year_of_interest - 1;
        return ($index - $years_before_graduation >= 0) ? $progression[$index - $years_before_graduation] : 'N/A';
    }

    // Standard level progression logic
    $index = array_search($current_level, $progression);
    if ($index === FALSE || ($index - $years_back) < 0) {
        return 'N/A';
    }
    return $progression[$index - $years_back] ?? 'N/A';
}




/**
 * Get level options from taxonomy.
 */
protected function getLevelOptions() {
    $options = [];

    // Load all terms from the "current_level" taxonomy.
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('current_level');

    // Populate options with term names.
    foreach ($terms as $term) {
        $options[$term->name] = $term->name;
    }

    // Ensure "Graduated from GIS" is included if not in the taxonomy.
    $options['Graduated from GIS'] = 'Graduated from GIS';
    $options['N/A'] = 'N/A';

    return $options;
}

/**
 * Saves or updates student level history records.
 */
protected function saveStudentLevelHistory($student_id, $year, $level) {
  // Skip "N/A" levels - do not save them
  if ($level === 'N/A' || empty($level)) {
      return;
  }

  // Query to check if a record already exists
  $query = \Drupal::entityQuery('node')
      ->condition('type', 'student_level_history')
      ->condition('field_historical_student', $student_id)
      ->condition('field_historical_academic_year', $this->getAcademicYearTermId($year))
      ->accessCheck(FALSE);

  $existing = $query->execute();
  $existing_id = reset($existing); // Get first result

  if ($existing_id) {
      // Load existing node and update it
      $history_node = \Drupal\node\Entity\Node::load($existing_id);
      if ($history_node) {
          $history_node->set('field_historical_level', ['target_id' => $this->getLevelTermId($level)]);
          $history_node->save();

          \Drupal::messenger()->addMessage($this->t('Updated existing record for Student @id in year @year.', [
              '@id' => $student_id,
              '@year' => $year,
          ]));
      }
  } else {
      // If no existing record, create a new one
      $node = \Drupal\node\Entity\Node::create([
          'type' => 'student_level_history',
          'title' => "Level history for student $student_id in $year",
          'field_historical_student' => ['target_id' => $student_id],
          'field_historical_academic_year' => ['target_id' => $this->getAcademicYearTermId($year)],
          'field_historical_level' => ['target_id' => $this->getLevelTermId($level)],
      ]);
      $node->save();

      \Drupal::messenger()->addMessage($this->t('Saved new history for Student @id in year @year.', [
          '@id' => $student_id,
          '@year' => $year,
      ]));
  }
}



/**
 * Get the term ID for an academic year.
 */
protected function getAcademicYearTermId($year) {
  $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'academic_year')
      ->condition('name', $year)
      ->accessCheck(FALSE)
      ->execute();
  
  return $query ? reset($query) : NULL;
}

/**
* Get the term ID for a student level.
*/
protected function getLevelTermId($level) {
  $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', 'current_level')
      ->condition('name', $level)
      ->accessCheck(FALSE)
      ->execute();
  
  return $query ? reset($query) : NULL;
}




  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValue('student_levels') as $student_id => $data) {
        if (!empty($data['select'])) { // Only process selected students
            foreach ($data as $year => $level) {
                if (is_numeric($year)) { // Ensure we're processing year columns
                    $this->saveStudentLevelHistory($student_id, $year, $level);
                }
            }
        }
    }
    \Drupal::messenger()->addMessage($this->t('Selected student records have been updated.'));
}


}
