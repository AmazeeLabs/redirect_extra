<?php

namespace Drupal\redirect_extra;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class RedirectExtraChecker.
 */
class RedirectExtraChecker {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new RedirectExtraChecker object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks if a redirect path is valid.
   *
   * The path to check can be internal or external.
   *
   * @param string $redirect
   *
   * @return bool
   */
  public function isValidPath($redirect) {
    // @todo implement
    $result = TRUE;
    return $result;
  }

  /**
   * Checks if a redirect is a chain.
   *
   * @param string $source
   * @param string $redirect
   *
   * @return bool
   */
  public function isChain($source, $redirect) {
    // @todo implement
    $result = FALSE;
    return $result;
  }

  /**
   * Converts a redirect chain.
   *
   * If the $redirect path is another $redirect, find the original
   * one and use it to avoid redirect chains.
   *
   * @param $source
   * @param $redirect
   *
   * @return bool
   */
  public function unchain($source, $redirect) {
    // @todo implement
    // @see RedirectRepository::findMatchingRedirect
    $result = FALSE; // unchain result
    return $result;
  }

}
