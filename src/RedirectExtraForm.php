<?php

namespace Drupal\redirect_extra;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class RedirectExtra.
 */
class RedirectExtraForm {

  const OPERATION_CREATE = 'create';
  const OPERATION_EDIT = 'edit';

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new RedirectExtra object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
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
        \Drupal::messenger()->addError(t('You do not have access to any redirect status.'));
      }
    }
  }

}
