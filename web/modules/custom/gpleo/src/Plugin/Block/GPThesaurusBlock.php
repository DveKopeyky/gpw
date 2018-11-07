<?php

namespace Drupal\gpleo\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Url;
use \Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;


/**
 * Provides a 'Thesaurus tabs' Block.
 *
 * @Block(
 *   id = "gpleo_thesaurus_block",
 *   admin_label = @Translation("GP LEO thesaurus tabs Block"),
 *   category = @Translation("TAGS"),
 * )
 */
class GPThesaurusBlock extends BlockBase implements ContainerFactoryPluginInterface {


  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

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
    $DownloadTermsURL = Url::fromRoute('gpthesaurus.to_xls');
    $DownloadTermsLink = Link::fromTextAndUrl(t('Download All Terms'), $DownloadTermsURL);
    $DownloadTermsLink = $DownloadTermsLink->toRenderable();
    $DownloadTermsLink['#attributes'] = [
      'class' => [
        'glossary-download',
        'col-sm-3',
        'btn',
        'btn-outline',
      ],
    ];
    $DownloadTermsMarkup = render($DownloadTermsLink);

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

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->routeMatch->getRouteName() == 'entity.taxonomy_term.canonical'
      && $tid =$this->routeMatch->getRawParameter('taxonomy_term')
    ) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
      if ($term->bundle() == 'thesaurus') {
        return AccessResult::forbidden();
      }
    }
    return parent::blockAccess($account);
  }


}
