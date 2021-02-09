<?php

/**
 * @deprecated will be deleted in version 7.0.0
 */
class CRM_CiviMobileAPI_ApiWrapper_Contribution implements API_Wrapper {

  /**
   * Interface for interpreting api input
   *
   * @param array $apiRequest
   *
   * @return array
   */
  public function fromApiInput($apiRequest) {
    if (!empty($apiRequest['params']['return'])) {
      if (is_string($apiRequest['params']['return'])) {
        $apiRequest['params']['return'] = explode(',', $apiRequest['params']['return']);
      }
      $apiRequest['params']['return'] = array_unique(array_merge($apiRequest['params']['return'], ['currency', 'financial_type_id']));
    }
    return $apiRequest;
  }

  /**
   * Interface for interpreting api output
   *
   * @param $apiRequest
   * @param $result
   *
   * @return array
   */
  public function toApiOutput($apiRequest, $result) {
    if (is_string($apiRequest['params']['return'])) {
      $apiRequest['params']['return'] = explode(',', $apiRequest['params']['return']);
    }

    $result = $this->fillFinancialTypeName($apiRequest, $result);
    $result = $this->fillFormatTotalAmount($apiRequest, $result);

    return $result;
  }

  /**
   * Check with API version is being called.
   *
   * @param $result - either apiv3 or apiv4 result
   *
   * @return Boolean - TRUE if the api result is from version 3.
   */
  private static function isApiVersion3($result) {
    if (is_array($result) && isset($result['version']) && $result['version'] == 3) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param array $apiRequest
   * @param array $result
   *
   * @return mixed
   */
  private function fillFinancialTypeName($apiRequest, $result) {
    if (!self::isApiVersion3($result)) {
      return $result;
    }

    if (!empty($apiRequest['params']['return'])
      && in_array('financial_type_name', $apiRequest['params']['return'])
      && $apiRequest['action'] == 'get'
    ) {
      foreach ($result['values'] as &$contribution) {
        $contribution['financial_type_name'] = $this->getFinancialTypeName($contribution);
      }
    }

    return $result;
  }

  /**
   * @param array $apiRequest
   * @param array $result
   *
   * @return mixed
   */
  private function fillFormatTotalAmount($apiRequest, $result) {
    if (!self::isApiVersion3($result)) {
      return $result;
    }

    if (!empty($apiRequest['params']['return'])
      && in_array('total_amount', $apiRequest['params']['return'])
      && $apiRequest['action'] == 'get'
    ) {
      foreach ($result['values'] as &$contribution) {
        $contribution['format_total_amount'] = CRM_Utils_Money::format($contribution['total_amount'], $contribution['currency']);
      }
    }

    return $result;
  }

  /**
   * @param array $contribution
   *
   * @return string
   */
  private function getFinancialTypeName($contribution) {
    if (!empty($contribution['financial_type_id'])) {
      return CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialType', $contribution['financial_type_id'], 'name');
    } else {
      $financialTypeId = CRM_Core_DAO::getFieldValue('CRM_Contribute_DAO_Contribution', $contribution['id'], 'financial_type_id');

      return CRM_Core_DAO::getFieldValue('CRM_Financial_DAO_FinancialType', $financialTypeId, 'name');
    }
  }
}
