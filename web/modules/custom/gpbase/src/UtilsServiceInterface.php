<?php

namespace Drupal\gpbase;

/**
 * Interface UtilsServiceInterface.
 */
interface UtilsServiceInterface {

  /**
   * Calculates the sum of multiple duration fields.
   *
   * @param string $field1
   *  Duration field value.
   *
   * @param string $field2
   *  Duration field value.
   *
   * @param array $_ [optional]
   *
   * @return bool|\DateInterval
   *  The DateInterval object representing the sum of duration fields or FALSE on failure.
   *
   * @throws \Exception
   */
  public function durationFieldsSum($field1, $field2, $_ = null);

  /**
   * Converts a date interval to an array of values supported by duration_field.
   *
   * @param \DateInterval $dateInterval
   *
   * @return array
   */
  public function convertDateIntervalToDurationString(\DateInterval $dateInterval);

}
