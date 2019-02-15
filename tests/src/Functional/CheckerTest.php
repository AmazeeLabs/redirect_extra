<?php

namespace Drupal\Tests\redirect_extra\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Integration test for the checker.
 *
 * @group redirect_extra
 */
class CheckerTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['redirect_extra'];

  /**
   * A user with permission to administer redirect settings.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(['administer redirect settings']);
    $this->drupalLogin($this->user);

    // Go to the overview and click the settings link.
    $this->drupalGet('admin/config/search/redirect');
    $this->assertSession()->linkExists('Redirect Extra settings');
    $this->clickLink('Redirect Extra settings');
  }

  /**
   * Tests the configuration fields
   */
  public function testConfigurationFields() {
    // @todo implement.
  }

  /**
   * Tests the internal path validation.
   *
   * @dataProvider internalPathDataProvider
   */
  public function testInternalPathRedirect() {
    // @todo implement.
  }

  /**
   * Tests the external path validation.
   */
  public function testExternalPathRedirect() {
    // @todo implement.
  }

  /**
   * Tests redirect chains.
   */
  public function testChainRedirect() {
    // @todo implement.
  }

  /**
   * Data provider for testInternalPath().
   *
   * @return array
   */
  public function internalPathDataProvider() {
    return [
      '/',
      'internal:/admin/content',
      'entity:node/1',
      'entity:user/1',
      // @todo add redirect.
    ];
  }

}
