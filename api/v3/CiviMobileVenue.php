<?php

/**
 * Gets venues
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_civi_mobile_venue_get($params) {
  if (!CRM_CiviMobileAPI_Utils_Permission::isEnoughPermissionForGetEventVenues()) {
    throw new api_Exception('You don`t have enough permissions.', 'do_not_have_enough_permissions');
  }
  $preparedVenues = [];

  $venues = CRM_CiviMobileAPI_BAO_LocationVenue::getAll($params);

  foreach ($venues as $venue) {
    $preparedVenue = [
      'id' => $venue['id'],
      'name' => $venue['name'],
      'description' => $venue['description'],
      'address_description' => $venue['address_description'],
      'address' => $venue['address'],
      'longitude' => $venue['longitude'],
      'latitude' => $venue['latitude'],
      'is_active' => $venue['is_active'],
      'location_id' => $venue['location_id'],
      'background_color' => $venue['background_color'],
      'border_color' => $venue['border_color'],
      'weight' => $venue['weight'],
      'attached_files' => !empty($venue['attached_file_url']) ? [
        [
          'url' => $venue['attached_file_url'],
          'type' => $venue['attached_file_type'],
        ]
      ] : []
    ];

    if (!empty($params['sequential'])) {
      $preparedVenues[] = $preparedVenue;
    } else {
      $preparedVenues[$preparedVenue['id']] = $preparedVenue;
    }
  }

  return civicrm_api3_create_success($preparedVenues, $params);
}

/**
 * Adjust Metadata for get action
 *
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_civi_mobile_venue_get_spec(&$params) {
  $params['location_id'] = [
    'title' => 'Location Id',
    'description' => ts('Location Id'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['name'] = [
    'title' => 'Name',
    'description' => ts('Name'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['id'] = [
    'title' => 'Venue Id',
    'description' => ts('Venue Id'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['is_active'] = [
    'title' => 'Is active',
    'description' => ts('Is active'),
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
  $params['description'] = [
    'title' => 'Description',
    'description' => ts('Description'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['attached_file_url'] = [
    'title' => 'Attached file',
    'description' => ts('Attached file'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['address'] = [
    'title' => 'Address',
    'description' => ts('Address'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['address_description'] = [
    'title' => 'Address_description',
    'description' => ts('Address_description'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Create venues
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_civi_mobile_venue_create($params) {
  $result = (new CRM_CiviMobileAPI_Api_CiviMobileVenue_Create($params))->getResult();

  return civicrm_api3_create_success($result);
}

/**
 * Adjust Metadata for create action
 *
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_civi_mobile_venue_create_spec(&$params) {
  $params['location_id'] = [
    'title' => 'Location Id',
    'description' => ts('Location Id'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['name'] = [
    'title' => 'Name',
    'description' => ts('Name'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['id'] = [
    'title' => 'Venue Id',
    'description' => ts('Venue Id'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['is_active'] = [
    'title' => 'Is active',
    'description' => ts('Is active'),
    'type' => CRM_Utils_Type::T_BOOLEAN,
  ];
  $params['description'] = [
    'title' => 'Description',
    'description' => ts('Description'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['attached_file_url'] = [
    'title' => 'Attached file',
    'description' => ts('Attached file'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['attached_file_type'] = [
    'title' => 'Attached file type',
    'description' => ts('Attached file type'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['address'] = [
    'title' => 'Address',
    'description' => ts('Address'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['address_description'] = [
    'title' => 'Address description',
    'description' => ts('Address description'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['background_color'] = [
    'title' => 'Background color',
    'description' => ts('Background color'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['border_color'] = [
    'title' => 'Border color',
    'description' => ts('Border color'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $params['weight'] = [
    'title' => 'Weight',
    'description' => ts('Weight'),
    'type' => CRM_Utils_Type::T_INT,
  ];
}

/**
 * Deletes venue
 *
 * @param array $params
 *
 * @return array
 */
function civicrm_api3_civi_mobile_venue_delete($params) {
  if (!CRM_CiviMobileAPI_Utils_Permission::isEnoughPermissionForDeleteEventVenues()) {
    throw new api_Exception('You don`t have enough permissions.', 'do_not_have_enough_permissions');
  }
  try {
    $venue = CRM_CiviMobileAPI_BAO_LocationVenue::findById($params['id']);
  } catch (Exception $e) {
    throw new api_Exception('Venue does not exists.', 'venue_does not exists.');
  }
  CRM_CiviMobileAPI_Utils_Agenda_Venue::removeVenueAttach($params['id']);
  $venue->del($params['id']);

  return civicrm_api3_create_success([
    'message' => 'The EventVenue was deleted.'
  ]);
}

/**
 * Adjust Metadata for delete action
 *
 * The metadata is used for setting defaults, documentation & validation
 * @param array $params array or parameters determined by getfields
 */
function _civicrm_api3_civi_mobile_venue_delete_spec(&$params) {
  $params['id'] = [
    'title' => 'Venue Id',
    'description' => ts('Venue Id'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1
  ];
}

/**
 * Params for CiviMobileVenue getlist
 *
 * @param $request
 */
function _civicrm_api3_civi_mobile_venue_getlist_params(&$request) {
  $request['params']['name']['LIKE'] = $request['input'];
  $request['params']['options']['limit'] = 0;

  if (!empty($request['params']['id']['IN'])) {
    $request['params']['id'] = $request['params']['id']['IN'][0];
  }
}

/**
 * Get output for CiviMobileVenue list.
 *
 * @param array $result
 * @param array $request
 *
 * @param $entity
 * @param $fields
 * @return array
 * @see _civicrm_api3_generic_getlist_output
 *
 */
function _civicrm_api3_civi_mobile_venue_getlist_output($result, $request, $entity, $fields) {
  $output = [];
  foreach ($result['values'] as $venue) {
    $output[] = [
      'id' => $venue['id'],
      'label' => $venue['name'],
      'description' => []
    ];
  }

  return $output;
}

