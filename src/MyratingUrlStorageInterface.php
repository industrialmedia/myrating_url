<?php

namespace Drupal\myrating_url;

use Drupal\Core\Entity\ContentEntityStorageInterface;


interface MyratingUrlStorageInterface extends ContentEntityStorageInterface {


  /**
   * Get myrating_url by source_path
   *
   * @param string|null $source_path
   * @return \Drupal\myrating_url\MyratingUrlInterface
   */
  public function getMyratingUrlBySourcePath($source_path = NULL);



  /**
   * Get myrating_url form
   *
   * @param string|null $path
   * @param string|null $text_submit
   * @return array
   */
  public function getMyratingUrlForm($path = NULL, $text_submit = '');


}
