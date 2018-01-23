<?php

namespace Drupal\Tests\protect_before_launch\Browser;

use Drupal\protect_before_launch\Configuration;
use Drupal\Tests\BrowserTestBase;

/**
 * Class AuthenticateTest.
 *
 * @group tests
 * @package Drupal\Tests\protect_before_launch\Browser
 */
class AdminFormTest extends BrowserTestBase {

  protected static $modules = ['protect_before_launch'];

  protected function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(['administer modules', 'administer software updates', 'administer site configuration']);
    $this->drupalLogin($admin_user);
  }

  public function testViewForm() {
    $output = $this->drupalGet('admin/config/protect_before_launch/settings');

    $this->assertContains('Status', $output);
    $this->assertContains('Username', $output);
    $this->assertContains('Password', $output);
    $this->assertContains('Confirm password', $output);
    $this->assertContains('Authentication', $output);
    $this->assertContains('Realm', $output);
    $this->assertContains('Exclude Paths', $output);
    $this->assertContains('Environment Key', $output);
    $this->assertContains('Environment Value', $output);

    $this->assertSession()->fieldValueEquals('protect', Configuration::CONFIG_DISABLED);
    $this->assertSession()->fieldValueEquals('username', 'username');
    $this->assertSession()->fieldValueEquals('content', 'Access Denied');
    $this->assertSession()->fieldValueEquals('realm', 'Protected Site');
    $this->assertSession()->fieldValueEquals('authentication_type', Configuration::CONFIG_AUTH_SIMPLE);
    $this->assertSession()->fieldValueEquals('exclude_paths', '');
    $this->assertSession()->fieldValueEquals('environment_key', 'AH_NON_PRODUCTION');
    $this->assertSession()->fieldValueEquals('environment_value', '');
  }

  public function testUpdateForm() {
    $this->drupalGet('admin/config/protect_before_launch/settings');
    $this->submitForm([
      'username' => 'my_username',
      'realm' => 'My Realm',
      'content' => 'My Protected Content',
      'environment_key' => 'MY_KEY',
      'environment_value' => 'MY_VALUE'
    ], 'Save Configuration');

    $output = $this->drupalGet('admin/config/protect_before_launch/settings');
    $this->assertContains('Username', $output);
    $this->assertSession()->fieldValueEquals('protect', Configuration::CONFIG_DISABLED);
    $this->assertSession()->fieldValueEquals('username', 'my_username');
    $this->assertSession()->fieldValueEquals('content', 'My Protected Content');
    $this->assertSession()->fieldValueEquals('realm', 'My Realm');
    $this->assertSession()->fieldValueEquals('authentication_type', Configuration::CONFIG_AUTH_SIMPLE);
    $this->assertSession()->fieldValueEquals('exclude_paths', '');
    $this->assertSession()->fieldValueEquals('environment_key', 'MY_KEY');
    $this->assertSession()->fieldValueEquals('environment_value', 'MY_VALUE');
  }

  public function testEnable() {
    $this->drupalGet('admin/config/protect_before_launch/settings');
    $this->submitForm([
      'protect' => Configuration::CONFIG_ENABLED,
    ], 'Save Configuration');

    $output = $this->drupalGet('admin/config/protect_before_launch/settings');
    $this->assertEquals('Access Denied', $output);

    $output = $this->drupalGet('admin/config/protect_before_launch/settings', [], [
      'PHP_AUTH_USER' => 'username',
      'PHP_AUTH_PW' => 'password'
    ]);
    $this->assertContains('Username', $output);
  }
}