<?php
/**
 * @package   AkeebaSocialLogin
 * @copyright Copyright (c)2016-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Joomla\Plugin\Sociallogin\Paypal\Extension;

defined('_JEXEC') || die();

use Exception;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Sociallogin\Paypal\Integration\OAuth as PaypalOAuth;
use Joomla\Plugin\Sociallogin\Paypal\Integration\UserQuery;
use Joomla\Plugin\System\SocialLogin\Library\Data\UserData;
use Joomla\Plugin\System\SocialLogin\Library\Plugin\AbstractPlugin;
use Joomla\Registry\Registry;

if (!class_exists(AbstractPlugin::class, true)) {
  return;
}

class Plugin extends AbstractPlugin {

  public static function getSubscribedEvents(): array {
      return array_merge(parent::getSubscribedEvents(), ['onAjaxPaypal' => 'onSocialLoginAjax']);
    }

    public function init(): void {
        $this->fgColor = '';
        $this->bgColor = '';
        parent::init();
        $this->buttonImage = 'plg_sociallogin_paypal/paypal.png';
      }

      protected function getConnector(): PaypalOAuth {
          if (is_null($this->connector)) {
            $options = ['clientid' => $this->appId, 'clientsecret' => $this->appSecret, 'redirecturi' => Uri::base() . 'index.php?option=com_ajax&group=sociallogin&plugin=' . $this->integrationName . '&format=raw', 'requestparams' => ['state' => $this->getApplication()->getSession()->getToken()]];
            $httpClient = HttpFactory::getHttp();
            $this->connector = new PaypalOAuth($options, $httpClient, $this->getApplication()->input, $this->getApplication());
            $this->connector->setScope('openid profile email');
          }
          return $this->connector;
        }

        protected function getSocialNetworkProfileInformation(object $connector): array {
            $tokenArray = $connector->getToken();
            $options = new Registry(['userAgent' => 'Akeeba-Social-Login']);
            $client = HttpFactory::getHttp($options);
            $dUserQuery = new UserQuery($client, $tokenArray['access_token']);
            $dUserFields = $dUserQuery->getUserInformation();
            return (array)$dUserFields;
          }

          protected function mapSocialProfileToUserData(array $socialProfile): UserData {
              $userData = new UserData();
              $userData->name = $socialProfile['name'] ?? '';
              $userData->id = str_replace('https://www.paypal.com/webapps/auth/identity/user/', null, $socialProfile['user_id']) ?? '';
              $userData->email = $socialProfile['emails']->value ?? '';
              $userData->verified = $socialProfile['emails']->confirmed ?? false;
              return $userData;
            }

          }