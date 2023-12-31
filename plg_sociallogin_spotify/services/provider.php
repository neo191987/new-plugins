<?php
/**
 *  @package   AkeebaSocialLogin
 *  @copyright Copyright (c)2016-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class implements ServiceProviderInterface {

  public function register(Container $container) {
    $container->set(PluginInterface::class, function (Container $container) {
      $subject = $container->get(DispatcherInterface::class);
      $config = (array)PluginHelper::getPlugin('sociallogin', 'spotify');
      $plugin = new Joomla\Plugin\Sociallogin\Spotify\Extension\Plugin($subject, $config);
      $plugin->setApplication(\Joomla\CMS\Factory::getApplication());
      $plugin->setDatabase($container->get(DatabaseInterface::class));
      $plugin->init();
      return $plugin;
    });
  }

};