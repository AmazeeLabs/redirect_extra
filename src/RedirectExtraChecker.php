<?php

namespace Drupal\redirect_extra;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use GuzzleHttp\Exception\RequestException;

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
   * Drupal\Core\Path\PathValidatorInterface definition.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Constructs a new RedirectExtraChecker object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, PathValidatorInterface $path_validator) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->pathValidator = $path_validator;
  }

  /**
   * Checks if a redirect path is 'accessible'.
   *
   * The path to check can be internal (valid Drupal path)
   * or external (valid http status code).
   *
   * @param string $redirect
   *
   * @return bool
   */
  public function isAccessible($redirect) {
    $result = TRUE;

    $redirectExtraSettings = $this->configFactory->get('redirect_extra.settings');
    $checkInternal = $redirectExtraSettings->get('404_path')['internal'] === 'internal';
    $checkExternal = $redirectExtraSettings->get('404_path')['external'] === 'external';

    // Bypass any check
    if (!$checkInternal && !$checkExternal) {
      return $result;
    }

    $validHttpStatus = [200, 301, 302]; // @todo set in config.

    // External url check if enabled.
    if (UrlHelper::isExternal($redirect) && $checkExternal) {
      // Check status code if url is valid.
      if (UrlHelper::isValid($redirect, TRUE)) {
        $httpStatus = $this->getHttpStatus($redirect);
        $result = in_array($httpStatus, $validHttpStatus);
      }
      // Invalid url.
      else {
        $result = FALSE;
      }
    }
    // Internal path check if enabled.
    // Possible values for $redirect: internal:/test, entity:node/1, ...
    elseif ($checkInternal) {
      // Interestingly, a redirect is not a valid internal path for
      // the pathValidator, so using UrlHelper.
      // @todo check if it generates a valid status code.
      $url = Url::fromUri($redirect)->setAbsolute()->toString();
      // The front page should be included as valid.
      // $result = $url === '/' || $this->pathValidator->isValid($url);
      $result = UrlHelper::isValid($url);
    }

    return $result;
  }

  /**
   * Gets the http status code for an uri.
   *
   * @param string $uri
   *
   * @return int
   *   Http status code.
   */
  public function getHttpStatus($uri) {
    $result = 0;
    $client = \Drupal::httpClient();
    try {
      $request = $client->get($uri, []);
      if ($response = $request->getStatusCode()) {
        $result = $response;
      }
    }
    catch (RequestException $exception) {
      \Drupal::messenger()->addError($exception->getMessage());
    }
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
