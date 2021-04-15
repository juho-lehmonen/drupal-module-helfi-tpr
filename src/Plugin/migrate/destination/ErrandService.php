<?php

declare(strict_types = 1);

namespace Drupal\helfi_tpr\Plugin\migrate\destination;

use Drupal\helfi_api_base\Plugin\migrate\destination\TranslatableEntityBase;
use Drupal\helfi_tpr\Entity\Service;
use Drupal\migrate\Row;

/**
 * Provides a destination plugin for Tpr errand service entities.
 *
 * @MigrateDestination(
 *   id = "tpr_errand_service",
 * )
 */
final class ErrandService extends TranslatableEntityBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return 'tpr_errand_service';
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(Row $row, array $old_destination_id_values) {
    /** @var \Drupal\helfi_tpr\Entity\ErrandService $entity */
    $entity = parent::getEntity($row, $old_destination_id_values);

    if ($channels = $row->getSourceProperty('channels')) {
      $existing_channels = $entity->getData('channels', []);

      foreach ($channels as $channel) {
        $existing_channels[$row->getSourceProperty('language')] = $channel;
      }
      $entity->setData('channels', $existing_channels);
    }

    if ($descriptions = $row->getSourceProperty('exact_descriptions')) {
      $services = Service::loadMultiple($descriptions);

      array_map(function (Service $service) use ($entity) {
        $service->addErrandService($entity)
          ->save();
      }, $services);
    }

    return $entity;
  }

}
