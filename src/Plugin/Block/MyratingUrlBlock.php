<?php

namespace Drupal\myrating_url\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\myrating_url\MyratingUrlStorage;
use Drupal\votingapi\VoteResultFunctionManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Utility\Token;

/**
 * @Block(
 *   id = "myrating_url",
 *   admin_label = "Рейтинг страницы",
 * )
 */
class MyratingUrlBlock extends BlockBase implements ContainerFactoryPluginInterface {


  /**
   * The myrating_url storage.
   *
   * @var \Drupal\myrating_url\MyratingUrlStorageInterface
   */
  protected $myratingUrlStorage;


  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;


  /**
   * @var \Drupal\myrating_url\MyratingUrlInterface
   */
  protected $myrating_url;


  /**
   * The vote result manager.
   *
   * @var \Drupal\votingapi\VoteResultFunctionManager
   */
  protected $voteResultManager;





  /**
   * Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;


  /**
   * Constructs a new CartBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\myrating_url\MyratingUrlStorage $myrating_url_storage
   *   The database myrating_url storage.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\votingapi\VoteResultFunctionManager $vote_result_manager
   *   The vote result manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token utility.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MyratingUrlStorage $myrating_url_storage, RendererInterface $renderer, VoteResultFunctionManager $vote_result_manager, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->myratingUrlStorage = $myrating_url_storage;
    $this->renderer = $renderer;
    $this->myrating_url = $this->myratingUrlStorage->getMyratingUrlBySourcePath();
    $this->voteResultManager = $vote_result_manager;
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = $container->get('renderer');
    /* @var \Drupal\votingapi\VoteResultFunctionManager $vote_result_manager */
    $vote_result_manager = $container->get('plugin.manager.votingapi.resultfunction');
    /* @var \Drupal\Core\Utility\Token $token */
    $token = $container->get('token');
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('myrating_url'),
      $renderer,
      $vote_result_manager,
      $token
    );
  }


  /**
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\myrating_url\MyratingUrlInterface|mixed|null
   */
  public function getMyratingUrl() {
    return $this->myrating_url;
  }


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'text_submit' => 'Спасибо, ваш голос учтен!',
      'text_chema_org' => '
<div itemprop="creativeWorkSeries" itemscope itemtype="http://schema.org/CreativeWorkSeries" class="hidden">
  <div itemprop="name">[site:name]. [current-page:title]</div>.
  <div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating">
    Общий рейтинг: <span itemprop="ratingValue">%ratingValue</span>/
    <span itemprop="bestRating">%bestRating</span>
    на основе <span itemprop="reviewCount">%reviewCount</span> человек.
  </div>
</div>',
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['text_submit'] = [
      '#type' => 'textfield',
      '#title' => 'Текст после успешного голосования',
      '#default_value' => $config['text_submit'],
    ];
    $form['text_chema_org'] = [
      '#type' => 'textarea',
      '#title' => 'Текст микроразметки',
      '#default_value' => $config['text_chema_org'],
      '#rows' => 15,
    ];
    $form['token_tree'] = array(
      '#theme' => 'token_tree_link',
      '#token_types' => [],
      '#show_restricted' => TRUE,
      '#show_nested' => FALSE,
    );
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['text_submit'] = $form_state->getValue('text_submit');
    $this->configuration['text_chema_org'] = $form_state->getValue('text_chema_org');
  }


  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $build = [];
    $build['#cache']['contexts'] = ['route'];
    $build['#cache']['tags'] = ['myrating_url_list'];
    $myrating_url = $this->getMyratingUrl();
    $vote_count = 0;
    $vote_average = 100;
    if ($myrating_url) {
      $build['#cache']['tags'] = $myrating_url->getCacheTags();
      $results = $this->voteResultManager->getResults('myrating_url', $myrating_url->id());
      if (!empty($results['vote']['vote_count'])) {
        $vote_count = $results['vote']['vote_count'];
      }
      if (!empty($results['vote']['vote_average'])) {
        $vote_average = $results['vote']['vote_average'];
      }
    }
    $build['#attributes']['class'][] = 'block-myrating-url';
    $build['form'] = $this->myratingUrlStorage->getMyratingUrlForm(NULL, $config['text_submit']);


    $config['text_chema_org'] = $this->token->replace($config['text_chema_org'], [], array('clear' => TRUE));

    if ($vote_count) {
      $build['text_chema_org'] = [
        '#type' => 'markup',
        '#markup' => '<div class="text-chema-org">' .
          t($config['text_chema_org'], [
            '%ratingValue' => 5 * $vote_average / 100,
            '%bestRating' => '5',
            '%reviewCount' => $vote_count,
          ]) . '</div>',
      ];
    }

    return $build;
  }


}