<?php

namespace Drupal\gpbase;

use Drupal\duration_field\Service\DurationService;
use Drupal\eck\EckEntityInterface;
use Drupal\eck\Entity\EckEntity;
use Drupal\node\NodeInterface;

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

  /**
   * @inheritdoc
   */
  public function computeCourseFields(NodeInterface &$node) {
    if ($node->bundle() != 'course') {
      throw new \InvalidArgumentException();
    }
    $sections = array_column($node->field_course_sections->getValue(), 'target_id');

    $lecturesNumber = 0;
    $durationChildValues = [];
    foreach ($sections as $sectionId) {
      $section = EckEntity::load($sectionId);
      $durationChildValues[] = $section->field_video_duration->value;
      $lecturesNumber += $section->field_lectures_number->value;
    }
    $duration = $this->durationFieldsSum(...$durationChildValues);

    $node->set('field_lectures_number', $lecturesNumber);
    $node->set('field_video_duration', DurationService::convertValue($this->convertDateIntervalToDurationString($duration)));
  }
}
