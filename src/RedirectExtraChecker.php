<?php

namespace Drupal\redirect_extra;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\redirect\RedirectRepository;
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
   * Drupal\redirect\RedirectRepository definition.
   *
   * @var \Drupal\redirect\RedirectRepository
   */
  protected $redirectRepository;

  /**
   * Constructs a new RedirectExtraChecker object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_type_manager,
    PathValidatorInterface $path_validator,
    RedirectRepository $redirect_repository
  ) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->pathValidator = $path_validator;
    $this->redirectRepository = $redirect_repository;
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

    // @todo check if the settings are defined or pass default params
    //   to allow usage of this service without the configuration.
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
      $url = Url::fromUri($redirect)->toString();
      // Simply skip the test if this is a redirect
      // as it is not regarded as a valid internal path
      // and the process of unchaining is not handled here.
      if (!empty($this->getOriginalRedirect($redirect))) {
        $result = TRUE;
      }
      else {
        $result = $this->pathValidator->getUrlIfValidWithoutAccessCheck($url);
      }
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
   * @param string $redirect
   *   Example internal:/test_redirect/1
   *
   * @return bool
   */
  public function isChain($redirect) {
    return !empty($this->getOriginalRedirect($redirect));
  }

  /**
   * Returns a potential array of Redirect entity.
   *
   * Helper to detect and unchain redirect chains.
   *
   * @param string $redirect
   *
   * @return array|\Drupal\redirect\Entity\Redirect[]
   */
  private function getOriginalRedirect($redirect) {
    $result = [];
    // Check if the redirect is internal, external redirects or other uri forms
    // like entity:node/1 cannot lead to redirect chains.
    if (strpos($redirect, 'internal:/') === 0) {
      // If the redirect uri already exists in the source, it is a redirect.
      $sourceCandidate = str_replace('internal:/', '', $redirect);
      $result = $this->redirectRepository->findBySourcePath($sourceCandidate);
    }
    return $result;
  }

  /**
   * Gets the original redirect uri.
   *
   * If the $redirect path is another $redirect, find the original
   * one and use it to avoid redirect chains.
   *
   * Example:
   * Existing source: /redirect/1 -> Existing redirect: /node/1
   * $source: /redirect/2 -> $redirect: /redirect/1
   * Replace $redirect by /node/1
   *
   * @param string $redirect
   *
   * @return string
   */
  public function getOriginalRedirectUri($redirect) {
    $result = $redirect;
    // Get the Redirect entity that matches the same source and get
    // its own redirect uri.
    // Then set it as a replacement of the desired redirect.
    $originalRedirect = $this->getOriginalRedirect($redirect);
    if (!empty($originalRedirect)) {
      /** @var \Drupal\redirect\Entity\Redirect $redirectEntity */
      $redirectEntity = reset($originalRedirect);
      if (!empty($redirectEntity->get('redirect_redirect')->getValue())) {
        $result = $redirectEntity->get('redirect_redirect')->getValue()[0]['uri'];
      }
    }
    // Note that several chains can happen if the 'prevent redirect chains'
    // feature is enabled on existing chains.
    // So a recursive unchain method could be implemented.
    // Example:
    // /redirect/1 --> /node/1
    // /redirect/2 --> /redirect/1
    // /redirect/3 --> /redirect/2
    // In this case we need several passes to unchain all the redirects.
    return $result;
  }

}
