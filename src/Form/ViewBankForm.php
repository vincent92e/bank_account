<?php
namespace Drupal\bank_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 *
 * ExampleForm class.
 */
class ViewBankForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $options = NULL) {

    $host = \Drupal::request()->getSchemeAndHttpHost();

    $results = $this->selectData();
    $counter = 1;

    foreach ($results as $result) {
      $container = 'bank_'.$counter.'_details';
      $container_id = 'bank-'.$counter.'-details';

      $form[$container] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => [$container_id],
        ],
        '#tree' => TRUE,
      ];

      $form[$container]['bank_name'] = [
        '#type' => 'item',
        '#markup' => $this->t($this->getBankName($result->bank_name)),
        '#theme_wrappers' => [],
      ];

      $form[$container]['account_number'] = [
        '#type' => 'item',
        '#markup' => $this->t($this->formatAccountNumber($result->account_number)),
        '#theme_wrappers' => [],
      ];

      $form[$container]['status'] = [
        '#type' => 'item',
        '#markup' => ($result->status == 1)? $this->t('Approved'):$this->t('Not Approved'),
        '#theme_wrappers' => [],
      ];

      $form[$container]['default'] = [
        '#type' => 'item',
        '#markup' => ($result->default == 1)? $this->t('Primary'):'',
        '#theme_wrappers' => [],
      ];

      $form[$container]['more_details'] = [
        '#type' => 'item',
        '#markup' => $this->t('<a class="use-ajax text-light btn-link mx-2" data-toggle="modal" data-dialog-type="modal" href="@host/payyed/bank_account/view/bank_info/@rid" data-dialog-options="{&quot;width&quot;:620}"><span class="mr-1"><i class="fa fa-share mr-1"></i>More Details</a>', ['@rid' => $result->rid,'@host' => $host]),
      ];

      $form[$container]['delete_card'] = [
        '#type' => 'item',
        '#markup' => $this->t('<a class="use-ajax text-light btn-link mx-2" data-toggle="modal" data-dialog-type="modal" href="@host/payyed/bank_account/delete/bank/@rid" data-dialog-options="{&quot;width&quot;:350}"><span class="mr-1"><i class="fa fa-minus-circle"></i></span>Delete</a>', ['@rid' => $result->rid,'@host' => $host]),
      ];

      $counter++;
    }

    $form['add_bank'] = [
      '#type' => 'link',
      '#title' => $this->t('Add New Bank Account'),
      '#url' => Url::fromUri($host.'/payyed/view/bank_account/AddBankForm'),
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
    return 'view_bank';
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
    return ['config.view_bank'];
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
   * View bank details
   * @return mixed
   */
  public function selectData() {
    $database = \Drupal::database();
    $query = $database->select('bank_account_banks', 'ab');
    $query->fields('ab', ['rid','uid','type','bank_country','bank_name','account_name','account_number','sort_code','confirmed','status','default']);
    $query->condition('ab.uid', $this->getUserId());
    $query->orderBy('default', 'DESC');

    return $query->execute()->fetchAll();
  }

  /**
   * Format account number
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
