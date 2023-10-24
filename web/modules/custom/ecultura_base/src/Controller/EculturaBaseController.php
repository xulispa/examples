<?php

namespace Drupal\ecultura_base\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for eCultura Base routes.
 */
class EculturaBaseController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
