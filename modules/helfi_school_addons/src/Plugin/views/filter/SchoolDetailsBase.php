<?php

declare(strict_types = 1);

namespace Drupal\helfi_school_addons\Plugin\views\filter;

use Drupal\helfi_school_addons\SchoolUtility;
use Drupal\helfi_tpr\Entity\OntologyWordDetails;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Base views filter class for school details.
 */
abstract class SchoolDetailsBase extends InOperator {

  /**
   * Ontology word ID used to limit filter options and results.
   *
   * @var int|null
   */
  protected ?int $wordId = NULL;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Allowed clarifications');
    $this->definition['options callback'] = [$this, 'generateOptions'];
  }

  /**
   * Builds the views filter query.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function query() {
    if (empty($this->value) || (isset($this->value[0]) && $this->value[0] === 'All')) {
      return;
    }

    if ($this->wordId === NULL) {
      return;
    }
    $this->queryByWordId($this->wordId);
  }

  /**
   * Add relationships to ontology word details and school details tables.
   *
   * @param int $wordId
   *   Ontology word details ID.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function queryByWordId(int $wordId) {
    // Join with tpr_ontology_word_details_field_data table.
    $owdFdConfiguration = [
      'table' => 'tpr_ontology_word_details_field_data',
      'field' => 'unit_id',
      'left_table' => 'tpr_unit_field_data',
      'left_field' => 'id',
      'operator' => '=',
    ];
    /** @var \Drupal\views\Plugin\views\join\JoinPluginBase $owdFdJoin */
    $owdFdJoin = Views::pluginManager('join')->createInstance('standard', $owdFdConfiguration);
    $this->query->addRelationship('owd_fd', $owdFdJoin, 'tpr_unit_field_data');

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $this->query->addWhere('AND', 'owd_fd.langcode', $language);
    $this->query->addWhere('AND', 'owd_fd.ontologyword_id', $wordId);

    // Join with tpr_ontology_word_details__school_details table.
    $owdSdConfiguration = [
      'table' => 'tpr_ontology_word_details__school_details',
      'field' => 'entity_id',
      'left_table' => 'owd_fd',
      'left_field' => 'id',
      'operator' => '=',
    ];
    /** @var \Drupal\views\Plugin\views\join\JoinPluginBase $owdSdJoin */
    $owdSdJoin = Views::pluginManager('join')->createInstance('standard', $owdSdConfiguration);
    $this->query->addRelationship('owd_sd', $owdSdJoin, 'owd_fd');

    $schoolYear = SchoolUtility::getCurrentSchoolYear();
    if ($schoolYear) {
      $this->query->addWhere('AND', 'owd_sd.school_details_schoolyear', $schoolYear);
    }

    $this->query->addWhere('AND', 'owd_sd.school_details_clarification', $this->value);
  }

  /**
   * Generates options from ontology word details.
   *
   * @return string[]
   *   Available options for the filter.
   */
  protected function generateOptions(): array {
    if ($this->wordId === NULL) {
      return [];
    }

    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $schoolYear = SchoolUtility::getCurrentSchoolYear();
    if ($schoolYear === NULL) {
      return [];
    }

    $details = [];
    $multipleOntologyWordDetails = OntologyWordDetails::loadMultipleByWordId($this->wordId);
    foreach ($multipleOntologyWordDetails as $ontologyWordDetails) {
      /** @var \Drupal\helfi_tpr\Entity\OntologyWordDetails $ontologyWordDetails */
      $details[] = $ontologyWordDetails->getDetailByAnother('school_details', 'clarification', 'schoolyear', $schoolYear, $langcode);
    }

    $options = array_map('ucfirst', array_merge(...$details));
    asort($options);
    return $options;
  }

}
