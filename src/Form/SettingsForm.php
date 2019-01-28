<?php

namespace Drupal\redirect_extra\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'redirect_extra.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'redirect_extra_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('redirect_extra.settings');
    // @todo add conditional required
    $form['404'] = [
      '#type' => 'details',
      '#title' => t('404 validation'),
      '#open' => TRUE,
    ];
    $form['404']['404_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#description' => $this->t('Enable 404 validation.'),
      '#default_value' => $config->get('404_enable'),
    ];
    $form['404']['404_behavior'] = [
      '#type' => 'radios',
      '#title' => $this->t('404 behavior'),
      '#description' => $this->t('Performs extra validation on the <em>to</em> path while creating or editing a redirect.'),
      '#options' => ['warning' => $this->t('Warning'), 'error' => $this->t('Error')],
      '#default_value' => $config->get('404_behavior'),
      '#states' => [
        'visible' => [
          ':input[name="404_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['404']['404_path'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('404 path'),
      '#description' => $this->t('Check internal and/or external paths.'),
      '#options' => ['internal' => $this->t('Internal'), 'external' => $this->t('External')],
      '#default_value' => $config->get('404_path'),
      '#states' => [
        'visible' => [
          ':input[name="404_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['404']['404_scope'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('404 scope'),
      '#description' => $this->t('Apply validation for the form and/or the API.'),
      '#options' => ['form' => $this->t('Form'), 'api' => $this->t('API')],
      '#default_value' => $config->get('404_scope'),
      '#states' => [
        'visible' => [
          ':input[name="404_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['chain'] = [
      '#type' => 'details',
      '#title' => t('Chain validation'),
      '#open' => TRUE,
    ];
    $form['chain']['chain_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable'),
      '#description' => $this->t('Enable redirect chains configuration.'),
      '#default_value' => $config->get('404_enable'),
    ];
    $form['chain']['chain_behavior'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Chain behavior'),
      '#description' => $this->t('Warn and/or convert redirect to the original destination when a redirect chain is detected.'),
      '#options' => ['warning' => $this->t('Warning'), 'convert' => $this->t('Convert')],
      '#default_value' => $config->get('chain_behavior'),
      '#states' => [
        'visible' => [
          ':input[name="chain_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['chain']['chain_scope'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Chain scope'),
      '#description' => $this->t('Apply validation for the form and/or the API'),
      '#options' => ['form' => $this->t('Form'), 'api' => $this->t('API')],
      '#default_value' => $config->get('chain_scope'),
      '#states' => [
        'visible' => [
          ':input[name="chain_enable"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['status_permissions_enable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Status permissions'),
      '#description' => $this->t('Enable permissions for redirect status.'),
      '#default_value' => $config->get('status_permissions_enable'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('redirect_extra.settings')
      ->set('404_enable', $form_state->getValue('404_enable'))
      ->set('404_behavior', $form_state->getValue('404_behavior'))
      ->set('404_path', $form_state->getValue('404_path'))
      ->set('404_scope', $form_state->getValue('404_scope'))
      ->set('chain_enable', $form_state->getValue('chain_enable'))
      ->set('chain_behavior', $form_state->getValue('chain_behavior'))
      ->set('chain_scope', $form_state->getValue('chain_scope'))
      ->set('status_permissions_enable', $form_state->getValue('status_permissions_enable'))
      ->save();
  }

}

