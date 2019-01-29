<?php

namespace Drupal\redirect_extra;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class RedirectExtra.
 */
class RedirectExtraForm {

  use StringTranslationTrait;

  const OPERATION_CREATE = 'create';
  const OPERATION_EDIT = 'edit';

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\redirect_extra\RedirectExtraChecker definition.
   *
   * @var \Drupal\redirect_extra\RedirectExtraChecker
   */
  protected $checker;

  /**
   * Constructs a new RedirectExtra object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RedirectExtraChecker $checker) {
    $this->configFactory = $config_factory;
    $this->checker = $checker;
  }


  /**
   * Helper to alter the redirect create and edit form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $operation
   *   'create' or 'edit'
   */
  public function formAlter(array &$form, FormStateInterface $form_state, $operation) {
    $redirectExtraSettings = $this->configFactory->get('redirect_extra.settings');
    // Limit the available redirect status if the permissions are enabled.
    if ((int) $redirectExtraSettings->get('status_permissions_enable') === 1) {
      $user = \Drupal::currentUser();
      // Equivalent to redirect_status_code_options().
      $status_codes = array_keys($form['status_code']['#options']);

      $redirectSettings = $this->configFactory->get('redirect.settings');
      $default_status_code = (int) $redirectSettings->get('default_status_code');

      foreach ($status_codes as $code) {
        // Unset the default status code if the user does not have the
        // permission to use the default one.
        // @todo handle exception during edition if the default status code has been changed.
        if (
          $code === $default_status_code &&
          !$user->hasPermission('create '. $code .' redirect') &&
          !$user->hasPermission('use default status code')
        ) {
          unset($form['status_code']['#options'][$code]);
        }
        // Unset all other status codes based on the dynamic permissions.
        elseif (
          $code !== $default_status_code &&
          !$user->hasPermission('create '. $code .' redirect')
        ) {
          unset($form['status_code']['#options'][$code]);
        }
      }

      // This case probably comes from a configuration error,
      // because it does not make sense to set the permission
      // 'administer redirects' without any redirect code.
      if (empty($form['status_code']['#options'])) {
        \Drupal::messenger()->addError($this->t('You do not have access to any redirect status.'));
      }
    }

    // @todo replace by constraints.
    $form['#validate'][] = '\Drupal\redirect_extra\RedirectExtraForm::formValidate';
  }

  /**
   * Custom validation callback for the Redirect create or edit form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function formValidate(array &$form, FormStateInterface $form_state) {
    $redirectExtraSettings = \Drupal::configFactory()->get('redirect_extra.settings');

    // Check if validation is required for forms.
    $validate404 = $redirectExtraSettings->get('404_enable') === 1 &&
      $redirectExtraSettings->get('404_scope')['form'] === 'form';
    $validateChain = $redirectExtraSettings->get('chain_enable') === 1  &&
      $redirectExtraSettings->get('chain_scope')['form'] === 'form';

    if (!$validate404 && !$validateChain) {
      return;
    }

    /** @var \Drupal\redirect_extra\RedirectExtraChecker $checker */
    $checker = \Drupal::service('redirect_extra.checker');
    $messenger = \Drupal::messenger();
    $source = $form_state->getValue('redirect_source')[0]['path'];
    $redirect = $form_state->getValue('redirect_redirect')[0]['uri'];

    // Check 404.
    if ($validate404 && !$checker->isAccessible($redirect)) {
      $message = t('The redirect path @redirect is not accessible.', [
        '@redirect' => $redirect,
      ]);
      if ($redirectExtraSettings->get('404_behavior') === 'warning') {
        $messenger->addWarning($message);
      }
      else {
        $form_state->setErrorByName('redirect_redirect', $message);
      }
    }

    // Check chain.
    if ($validateChain && $checker->isChain($source, $redirect)) {
      if ($redirectExtraSettings->get('chain_behavior')['warning'] === 'warning') {
        $message = t('The redirect @redirect is a chain.', [
          '@redirect' => $redirect,
        ]);
        $messenger->addWarning($message);
      }
      if (
        $redirectExtraSettings->get('chain_behavior')['convert'] === 'convert' &&
        $checker->unchain($source, $redirect)
      ) {
        $message = t('The redirect @redirect has been converted.', [
          '@redirect' => $redirect,
        ]);
        $messenger->addStatus($message);
      }

    }
  }

}
