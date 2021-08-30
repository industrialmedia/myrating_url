<?php

namespace Drupal\myrating_url;

use Drupal\Core\Entity\ContentEntityInterface;


/**
 * Provides an interface defining a MyratingUrl entity.
 * @ingroup myrating_url
 */
interface MyratingUrlInterface extends ContentEntityInterface {


  /**
   * Gets the source_path.
   *
   * @return string
   *   source_path of the myrating_url.
   */
  public function getSourcePath();


  /**
   * Sets the myrating_url source_path.
   *
   * @param string $source_path
   *   The myrating_url source_path.
   *
   * @return \Drupal\myrating_url\MyratingUrlInterface
   *   The called myrating_url entity.
   */
  public function setSourcePath($source_path);


}


