<?php

/**
 * Class provide Event helper methods
 */
class CRM_CiviMobileAPI_Utils_Event {

  /**
   * Checks if "Same email" option is enabled
   * This option allows creating Participant
   * with Contacts which has same emails
   *
   * @param $eventId
   *
   * @return bool
   * @throws \CiviCRM_API3_Exception
   */
  public static function isAllowSameEmail($eventId) {
    $allowSameParticipantEmails = civicrm_api3('Event', 'getvalue', [
      'return' => "allow_same_participant_emails",
      'id' => $eventId,
    ]);

    return !empty($allowSameParticipantEmails) && $allowSameParticipantEmails == 1;
  }

  /**
   * Gets Event by id
   *
   * @param $eventId
   *
   * @return bool
   */
  public static function getById($eventId) {
    $event = civicrm_api3('Event', 'getsingle', [
      'id' => $eventId
    ]);

    return (!empty($event) && $event['is_error'] != 1) ? $event : false;
  }

  /**
   * Check is event have location
   *
   * @param $eventId
   *
   * @return bool
   */
  public static function isEventHaveLocation($eventId) {
    try {
      $apiOutput = civicrm_api3('Event', 'getsingle', [
        'return' => ["loc_block_id"],
        'id' => $eventId,
      ]);
    } catch (Exception $e) {
      return FALSE;
    }
    if (array_key_exists('loc_block_id', $apiOutput)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
