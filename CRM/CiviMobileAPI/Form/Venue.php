<?php

class CRM_CiviMobileAPI_Form_Venue extends CRM_Core_Form {

  /**
   * Id of venue
   *
   * @var
   */
  private $id;

  /**
   * Location id of venue
   *
   * @var
   */
  private $location_id;

  /**
   * Build all the data structures needed to build the form.
   */
  public function preProcess() {
    parent::preProcess();
    $null = NULL;

    if (in_array($this->getAction(), [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE, CRM_Core_Action::DELETE])
      && !CRM_CiviMobileAPI_Utils_Permission::isEnoughPermissionForCreateEventVenues()) {
      CRM_Core_Error::statusBounce('You do not have all the permissions needed for this page.', '', ts('Permission Denied'));
    }

    $this->location_id = CRM_Utils_Request::retrieve('location_id', 'Positive');
    $this->assign('location_id', $this->location_id);
    if (empty($this->location_id)) {
      CRM_Core_Error::fatal(ts('Empty location id.'));
    }

    if (!in_array($this->location_id, array_keys(CRM_Event_BAO_Event::getLocationEvents()))) {
      CRM_Core_Error::fatal(ts('Wrong location id'));
    }

    if ($this->getAction() == CRM_Core_Action::UPDATE
      || $this->getAction() == CRM_Core_Action::DELETE
      || $this->getAction() == CRM_Core_Action::VIEW) {
      $this->id = CRM_Utils_Request::retrieve('id', 'Positive');

      if (empty($this->id) && !empty($this->_submitValues)) {
        $this->id = $this->_submitValues['id'];
      }

      if (empty($this->id)) {
        CRM_Core_Error::fatal(ts('Empty venue id.'));
      }

      if (empty(CRM_CiviMobileAPI_BAO_LocationVenue::getAll(['id' => $this->id]))) {
        CRM_Core_Error::fatal(ts('Venue id does not exist.'));
      }
    }
    if ($this->getAction() == CRM_Core_Action::VIEW) {
      $showDeleteButton = CRM_Utils_Request::retrieve('use_delete_button', 'Boolean', $null, FALSE, 1) && CRM_CiviMobileAPI_Utils_Permission::isEnoughPermissionForDeleteEventVenues();
      $this->assign('can_edit_venue', CRM_CiviMobileAPI_Utils_Permission::isEnoughPermissionForCreateEventVenues());
      $this->assign('can_delete_venue', $showDeleteButton);
    }


  }

  /**
   * Build the form object
   *
   * @throws \HTML_QuickForm_Error
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    $cancelButtonTittle = 'Cancel';

    $this->add('hidden', 'location_id', $this->location_id);
    $this->add('hidden', 'id', $this->id);
    $cancelURL = CRM_Utils_System::url('civicrm/civimobile/manage-venues', http_build_query([
      'reset' => '1',
      'location_id' => $this->location_id
    ]));

    $buttons = [];
    $isActive = [
      1 => 'Yes',
      0 => 'No',
    ];

    if ($this->getAction() == CRM_Core_Action::VIEW) {
      $this->setTitle('View venue');
      $cancelButtonTittle = 'Done';
    }
    if ($this->getAction() == CRM_Core_Action::UPDATE) {
      $this->setTitle('Edit venue');
    }
    if ($this->getAction() == CRM_Core_Action::ADD) {
      $this->setTitle('Add venue');
    }

    if ($this->getAction() == CRM_Core_Action::ADD
      || $this->getAction() == CRM_Core_Action::UPDATE
      || $this->getAction() == CRM_Core_Action::VIEW) {
      $this->add('text', 'venue_name', ts('Title'), ['class' => 'huge'], TRUE);
      $this->add('textarea', 'description', ts('Description'), ['class' => 'big']);
      $this->addRadio('is_active', ts('Is active'), $isActive);
      $this->add('text', 'address_description', ts('Details'), ['class' => 'huge']);
      $this->add('file', "attached_file", ts('Scheme'), ['accept' => 'image/jpeg,image/png,application/pdf']);
      $this->add('text', 'address', ts('Address'), ['class' => 'huge']);
      $this->add('number', 'weight', ts('Order'), [], TRUE);
    }
    if ($this->getAction() == CRM_Core_Action::ADD
      || $this->getAction() == CRM_Core_Action::UPDATE) {
      $this->add('hidden', 'id', $this->id);
      $buttons = [
        [
          'type' => 'submit',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ]
      ];
    }
    if ($this->getAction() == CRM_Core_Action::DELETE) {
      $buttons = [
        [
          'type' => 'submit',
          'name' => ts('Delete'),
          'isDefault' => TRUE,
        ]
      ];

      $this->setTitle('Delete venue');
    }
    if ($this->getAction() == CRM_Core_Action::VIEW || $this->getAction() == CRM_Core_Action::UPDATE) {
      try {
        $venue = civicrm_api3('CiviMobileVenue', 'getsingle', [
          'id' => $this->id
        ]);

        $this->assign('venue', $venue);
      } catch (Exception $e) {
        CRM_Core_Error::statusBounce('Invalid venueId parameter.', ts('Venue Not Found'));
      }
    }

    $buttons[] = [
      'type' => 'cancel',
      'name' => ts($cancelButtonTittle),
      'class' => 'cancel',
      'js' => ['onclick' => "
         if( CRM.$('.ui-dialog').length ) {
           var active = 'a.crm-popup';
           CRM.$('#crm-main-content-wrapper').on('crmPopupFormSuccess.crmLivePage', active, CRM.refreshParent, CRM.$('.ui-dialog-titlebar-close').trigger('click'));
         } else {
           window.location.href='{$cancelURL}'; return false;
         }"
      ],
    ];

    $this->addButtons($buttons);
  }

  /**
   * Process the form submission.
   */
  public function postProcess() {
    $inputValues = $this->exportValues();
    $url = CRM_Utils_System::url('civicrm/civimobile/manage-venues?reset=1&location_id=' . $inputValues['location_id']);

    $venueParams = [];

    if ($this->getAction() == CRM_Core_Action::ADD) {
      $venueParams = [
        'location_id' => $this->location_id,
        'name' => $inputValues['venue_name'],
        'is_active' => $inputValues['is_active'],
        'description' => $inputValues['description'],
        'address_description' => $inputValues['address_description'],
        'address' => $inputValues['address'],
        'weight' => $inputValues['weight'],
      ];
    }

    if ($this->getAction() == CRM_Core_Action::UPDATE) {
      $venueParams = [
        'id' => $this->id,
        'location_id' => $inputValues['location_id'],
        'name' => $inputValues['venue_name'],
        'is_active' => $inputValues['is_active'],
        'description' => $inputValues['description'],
        'address_description' => $inputValues['address_description'],
        'address' => $inputValues['address'],
        'weight' => $inputValues['weight'],
      ];
    }

    if ($this->getAction() == CRM_Core_Action::ADD || $this->getAction() == CRM_Core_Action::UPDATE) {
      if ($this->getAction() == CRM_Core_Action::ADD) {
        $venueParams['attached_file_url'] = self::generateFileURL();
        if (!empty($venueParams['attached_file_url'])) {
          $venueParams['attached_file_type'] = $_FILES['attached_file']['type'];
        }
      } elseif (!empty($_FILES['attached_file']['name'])) {
        CRM_CiviMobileAPI_Utils_Agenda_Venue::removeVenueAttach($venueParams['id']);
        $venueParams['attached_file_url'] = self::generateFileURL();
        if (!empty($venueParams['attached_file_url'])) {
          $venueParams['attached_file_type'] = $_FILES['attached_file']['type'];
        }
      }

      try {
        civicrm_api3('CiviMobileVenue', 'create', $venueParams);
      } catch (Exception $e) {
        CRM_Core_Session::setStatus($e->getMessage(), ts('Venue wasn`t saved'), "error");
      }
    }

    if ($this->getAction() == CRM_Core_Action::DELETE) {
      if (empty(CRM_CiviMobileAPI_BAO_EventSession::getAll(['venue_id' => $this->id]))) {
        civicrm_api3('CiviMobileVenue', 'delete', [
          'id' => $this->id,
        ]);
      } else {
        $url = CRM_Utils_System::url('civicrm/civimobile/venue', http_build_query([
          'action' => 'delete',
          'reset' => '1',
          'id' => $this->id,
          'location_id' => $inputValues['location_id']
        ]));
        CRM_Core_Session::setStatus(ts('This Venue is used in the session. To remove it, first remove it from the session.'), ts("Venue is used"), "error");
      }
    }

    $this->controller->setDestination($url);
  }

  /**
   * Set defaults for form.
   */
  public function setDefaultValues() {
    $defaults = [];

    if ($this->getAction() == CRM_Core_Action::ADD) {
      $defaults['is_active'] = 1;
      $fieldValues = ['location_id' => $this->location_id];
      $defaults['weight'] = CRM_Utils_Weight::getMax('CRM_CiviMobileAPI_DAO_LocationVenue', $fieldValues, 'weight') + 1;
    }

    if ($this->getAction() == CRM_Core_Action::UPDATE) {
      $venueData = CRM_CiviMobileAPI_BAO_LocationVenue::getAll(['id' => $this->id])[0];
      $defaults['venue_name'] = $venueData['name'];
      $defaults['is_active'] = $venueData['is_active'];
      $defaults['description'] = $venueData['description'];
      $defaults['address_description'] = $venueData['address_description'];
      $defaults['address'] = $venueData['address'];
      $defaults['weight'] = $venueData['weight'];
    }

    return $defaults;
  }

  /**
   * AddRules hook
   */
  public function addRules() {
    if ($this->getAction() == CRM_Core_Action::ADD && $this->getAction() == CRM_Core_Action::UPDATE) {
      $this->addFormRule([self::class, 'validateForm']);
    }
  }

  /**
   * Validates form
   *
   * @param $values
   *
   * @return array
   */
  public static function validateForm($values) {
    $errors = [];
    if (array_key_exists("venue_name", $values)) {
      if (empty($values["venue_name"])) {
        $errors["venue_name"] = ts('Name can`t be empty!');
      }
      if (strlen($values["venue_name"]) > 255) {
        $errors["venue_name"] = ts('Name length must be less than 255 characters.');
      }
    }
    if (array_key_exists("attached_file_url", $values)) {
      if (strlen($values["attached_file_url"]) > 255) {
        $errors["attached_file_url"] = ts('Attached file url length must be less than 255 characters.');
      }
    }
    if (array_key_exists("address", $values)) {
      if (strlen($values["address"]) > 255) {
        $errors["address"] = ts('Venue location length must be less than 255 characters.');
      }
    }
    if (!empty($_FILES['attached_file']['type']) && !in_array($_FILES['attached_file']['type'], ['application/pdf', 'image/png', 'image/jpeg'])) {
      $errors["attached_file"] = ts('You can upload only PDF, PNG and JPEG files.');
    }

    $venuesWithSameName = civicrm_api3('CiviMobileVenue', 'get', [
      'sequential' => 1,
      'location_id' => $values['location_id'],
      'name' => $values['venue_name']
    ]);

    if (($venuesWithSameName['count'] && empty($values["id"])) || (!empty($values["id"]) && $venuesWithSameName['count'] && $venuesWithSameName['values'][0]['id'] != $values['id'])) {
      $errors["venue_name"] = ts('Venue with same name already exists for this location.');
    }

    return empty($errors) ? TRUE : $errors;
  }

  public static function generateFileURL() {
    if (empty($_FILES['attached_file']['name'])) {
      return '';
    }
    $fileName = basename($_FILES['attached_file']['name']);
    $fileType = $_FILES['attached_file']['type'];
    $fileStructure = pathinfo($fileName);
    $fileSalt = "e787ada2e9a69a3bc67d14893ac3sdf3a67a21a2a" . time();
    $newName = md5($fileName . $fileSalt) . time() . '.' . $fileStructure['extension'];
    $pathToCustomFileUploadDir = CRM_CiviMobileAPI_Utils_File::getUploadDirPath() . $newName;

    if (!move_uploaded_file($_FILES['attached_file']['tmp_name'], $pathToCustomFileUploadDir)) {
      CRM_Core_Error::statusBounce(ts("Can`t upload file"));
    }
    $currentCMS = CRM_CiviMobileAPI_Utils_CmsUser::getInstance()->getSystem();
    $fileUrl = CRM_Utils_System::url('civicrm/file', ['filename' => $newName, 'mime-type' => $fileType], TRUE);
    if ($currentCMS == CRM_CiviMobileAPI_Utils_CmsUser::CMS_JOOMLA) {
      $fileUrl = preg_replace('/administrator\//', 'index.php', $fileUrl);
    }

    return $fileUrl;
  }

}
