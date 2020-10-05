<?php
namespace Drupal\bank_account\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 *
 * ExampleForm class.
 */
class DeleteCardForm extends FormBase
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

    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('Delete Card?'),
      '#theme_wrappers' => [],
    ];

    $form['yes'] = [
      '#type' => 'submit',
      '#value' => $this->t('Yes'),
      '#attributes' => [
        'class' => ['payyed-btn', 'w-100', 'btn', 'btn-block', 'mt-2', 'font-weight-500', 'font-size-16', 'payyed-font'],
      ],
    ];

    $form['no'] = [
      '#type' => 'submit',
      '#value' => $this->t('No'),
      '#submit' => ['::cancelDelete'],
      '#attributes' => [
        'class' => ['payyed-btn', 'w-100', 'btn', 'btn-block', 'mt-2', 'font-weight-500', 'font-size-16', 'no-delete'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->deleteData();

    // Redirect to same page
    $url = Url::fromRoute('bank_account_controller_page');
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function cancelDelete(array &$form, FormStateInterface $form_state)
  {
    // Redirect to same page
    $url = Url::fromRoute('bank_account_controller_page');
    $form_state->setRedirectUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'delete_card';
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
    return ['config.delete_card'];
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
   * Delete bank card
   */
  public function deleteData() {
    $query = \Drupal::database()->delete('bank_account_cards');
    $query->condition('uid', $this->getUserId());
    $query->condition('rid', $this->rid);
    $query->execute();
  }

}
