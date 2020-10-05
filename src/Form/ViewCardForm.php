<?php
namespace Drupal\bank_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 *
 * ExampleForm class.
 */
class ViewCardForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $host = \Drupal::request()->getSchemeAndHttpHost();

    $results = $this->selectData();
    $counter = 1;

    foreach ($results as $result) {
      $container = 'card_'.$counter.'_details';
      $container_id = 'card-'.$counter.'-details';
      $img_path = $this->getImagePath($result->type);
      $rid = $result->rid;

      $form[$container] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => [$container_id],
        ],
        '#tree' => TRUE,
      ];

      $form[$container]['card_type'] = [
        '#type' => 'item',
        '#markup' => '<img src="'.$img_path.'" class="card-type">',
        '#theme_wrappers' => [],
      ];

      $form[$container]['card_number'] = [
        '#type' => 'item',
        '#markup' => '<p class="font-size-18">'.$this->t($this->formatCardNumber($result->card_number)).'</p>',
        '#theme_wrappers' => [],
      ];

      $form[$container]['expiry_date'] = [
        '#type' => 'item',
        '#markup' => $this->t($result->expiry_date),
        '#theme_wrappers' => [],
      ];

      $form[$container]['card_name'] = [
        '#type' => 'item',
        '#markup' => $this->t($result->card_name),
        '#theme_wrappers' => [],
      ];

      $form[$container]['default'] = [
        '#type' => 'item',
        '#markup' => ($result->default == 1)? $this->t('Primary'):'',
        '#theme_wrappers' => [],
      ];

      $form[$container]['edit_card'] = [
        '#type' => 'item',
        '#markup' => $this->t('<a class="use-ajax text-light btn-link mx-2" data-toggle="modal" data-dialog-type="modal" href="@host/payyed/bank_account/view/update_cards/@rid" data-dialog-options="{&quot;width&quot;:500}"><span class="mr-1"><i class="fa fa-edit"></i></span>Edit</a>', ['@rid' => $result->rid,'@host' => $host]),
      ];

      $form[$container]['delete_card'] = [
        '#type' => 'item',
        '#markup' => $this->t('<a class="use-ajax text-light btn-link mx-2" data-toggle="modal" data-dialog-type="modal" href="@host/payyed/bank_account/delete/card/@rid" data-dialog-options="{&quot;width&quot;:350}"><span class="mr-1"><i class="fa fa-minus-circle"></i></span>Delete</a>', ['@rid' => $result->rid,'@host' => $host]),
      ];

      $counter++;
    }

    $form['add_card'] = [
      '#type' => 'link',
      '#title' => $this->t('Add New Card'),
      '#url' => Url::fromUri($host.'/payyed/view/bank_account/AddCardForm'),
      '#attributes' => [
        'class' => ['use-ajax', 'black-shade1', 'font-weight-400', 'add-card', 'text-decoration-none'],
      ],
    ];


    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'view_card';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames()
  {
    return ['config.view_card'];
  }

  /**
   * Get user ID
   * @return int
   */
  public function getUserId()
  {
    return \Drupal::currentUser()->id();
  }

  /**
   * Get bank card details
   * @return mixed
   */
  public function selectData() {
    $database = \Drupal::database();
    $query = $database->select('bank_account_cards', 'ac');
    $query->fields('ac', ['rid','uid','type','card_number','expiry_date','cvv','card_name','debit','default']);
    $query->condition('ac.uid', $this->getUserId());
    $query->orderBy('default', 'DESC');

    return $query->execute()->fetchAll();
  }

  /**
   * Get card type image path
   * @param $var
   * @return string
   */
  public function getImagePath($var) {
    $module_path = drupal_get_path('module', 'bank_account');

    switch ($var) {
      case 'visa':
        return file_create_url($module_path . '/img/visa.png');
      case 'master_card':
        return file_create_url($module_path . '/img/master-card.png');
      case 'a_express':
        return file_create_url($module_path . '/img/american-express.png');
      case 'discover':
        return file_create_url($module_path . '/img/discover.png');
    }
  }

  /**
   * Format card number
   * @param $var
   * @return string
   */
  public function formatCardNumber($var) {
    $card_number = explode(' ', $var);
    return 'XXXX-XXXX-XXXX-'.$card_number[3];
  }

}
