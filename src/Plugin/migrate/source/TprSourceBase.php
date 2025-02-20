<?php

declare(strict_types=1);

namespace Drupal\helfi_tpr\Plugin\migrate\source;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\Plugin\migrate\source\HttpSourcePluginBase;

/**
 * Base class for TPR sources.
 */
abstract class TprSourceBase extends HttpSourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  protected bool $useRequestCache = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getIds() : array {
    return [
      'id' => [
        'type' => 'string',
      ],
      'language' => [
        'type' => 'string',
        'entity_key' => 'langcode',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields() : array {
    return [];
  }

}
