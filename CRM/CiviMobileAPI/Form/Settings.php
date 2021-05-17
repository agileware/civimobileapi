<?php

use CRM_CiviMobileAPI_ExtensionUtil as E;

class CRM_CiviMobileAPI_Form_Settings extends CRM_Core_Form {

  public function preProcess() {
    parent::preProcess();

    $pushNotificationMessage = E::ts('To use Push Notifications you must register at <a href="https://civimobile.org/partner"  target="_blank">civimobile.org</a> and generate your own Server Key');
    $version = CRM_CiviMobileAPI_Utils_VersionController::getInstance();
    $latestCivicrmMessage = FALSE;
    $oldCivicrmMessage = FALSE;
    $serverKeyValidMessage = FALSE;
    $folderPermissionMessage = FALSE;
    $serverKeyInValidMessage = FALSE;
    $currentExtensionName = CRM_CiviMobileAPI_Utils_Extension::getCurrentExtensionName();
    $currentExtensionPath = CRM_CiviMobileAPI_Utils_Extension::getCurrentExtensionPath();
    $isCorrectExtensionName = CRM_CiviMobileAPI_Utils_Extension::isCorrectExtensionName();

    if ($version->isCurrentVersionLowerThanRepositoryVersion()) {
      $oldCivicrmMessage = E::ts('You are using CiviMobileAPI <strong>%1</strong>. The latest version is CiviMobileAPI <strong>%2</strong>', [
        1 => 'v' . $version->getCurrentFullVersion(),
        2 => 'v' . $version->getLatestFullVersion(),
      ]);
    } else {
      $latestCivicrmMessage = E::ts('Your extension version is up to date - CiviMobile <strong>%1</strong>', [1 => 'v' . $version->getCurrentFullVersion()]);
    }

    if (!CRM_CiviMobileAPI_Utils_Extension::directoryIsWritable()) {
      $folderPermissionMessage = '<strong>' . E::ts('Access to extension directory with all files or for directory with extensions is denied. Please provide permission to access the extension directory and directory with extensions.') . '</strong> ';
    }

    if (Civi::settings()->get('civimobile_is_server_key_valid') == 1) {
      $serverKeyValidMessage = E::ts('Your Server Key is valid and you can use Push Notifications.');
    } else {
      $serverKeyInValidMessage =  E::ts('Your Server Key is invalid. Please enter valid Server Key.');
    }

    $enabledComponents = CRM_CiviMobileAPI_Utils_CiviCRM::getEnabledComponents();
    $possibleItemsToDisplayInPublicArea = [];

    if (Civi::settings()->get('civimobile_is_showed_news')) {
      $possibleItemsToDisplayInPublicArea[] = 'News';
    }
    if (in_array('CiviEvent', $enabledComponents)) {
      $possibleItemsToDisplayInPublicArea[] = 'Events';
    }
    if (in_array('CiviCampaign', $enabledComponents)) {
      $possibleItemsToDisplayInPublicArea[] = 'Petitions';
    }

    $this->assign('isWritable', CRM_CiviMobileAPI_Utils_Extension::directoryIsWritable());
    $this->assign('serverKeyValidMessage', $serverKeyValidMessage);
    $this->assign('serverKeyInValidMessage', $serverKeyInValidMessage);
    $this->assign('pushNotificationMessage', $pushNotificationMessage);
    $this->assign('latestCivicrmMessage', $latestCivicrmMessage);
    $this->assign('oldCivicrmMessage', $oldCivicrmMessage);
    $this->assign('folderPermissionMessage', $folderPermissionMessage);
    $this->assign('currentExtensionName', $currentExtensionName);
    $this->assign('currentExtensionPath', $currentExtensionPath);
    $this->assign('isCorrectExtensionName', $isCorrectExtensionName);
    $this->assign('correctExtensionName', CRM_CiviMobileAPI_ExtensionUtil::LONG_NAME);
    $this->assign('defaultRssFeedUrl', CRM_CiviMobileAPI_Utils_Cms::getCmsRssUrl());
    $this->assign('possibleItemsToDisplayInPublicArea', implode(', ', $possibleItemsToDisplayInPublicArea));

    CRM_Core_Resources::singleton()->addStyleFile('com.agiliway.civimobileapi', 'css/civimobileapiSettings.css', 200, 'html-header');
  }

  /**
   * AddRules hook
   */
  public function addRules() {
    $params = $this->exportValues();

    if (!empty($params['_qf_Settings_submit'])) {
      $this->addFormRule([CRM_CiviMobileAPI_Form_Settings::class, 'validateToken']);
    } elseif (!empty($params['_qf_Settings_upload'])) {
      $this->addFormRule([CRM_CiviMobileAPI_Form_Settings::class, 'validateNewsSettings']);
      $this->addFormRule([CRM_CiviMobileAPI_Form_Settings::class, 'validatePushNotificationSettings']);
    }
  }

  /**
   * Validate news settings
   * Uses on form validation
   *
   * @param $values
   * @return array|bool
   */
  public static function validateNewsSettings($values) {
    $errors = [];
    if (isset($values['civimobile_is_showed_news'])
      && $values['civimobile_is_showed_news'] == 1
      && empty($values['civimobile_news_rss_feed_url'])
    ) {
      $errors['civimobile_news_rss_feed_url'] = E::ts('Field can not be empty.');
    }

    return empty($errors) ? TRUE : $errors;
  }

  public static function validatePushNotificationSettings($values) {
    if (!is_numeric($values["civimobile_push_notification_lifetime"])) {
      $errors["civimobile_push_notification_lifetime"] = E::ts('Value must be integer!');
    }
    if ($values["civimobile_push_notification_lifetime"] < 0) {
      $errors["civimobile_push_notification_lifetime"] = E::ts('Value cannot be negative!');
    }
    if ($values["civimobile_push_notification_lifetime"] > 90) {
      $errors["civimobile_push_notification_lifetime"] = E::ts('Value cannot be greater than 90!');
    }
    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Validates token
   *
   * @param $values
   *
   * @return array
   */
  public static function validateToken($values) {
    $errors = [];
    $tokenFieldName = 'civimobile_server_key';

    if (empty($values[$tokenFieldName]) || empty(trim($values[$tokenFieldName]))) {
      $errors[$tokenFieldName] = E::ts('Fields can not be empty.');
      return empty($errors) ? TRUE : $errors;
    }

    $token = trim($values[$tokenFieldName]);

    try {
      $result = civicrm_api3('CiviMobileConfirmToken', 'run', ['civicrm_server_token' => $token]);
    } catch (CiviCRM_API3_Exception $e) {
      $errors[$tokenFieldName] = E::ts('Error. Something went wrong. Please contact us.');
    }

    if (!empty($result['values']['response']) ) {
      if ($result['values']['response']['error'] == 1) {
        $errors[$tokenFieldName] = E::ts($result['values']['response']['message']);
      } else {
        Civi::settings()->set('civimobile_is_server_key_valid', 1);
      }
    }

    if (!empty($errors)) {
      Civi::settings()->set('civimobile_is_server_key_valid', 0);
    }

    return empty($errors) ? TRUE : $errors;
  }

  /**
   * Build the form object
   *
   * @throws \HTML_QuickForm_Error
   */
  public function buildQuickForm() {
    parent::buildQuickForm();

    $this->addElement('text', 'civimobile_server_key', E::ts('Server key'));
    $this->addElement('checkbox', 'civimobile_auto_update', E::ts('Automatically keep the extension up to date'));
    $this->addElement('checkbox', 'civimobile_is_allow_public_website_url_qrcode', E::ts('Show a Website URL QR-code for Anonymous users'));
    $this->addElement('radio', 'civimobile_site_name_to_use', NULL, E::ts('Use CMS site name'), 'cms_site_name');
    $this->addElement('radio', 'civimobile_site_name_to_use', NULL, E::ts('Use custom site name'), 'custom_site_name');
    $this->addElement('text', 'civimobile_custom_site_name', E::ts('Site name'));
    $this->addElement('checkbox', 'civimobile_is_allow_public_info_api', E::ts('Show Public area'));
    $this->addElement('checkbox', 'civimobile_is_showed_news', E::ts('Show News'));
    $this->addElement('text', 'civimobile_news_rss_feed_url', E::ts('News RSS feed URL'));
    $this->addElement('text', 'civimobile_firebase_key', E::ts('Firebase key'));
    $this->addElement('checkbox', 'civimobile_is_custom_app', E::ts('Do you have custom application?'));
    $this->addElement('text', 'civimobile_push_notification_lifetime', E::ts('Life time for push notification messages'));

    $buttons = [
      [
        'type' => 'upload',
        'name' => E::ts('Save settings'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'submit',
        'name' => E::ts('Confirm server key'),
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
      ]
    ];

    if (CRM_CiviMobileAPI_Utils_VersionController::getInstance()->isCurrentVersionLowerThanRepositoryVersion()
      && !empty(CRM_CiviMobileAPI_Utils_Extension::directoryIsWritable())) {
      $buttons[] = [
        'type' => 'next',
        'name' => E::ts('Update CiviMobile Extension'),
      ];
    }

    $this->addButtons($buttons);
  }

  public function postProcess() {
    $params = $this->exportValues();

    if (!empty($params['_qf_Settings_submit'])) {
      if (empty($params['_qf_Settings_next'])) {
        Civi::settings()->set('civimobile_server_key', $params['civimobile_server_key']);
        CRM_Core_Session::singleton()->setStatus(E::ts('Server key updated'), E::ts('CiviMobile Settings'), 'success');
      }
    }
    elseif (!empty($params['_qf_Settings_next'])) {
      try {
        if (CRM_CiviMobileAPI_Utils_VersionController::getInstance()->isCurrentVersionLowerThanRepositoryVersion()) {
          $this->controller->setDestination(CRM_Utils_System::url('civicrm/civimobile/settings', http_build_query([])));
          CRM_CiviMobileAPI_Utils_Extension::update();

          CRM_Core_Session::singleton()->setStatus(E::ts('CiviMobile updated'), E::ts('CiviMobile Settings'), 'success');
        }
      }
      catch (Exception $e) {
        CRM_Core_Session::setStatus($e->getMessage());
      }
    }
    elseif (!empty($params['_qf_Settings_upload'])) {
      $this->controller->setDestination(CRM_Utils_System::url('civicrm/civimobile/settings', http_build_query([])));
      if (!empty($params['civimobile_auto_update'])) {
        Civi::settings()->set('civimobile_auto_update', 1);
      }
      else {
        Civi::settings()->set('civimobile_auto_update', 0);
      }
      if (!isset($params['civimobile_is_allow_public_website_url_qrcode'])) {
        $params['civimobile_is_allow_public_website_url_qrcode'] = 0;
      }
      if (!isset($params['civimobile_custom_site_name'])) {
        $params['civimobile_custom_site_name'] = '';
      }
      if(!isset($params['civimobile_is_showed_news'])) {
        $params['civimobile_is_showed_news'] = 0;
      }
      if (!isset($params['civimobile_is_custom_app'])) {
        $params['civimobile_is_custom_app'] = 0;
      }
      if (!isset($params['civimobile_is_allow_public_info_api'])) {
        $params['civimobile_is_allow_public_info_api'] = 0;
      }
      if (!isset($params['civimobile_push_notification_lifetime'])) {
        $params['civimobile_push_notification_lifetime'] = CRM_CiviMobileAPI_BAO_PushNotificationMessages::LIFE_TIME_IN_DAYS;
      }

      Civi::settings()->set('civimobile_is_custom_app', $params['civimobile_is_custom_app']);
      Civi::settings()->set('civimobile_firebase_key', $params['civimobile_firebase_key']);
      Civi::settings()->set('civimobile_is_allow_public_website_url_qrcode', $params['civimobile_is_allow_public_website_url_qrcode']);
      Civi::settings()->set('civimobile_site_name_to_use', $params['civimobile_site_name_to_use']);
      Civi::settings()->set('civimobile_custom_site_name', $params['civimobile_custom_site_name']);
      Civi::settings()->set('civimobile_is_allow_public_info_api', $params['civimobile_is_allow_public_info_api']);
      Civi::settings()->set('civimobile_is_showed_news', $params['civimobile_is_showed_news']);
      Civi::settings()->set('civimobile_news_rss_feed_url', $params['civimobile_news_rss_feed_url']);
      Civi::settings()->set("civimobile_push_notification_lifetime", $params['civimobile_push_notification_lifetime']);
      CRM_Core_Session::singleton()->setStatus(E::ts('CiviMobile settings updated'), E::ts('CiviMobile Settings'), 'success');
    }
  }

  /**
   * Set defaults for form.
   */
  public function setDefaultValues() {
    $defaults = [];
    $pushNotificationLifetime = Civi::settings()->get('civimobile_push_notification_lifetime');

    $defaults['civimobile_auto_update'] = Civi::settings()->get('civimobile_auto_update');
    $defaults['civimobile_server_key'] = Civi::settings()->get('civimobile_server_key');
    $defaults['civimobile_is_allow_public_website_url_qrcode'] = CRM_CiviMobileAPI_Utils_Extension::isAllowPublicWebisteURLQRCode();
    $defaults['civimobile_site_name_to_use'] = (!empty(Civi::settings()->get('civimobile_site_name_to_use'))) ? Civi::settings()->get('civimobile_site_name_to_use') : 'cms_site_name' ;
    $defaults['civimobile_custom_site_name'] = Civi::settings()->get('civimobile_custom_site_name');
    $defaults['civimobile_is_allow_public_info_api'] = Civi::settings()->get('civimobile_is_allow_public_info_api');
    $defaults['civimobile_is_showed_news'] = Civi::settings()->get('civimobile_is_showed_news');
    $defaults['civimobile_news_rss_feed_url'] = CRM_CiviMobileAPI_Utils_Extension::newsRssFeedUrl();
    $defaults['civimobile_firebase_key'] = Civi::settings()->get('civimobile_firebase_key');
    $defaults['civimobile_is_custom_app'] = CRM_CiviMobileAPI_Utils_Extension::isCustomApp();
    $defaults['civimobile_push_notification_lifetime'] = isset($pushNotificationLifetime)
      ? (int) $pushNotificationLifetime : CRM_CiviMobileAPI_BAO_PushNotificationMessages::LIFE_TIME_IN_DAYS;

    return $defaults;
  }

}
