<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a 'GP Green' Block.
 *
 * @Block(
 *   id = "gpgreen_block",
 *   admin_label = @Translation("GP Green Block"),
 *   category = @Translation("Homepage"),
 * )
 */
class GPGreenBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    // Block title will be changed in the block config form by default.

    // It is not block title.
    $form['main_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Main title'),
      '#description' => $this->t('Main title for the block content.'),
      '#default_value' => isset($config['main_title']) ? $config['main_title'] : '',
    ];
    $form['main_title_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Main title URL'),
      '#description' => $this->t('Main title link, user will be redirected to this URL when title is pressed.'),
      '#default_value' => isset($config['main_title_url']) ? $config['main_title_url'] : '',
    ];

    // Description.
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Short description'),
      '#description' => $this->t('Block short description for the block.'),
      '#default_value' => isset($config['description']) ? $config['description'] : '',
    ];

    // Block image.
    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Block image'),
      '#description' => $this->t('This image will be used to display under the description.'),
      '#default_value' => isset($config['image']) ? $config['image'] : '',
      '#upload_location' => 'public://',
    ];

    // Action button.
    $form['action_button'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Action button text'),
      '#description' => $this->t('Action button text.'),
      '#default_value' => isset($config['action_button']) ? $config['action_button'] : '',
    ];
    $form['action_button_link'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Call to action link'),
      '#description' => $this->t('User will be redirected to this URL when action button is pressed.'),
      '#default_value' => isset($config['action_button_link']) ? $config['action_button_link'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['main_title'] = $form_state->getValue('main_title');
    $this->configuration['main_title_url'] = $form_state->getValue('main_title_url');
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['image'] = $form_state->getValue('image');
    $this->configuration['action_button'] = $form_state->getValue('action_button');
    $this->configuration['action_button_link'] = $form_state->getValue('action_button_link');

    if (isset($this->configuration['image'][0])) {
      $image = File::load($this->configuration['image'][0]);
      $image->setPermanent();
      $image->save();

      // Add image to file usage.
      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($image, 'gpleo', 'gpleo', \Drupal::currentUser()->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (isset($config['image'][0])) {
      $image = File::load($config['image'][0]);
      $config['image_url'] = $image->url();
    }

    return [
      '#theme' => 'gpleo_green_block',
      '#content' => $config,
      '#attributes' => [
        'class' => [
          'views-element-container',
          'contextual-region block',
          'block-views',
          'block-views-blockhomepage-courses-elearning-block',
          'block--homepage',
          'clearfix',
        ],
      ],
    ];
  }

}
