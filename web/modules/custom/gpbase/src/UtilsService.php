<?php

namespace Drupal\gpbase;

use Drupal\duration_field\Service\DurationService;
use Drupal\eck\EckEntityInterface;
use Drupal\eck\Entity\EckEntity;

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
  public function durationFieldsSum($field1 = 'PT0S', $field2 = 'PT0S', $_ = NULL) {
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

  /**
   * @inheritdoc
   */
  public function computeCourseSectionFields(EckEntityInterface &$entity) {
    if ($entity->bundle() != 'course_section') {
      throw new \InvalidArgumentException();
    }
    $lectures = array_column($entity->field_videos->getValue(), 'target_id');
    $entity->set('field_lectures_number', count($lectures));

    $durationChildValues = [];
    foreach ($lectures as $lectureId) {
      $video = EckEntity::load($lectureId);
      $durationChildValues[] = $video->field_video_duration->value;
    }
    $duration = $this->durationFieldsSum(...$durationChildValues);

    $entity->set('field_video_duration', DurationService::convertValue($this->convertDateIntervalToDurationString($duration)));
  }
}
