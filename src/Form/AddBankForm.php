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
class AddBankForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL)
  {

    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('Add bank account'),
    ];

    $form['bank_type'] = [
      '#type' => 'radios',
      '#options' => [0 => $this->t('Personal'), 1 => $this->t('Business')],
      '#prefix' => '<div class="card-group d-inline-flex w-100 mb-3 font-weight-500">',
      '#suffix' => '</div>',
      '#default_value' => 0,
      '#theme_wrappers' => [],
    ];

    $form['bank_country'] = [
      '#type' => 'select',
      '#title' => $this->t('Bank Country'),
      '#options' => CountryManager::getStandardList(),
      '#empty_option' => $this->t('-select-'),
      '#attributes' => [
        'class' => ['custom-select', 'font-size-16', 'black-shade6'],
        'disabled' => TRUE,
      ],
      '#required' => TRUE,
      '#default_value' => 'GB',
    ];

    $form['bank_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Bank Name'),
      '#options' => ['natwest' => 'Natwest Bank', 'barclays' => 'Barclays Bank', 'hsbc' => 'HSBC Bank'],
      '#empty_option' => $this->t('Please Select'),
      '#attributes' => [
        'class' => ['custom-select', 'font-size-16', 'black-shade6'],
      ],
      '#required' => TRUE,
    ];

    $form['account_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account Name'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('e.g. John Doe')
      ],
      '#required' => TRUE,
    ];

    $form['account_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Account Number'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('e.g. 12345678')
      ],
      '#required' => TRUE,
    ];

    $form['sort_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sort Code'),
      '#attributes' => [
        'class' => ['form-control', 'font-size-16', 'black-shade6'],
        'placeholder' => $this->t('e.g. 123456')
      ],
      '#required' => TRUE,
    ];

    $form['confirmed'] = [
      '#type' => 'checkboxes',
      '#options' => [1 => $this->t('I confirm the bank account details above')],
      '#required' => TRUE,
    ];

    $form['set_default'] = [
      '#type' => 'checkboxes',
      '#options' => [1 => $this->t('Set as default')],
      '#required' => TRUE,
    ];

    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add Bank Account'),
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

    //Redirect to same page
    $url = Url::fromRoute('bank_account_controller_page');
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'add_bank';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.add_bank'];
  }

  /**
   * Get user ID
   * @return int
   */
  public function getUserId() {
    return \Drupal::currentUser()->id();
  }

  /**
   * Get Bank type
   * @param $var
   * @return string
   */
  public function getBankType($var) {
    return ($var == 0)? 'Personal':'Business';
  }

  /**
   * Insert data into database
   * @param $form_state
   * @throws \Exception
   */
  public function insertData($form_state) {
    $default = isset($form_state->getUserInput()['set_default'][1])? intval($form_state->getUserInput()['set_default'][1]):0;


    $query = \Drupal::database()->insert('bank_account_banks');
    $query->fields([
      'uid',
      'type',
      'bank_country',
      'bank_name',
      'account_name',
      'account_number',
      'sort_code',
      'confirmed',
      'status',
      'default',
    ]);
    $query->values([
      $this->getUserId(),
      $this->getBankType($form_state->getUserInput()['bank_type']),
      $form_state->getValue('bank_country'),
      $form_state->getUserInput()['bank_name'],
      $form_state->getUserInput()['account_name'],
      $form_state->getUserInput()['account_number'],
      $form_state->getUserInput()['sort_code'],
      intval($form_state->getUserInput()['confirmed'][1]),
      1,//status
      $default,
    ]);
    $query->execute();
  }
}
