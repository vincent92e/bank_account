<?php
namespace Drupal\bank_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;
use Drupal\Core\Url;

/**
 *
 * ExampleForm class.
 */
class AddCardForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL)
  {


    $form['debit'] = [
      '#type' => 'radios',
      '#options' => [1 => $this->t('Debit'), 0 => $this->t('Credit')],
      '#prefix' => '<div class="card-group d-inline-flex w-100 mb-3 font-weight-500">',
      '#suffix' => '</div>',
      '#default_value' => 0,
      '#theme_wrappers' => [],
    ];


    $form['card_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Card Type'),
      '#options' => ['visa' => 'Visa', 'master_card' => 'Master Card', 'a_express' => 'American Express', 'discover' => 'Discover'],
      '#empty_option' => $this->t('Card Type'),
      '#attributes' => [
        'class' => ['custom-select', 'font-size-16', 'black-shade6'],
      ],
      '#required' => TRUE,
    ];

    $form['card_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Card Number'),
      '#attributes' => [
        'id' => 'card-number',
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('0000 0000 0000 0000')
      ],
      '#required' => TRUE,
    ];

    $form['expiry_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Expiry Date'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('MM/YY')
      ],
      '#required' => TRUE,
    ];

    $form['cvv'] = [
      '#type' => 'password',
      '#title' => $this->t('CVV'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('CVV (3 digits)')
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
      '#required' => TRUE,
    ];

    $form['set_default'] = [
      '#type' => 'checkboxes',
      '#options' => [1 => $this->t('Set as default')],
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Card'),
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
    $this->insertData($form_state);

    // Redirect to same page
    $url = Url::fromRoute('bank_account_controller_page');
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'add_card';
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
    return ['config.add_card'];
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
   * Insert data into database
   * @param $form_state
   * @throws \Exception
   */
  public function insertData($form_state) {

    $default = isset($form_state->getUserInput()['set_default'][1])? intval($form_state->getUserInput()['set_default'][1]):0;

    $query = \Drupal::database()->insert('bank_account_cards');
    $query->fields([
      'uid',
      'type',
      'card_number',
      'expiry_date',
      'cvv',
      'card_name',
      'debit',
      'default',
    ]);
    $query->values([
      $this->getUserId(),
      $form_state->getUserInput()['card_type'],
      $form_state->getUserInput()['card_number'],
      $form_state->getUserInput()['expiry_date'],
      $form_state->getUserInput()['cvv'],
      $form_state->getUserInput()['card_name'],
      intval($form_state->getUserInput()['debit']),
      $default,
    ]);
    $query->execute();
  }
}
