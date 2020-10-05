<?php

namespace Drupal\bank_account\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class BankAccountController.
 */
class BankAccountController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function myPage() {
    return [
      '#markup' => '',
    ];
  }

}
