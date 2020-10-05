<?php
namespace Drupal\bank_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 *
 * ExampleForm class.
 */
class UpdateCardForm extends FormBase
{

  /**
   * @var
   * Row Id of a card
   */
  protected $rid;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL, $rid = NULL) {
    $this->rid = intval($rid);
    $results = $this->selectData($this->rid);
    $img_path = $this->getImagePath($results[0]->type);
    $card_number = $this->formatCardNumber($results[0]->card_number);

    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('Update Card'),
    ];

    $form['card_number'] = [
      '#type' => 'item',
      '#markup' => $this->t('<span class="input-group-text type-img"><img src="@path" class="card-type"></span><input type="text" class="form-control" id="card-number" disabled="" value="@number" placeholder="Card Number">', ['@number' => $card_number, '@path' => $img_path]),
      '#theme_wrappers' => [],
    ];

    $form['expiry_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiry Date'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('MM/YY'),
      ],
      '#default_value' => $results[0]->expiry_date,
      '#required' => TRUE,
    ];

    $form['cvv'] = [
      '#type' => 'password',
      '#title' => $this->t('CVV'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('CVV (3 digits)'),
        'value' => $results[0]->cvv,
      ],
      '#required' => TRUE,
    ];

    $form['card_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Card Holder Name'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('Card Holder Name')
      ],
      '#default_value' => $results[0]->card_name,
      '#required' => TRUE,
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save Changes'),
      '#attributes' => [
        'class' => ['payyed-btn', 'w-100', 'btn', 'btn-block', 'mt-2', 'font-weight-500', 'font-size-16', 'payyed-font'],
      ],
    ];


    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $expiry_date = $form_state->getUserInput()['expiry_date'];
    $cvv = $form_state->getUserInput()['cvv'];
    $card_name = $form_state->getUserInput()['card_name'];

    $this->updateData($expiry_date, $cvv, $card_name);

    // Redirect to same page
    $url = Url::fromRoute('bank_account_controller_page');
    $form_state->setRedirectUrl($url);

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'update_card';
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
    return ['config.update_card'];
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
   * @param $var
   * @return mixed
   */
  public function selectData($var) {
    $database = \Drupal::database();
    $query = $database->select('bank_account_cards', 'ac');
    $query->fields('ac', ['rid','uid','type','card_number','expiry_date','cvv','card_name','debit','default']);
    $query->condition('ac.uid', $this->getUserId());
    $query->condition('ac.rid', $var);
    $query->orderBy('default', 'DESC');

    return $query->execute()->fetchAll();
  }

  /**
   * Update bank card
   * @param $expiry_date
   * @param $cvv
   * @param $card_name
   */
  public function updateData($expiry_date, $cvv, $card_name) {
    $current_user_id = $this->getUserId();

    $query = \Drupal::database()->update('bank_account_cards');
    $query->fields([
      'expiry_date' => $expiry_date,
      'cvv' => $cvv,
      'card_name' => $card_name,
    ]);
    $query->condition('uid', $current_user_id);
    $query->condition('rid', $this->rid);
    $query->execute();
  }

  /**
   * Get bank card type image
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
   * Format bank card number
   * @param $var
   * @return string
   */
  public function formatCardNumber($var) {
    $card_number = explode(' ', $var);
    return 'XXXX-XXXX-XXXX-'.$card_number[3];
  }

}
