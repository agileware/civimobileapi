<?php

class CRM_CiviMobileAPI_Form_EventLocations extends CRM_Core_Form {

  /**
   * Build the form object
   *
   * @return void
   * @throws \HTML_QuickForm_Error
   */
  public function buildQuickForm() {
    $this->assign('locations', CRM_Event_BAO_Event::getLocationEvents());
    $this->assign('locationsId', array_keys(CRM_Event_BAO_Event::getLocationEvents()));
    $buttons = [
      [
        'type' => 'cancel',
        'name' => ts('Back'),
      ],
    ];
    $this->addButtons($buttons);
  }

}
