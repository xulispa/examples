<?php
namespace Drupal\drupaldeelitebase\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines Routing class.
 */
class Routing extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Hello, World!'),
    ];
  }

}