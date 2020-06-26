<?php

/**
 * Deletes venue attached file
 *
 * @param array $params
 *
 * @return array
 */
function  civicrm_api3_civi_mobile_venue_attach_file_delete($params) {
  CRM_CiviMobileAPI_Utils_Agenda_Venue::removeVenueAttach($params['id']);

  return civicrm_api3_create_success([
    'message' => 'Attached file was deleted'
  ]);
}

/**
 * Adjust Metadata for delete action
 *
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_civi_mobile_venue_attach_file_delete_spec(&$params) {
  $params['id'] = [
    'title' => 'Venue Id',
    'description' => ts('Venue Id'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];
}
