<?php

namespace Drupal\redirect_extra;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for the redirect_extra module.
 */
class RedirectExtraPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a RedirectExtraPermissions instance.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Returns an array of redirect status code permissions.
   *
   * @return array
   */
  public function statusPermissions() {
    $permissions = [];
    // @todo only if the status permissions are enabled.
    // Create a permission for each redirect status.
    $status_codes = redirect_status_code_options();
    foreach ($status_codes as $code => $markup) {
      $permissions["create $code redirect"] = [
        'title' => $this->t('Create @status_code redirect', [
          '@status_code' => $code,
        ]),
      ];
    }
    return $permissions;
  }

}
