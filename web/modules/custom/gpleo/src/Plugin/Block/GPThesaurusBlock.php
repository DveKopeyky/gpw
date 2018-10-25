<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Url;
use \Drupal\Core\Link;

/**
 * Provides a 'Thesaurus tabs' Block.
 *
 * @Block(
 *   id = "gpleo_thesaurus_block",
 *   admin_label = @Translation("GP LEO thesaurus tabs Block"),
 *   category = @Translation("TAGS"),
 * )
 */
class GPThesaurusBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Alphabetically tab.
    $AlphabeticallyTabURL = Url::fromRoute('view.thesaurus.page_alphabetically');
    $AlphabeticallyTabLink = Link::fromTextAndUrl(t('Alphabetically'), $AlphabeticallyTabURL);
    $AlphabeticallyTabLink = $AlphabeticallyTabLink->toRenderable();
    $AlphabeticallyTabLink['#attributes'] = [
      'class' => [
        'glossary-tab',
        'glossary-alphabetically-tab',
      ],
    ];
    $AlphabeticallyTabMarkup = render($AlphabeticallyTabLink);

    // By topic tab.
    $TopicTabURL = Url::fromRoute('view.glossary.page_1');
    $TopicTabLink = Link::fromTextAndUrl(t('By topic'), $TopicTabURL);
    $TopicTabLink = $TopicTabLink->toRenderable();
    $TopicTabLink['#attributes'] = [
      'class' => [
        'glossary-tab',
        'glossary-topic-tab',
      ],
    ];
    $TopicTabMarkup = render($TopicTabLink);

    // Label for tabs.
    $LabelMarkup = '<div class="glossary-tabs-label">' . t('Display glossary terms: ') . '</div>';

    // Download markup.
    $DownloadTermsMarkup = '<div class="glossary-download col-sm-3"></div>';

    // Tabs markup.
    $TabsMarkup = '<div class="glossary-tabs col-sm-9">'
      . $LabelMarkup
      . $AlphabeticallyTabMarkup
      . $TopicTabMarkup
      . '</div>';

    // Result markup.
    $ResultMarkup = '<div class="glossary-wrapper row">'
      . $TabsMarkup
      . $DownloadTermsMarkup
      . '</div>';

    return array(
      '#markup' => $ResultMarkup,
      '#attributes' => [
        'class' => [
          'glossary-tabs-block',
        ],
      ],
      '#attached' => [
        'library' => ['gpleo/glossary-tabs'],
      ],
    );
  }

}
