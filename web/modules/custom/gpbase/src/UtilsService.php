<?php

namespace Drupal\gpbase;

/**
 * Class UtilsService.
 */
class UtilsService implements UtilsServiceInterface {

  /**
   * Constructs a new UtilsService object.
   */
  public function __construct() {

  }

  /**
   * @inheritdoc
   */
  public function durationFieldsSum($field1, $field2, $_ = null) {
    $d1 = new \DateTime();
    $d2 = new \DateTime();
    foreach (func_get_args() as $value) {
      $d2->add(new \DateInterval($value));
    }
    return $d2->diff($d1);
  }

  /**
   * @inheritdoc
   */
  public function convertDateIntervalToDurationString(\DateInterval $dateInterval) {
    return [
      'year' => $dateInterval->y,
      'month' => $dateInterval->m,
      'day' => $dateInterval->d,
      'hour' => $dateInterval->h,
      'minute' => $dateInterval->i,
      'second' => $dateInterval->s,
    ];
  }
}
