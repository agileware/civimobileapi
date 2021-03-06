<?php

use CRM_CiviMobileAPI_ExtensionUtil as E;

return [
  'civimobileapi_calendar_activity_types' => [
    'group_name' => 'Civimobileapi Calendar Settings',
    'group' => 'civimobileapi_calendar',
    'name' => 'civimobileapi_calendar_activity_types',
    'type' => 'String',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => [],
    'description' => E::ts('Shows certain activity types on the calendar'),
    'html_type' => 'Select',
    'html_attributes' => [
      'size' => 20,
      'class' => 'crm-select2',
    ],
    'multiple' => true,
    'pseudoconstant' => ['optionGroupName' => 'activity_type'],
  ],

  'civimobileapi_calendar_event_types' => [
    'group_name' => 'Civimobileapi Calendar Settings',
    'group' => 'civimobileapi_calendar',
    'name' => 'civimobileapi_calendar_event_types',
    'type' => 'String',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => [],
    'description' => E::ts('Shows certain event types on the calendar'),
    'html_type' => 'Select',
    'html_attributes' => [
      'size' => 20,
      'class' => 'crm-select2',
    ],
    'multiple' => true,
    'pseudoconstant' => ['optionGroupName' => 'event_type'],
  ],

  'civimobileapi_calendar_case_types' => [
    'group_name' => 'Civimobileapi Calendar Settings',
    'group' => 'civimobileapi_calendar',
    'name' => 'civimobileapi_calendar_case_types',
    'type' => 'String',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => [],
    'description' => E::ts('Shows certain case types on the calendar'),
    'html_type' => 'Select',
    'html_attributes' => [
      'size' => 20,
      'class' => 'crm-select2',
    ],
    'multiple' => true,
    'option_values' => CRM_Case_PseudoConstant::caseType('title'),
  ],

  'civimobileapi_calendar_hide_past_events' => [
    'group_name' => 'Civimobileapi Calendar Settings',
    'group' => 'civimobileapi_calendar',
    'name' => 'civimobileapi_calendar_hide_past_events',
    'type' => 'String',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => '0',
    'description' => E::ts('Hide past events'),
    'html_type' => 'Checkbox',
  ],

  'civimobileapi_calendar_synchronize_with_civicalendar' => [
    'group_name' => 'Civimobileapi Calendar Settings',
    'group' => 'civimobileapi_calendar',
    'name' => 'civimobileapi_calendar_synchronize_with_civicalendar',
    'type' => 'String',
    'add' => '4.7',
    'is_domain' => 1,
    'is_contact' => 0,
    'default' => '0',
    'description' => E::ts('Synchronize with CiviCalendar'),
    'html_type' => 'Checkbox',
  ],
];
