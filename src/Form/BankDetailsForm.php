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
class BankDetailsForm extends FormBase
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
    $countries = CountryManager::getStandardList();
    $country_code = $results[0]->bank_country;
    $country_array = $countries[$country_code];
    $country = $country_array->getUntranslatedString();

    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('Bank Account Details'),
    ];

    $form['bank_type'] = [
      '#type' => 'item',
      '#markup' => $this->t($results[0]->type)
    ];

    $form['bank_name'] = [
      '#type' => 'item',
      '#markup' => $this->t($this->getBankName($results[0]->bank_name)),
      '#theme_wrappers' => [],
    ];

    $form['account_name'] = [
      '#type' => 'item',
      '#markup' => $this->t($results[0]->account_name),
      '#theme_wrappers' => [],
    ];

    $form['account_number'] = [
      '#type' => 'item',
      '#markup' => $this->t($this->formatAccountNumber($results[0]->account_number)),
      '#theme_wrappers' => [],
    ];

    $form['bank_country'] = [
      '#type' => 'item',
      '#markup' => $this->t($country),
    ];

    $form['status'] = [
      '#type' => 'item',
      '#markup' => ($results[0]->status == 1)? $this->t('Approved'):$this->t('Not Approved'),
      '#theme_wrappers' => [],
    ];

    $form['default'] = [
      '#type' => 'item',
      '#markup' => ($results[0]->default == 1)? $this->t('Primary'):'',
      '#theme_wrappers' => [],
    ];

    $form['delete'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete Account'),
      '#attributes' => [
        'class' => ['payyed-btn', 'w-100', 'btn', 'btn-block', 'mt-2', 'font-weight-500', 'font-size-16', 'payyed-font', 'delete-cont'],
      ],
    ];


    // Attach the library for pop-up dialogs/modals.
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->deleteData();

    // Redirect to same page
    $url = Url::fromRoute('bank_account_controller_page');
    $form_state->setRedirectUrl($url);

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bank_info';
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames() {
    return ['config.bank_info'];
  }

  /**
   * Get user ID
   * @return int
   */
  public function getUserId() {
    return \Drupal::currentUser()->id();
  }

  /**
   * Get bank details
   * @param $var
   * @return mixed
   */
  public function selectData($var) {
    $database = \Drupal::database();
    $query = $database->select('bank_account_banks', 'ab');
    $query->fields('ab', ['rid','uid','type','bank_country','bank_name','account_name','account_number','sort_code','confirmed','status','default']);
    $query->condition('ab.uid', $this->getUserId());
    $query->condition('ab.rid', $var);
    $query->orderBy('default', 'DESC');

    return $query->execute()->fetchAll();
  }

  /**
   * Delete bank accounts
   */
  public function deleteData() {
    $query = \Drupal::database()->delete('bank_account_banks');
    $query->condition('uid', $this->getUserId());
    $query->condition('rid', $this->rid);
    $query->execute();
  }

  /**
   * Format account number for security
   * @param $var
   * @return string
   */
  public function formatAccountNumber($var) {
    $bank_number = str_split($var);
    return 'XXXXX-'.$bank_number[5].$bank_number[6].$bank_number[7];
  }

  /**
   * Get bank name
   * @param $var
   * @return string
   */
  public function getBankName($var) {
    switch ($var) {
      case 'natwest':
        return 'Natwest Bank';
      case 'barclays':
        return 'Barclays Bank';
      case 'hsbc':
        return 'HSBC Bank';
    }
  }

}
