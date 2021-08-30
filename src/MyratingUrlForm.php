<?php

namespace Drupal\myrating_url;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Component\Datetime\TimeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\votingapi\VoteResultFunctionManager;




/**
 * Form controller for the myrating_url entity edit forms.
 *
 * @ingroup myrating_url
 */
class MyratingUrlForm extends ContentEntityForm {


  /**
   * The route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;


  /**
   * The vote result manager.
   *
   * @var \Drupal\votingapi\VoteResultFunctionManager
   */
  protected $voteResultManager;


  /**
   * Constructs a new OrderForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   * @param \Drupal\votingapi\VoteResultFunctionManager $vote_result_manager
   *   The vote result manager.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, RouteProviderInterface $route_provider, VoteResultFunctionManager $vote_result_manager) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->routeProvider = $route_provider;
    $this->voteResultManager = $vote_result_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /* @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = $container->get('entity.repository');
    /* @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info */
    $entity_type_bundle_info = $container->get('entity_type.bundle.info');
    /* @var \Drupal\Component\Datetime\TimeInterface $time */
    $time = $container->get('datetime.time');
    /* @var \Drupal\Core\Routing\RouteProviderInterface $route_provider */
    $route_provider = $container->get('router.route_provider');
    /* @var \Drupal\votingapi\VoteResultFunctionManager $vote_result_manager */
    $vote_result_manager = $container->get('plugin.manager.votingapi.resultfunction');
    return new static(
      $entity_repository,
      $entity_type_bundle_info,
      $time,
      $route_provider,
      $vote_result_manager
    );
  }


  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    /* @var $entity \Drupal\myrating_url\Entity\MyratingUrl */
    $form = parent::buildForm($form, $form_state);

    /* @var $callback_object \Drupal\myrating_url\MyratingUrlForm */
    $callback_object = $form_state->getBuildInfo()['callback_object'];
    $operation = $callback_object->getOperation();




    // Подмена значения по умолчанию, фикс модуля фивестар
    if (isset($form['rating']['widget'][0]['rating'])) {
      $myrating_url = $callback_object->getEntity();
      $results = $this->voteResultManager->getResults('myrating_url', $myrating_url->id());
      $vote_average = 100;
      if (!empty($results['vote']['vote_average'])) {
        $vote_average = $results['vote']['vote_average'];
      }
      $form['rating']['widget'][0]['rating']['#default_value'] = $vote_average;
    }

    if ($operation == 'edit') {
      $form['source_path']['#disabled'] = 'disabled';
    } elseif ($operation == 'add_or_edit') {
      unset($form['actions']['delete']);
      $form['#attached']['library'][] = 'myrating_url/myrating_url';
      $form['actions']['#attributes']['style'] = 'display:none';
    }

    $form['actions']['#weight'] = 501;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);
    /* @var $myrating_url \Drupal\myrating_url\Entity\MyratingUrl */
    $myrating_url = $this->entity;

    /* @var $callback_object \Drupal\myrating_url\MyratingUrlForm */
    $callback_object = $form_state->getBuildInfo()['callback_object'];
    $operation = $callback_object->getOperation();
    $storage = $form_state->getStorage();

    if ($operation == 'add_or_edit') {
      if (!empty($storage['text_submit'])) {
        $this->messenger()->addStatus($this->t($storage['text_submit']));
      }
    }
    else {
      if ($status == SAVED_UPDATED) {
        $this->messenger()
          ->addStatus($this->t('The rating %feed has been updated.', [
            '%feed' => $myrating_url->toLink()
              ->toString()
          ]));
      } else {
        $this->messenger()
          ->addStatus($this->t('The rating %feed has been added.', [
            '%feed' => $myrating_url->toLink()
              ->toString()
          ]));
      }
    }

    // Redirect
    $url = Url::fromUserInput($myrating_url->getSourcePath());
    $form_state->setRedirectUrl($url);

    return $status;
  }






}

