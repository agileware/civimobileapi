<?php

class CRM_CiviMobileAPI_Utils_ContributionChartBar {

  /**
   * Get statistic by period
   *
   * @param $listOfContactId
   * @param $startDateParam
   * @param $endDateParam
   * @return array
   */
  public function periodDivide($listOfContactId, $params) {
    $preparedReceiveDate = $this->getPrepareReceiveDate($params);
    $period = $this->findPeriod($preparedReceiveDate['start_date'], $preparedReceiveDate['end_date']);

    if ($period == 'days') {
      return $this->dayPeriodDivide($listOfContactId, $params);
    }
    elseif ($period == 'weeks') {
      return $this->weekPeriodDivide($listOfContactId, $params);
    }
    elseif ($period == 'months') {
      return $this->monthPeriodDivide($listOfContactId, $params);
    }
    elseif ($period == 'years') {
      return $this->yearPeriodDivide($listOfContactId, $params);
    }
    else {
      return [];
    }
  }

  /**
   * Get period for contribution statistic
   *
   * @param $startDate
   * @param $endDate
   * @return false|string
   */
  public function findPeriod($startDate, $endDate) {
    $startDate = date_create($startDate);
    $endDate = date_create($endDate);
    $interval = date_diff($startDate, $endDate);

    if ($interval->days <= 14) {
      return 'days';
    }
    elseif ($interval->days > 14 && $interval->days <= 60) {
      return 'weeks';
    }
    elseif ($interval->days > 60 && $interval->y == 0 ) {
      return 'months';
    }
    elseif ($interval->y != 0) {
      return 'years';
    }
    else
      return FALSE;
  }

  /**
   * Get statistic by days
   *
   * @param $listOfContactId
   * @param $params
   * @return array
   */
  public function dayPeriodDivide($listOfContactId, $params) {
    $preparedReceiveDate = $this->getPrepareReceiveDate($params);

    $startDate = date_create($preparedReceiveDate['start_date']);
    $endDate = date_create($preparedReceiveDate['end_date']);
    $interval = date_diff($endDate,$startDate);

    $count = $interval->days;

    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = date_add($startDate, date_interval_create_from_date_string('1 days'));
    $statistic = [];

    for ($i = 0; $i < $count; $i++) {
      $firstDate = $firstDate->format('Y-m-d');
      $secondDate = $secondDate->format('Y-m-d');

      $selector = $this->getContributeSelector($listOfContactId, $params,  $firstDate, $secondDate);

      if ($i == ($count - 1)) {
        $secondDate = $endDate->format('Y-m-d');
      }

      $statistic[] = $this->transformStatistic($selector->getSummary(), $firstDate, $secondDate);

      $firstDate = date_create($secondDate);
      $secondDate = date_create($secondDate);
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('1 days'));
    }

    return $statistic;
  }

  /**
   * Get statistic by weeks
   *
   * @param $listOfContactId
   * @param $params
   * @return array
   */
  public function weekPeriodDivide($listOfContactId, $params) {
    $preparedReceiveDate = $this->getPrepareReceiveDate($params);

    $date = date_create($preparedReceiveDate['start_date']);
    $startDate = date_create($preparedReceiveDate['start_date']);
    $endDate = date_create($preparedReceiveDate['end_date']);
    $interval = date_diff($endDate,$startDate);

    $count = $interval->days;
    $countOfWeeks = ceil($count / 7);

    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = date_add($startDate, date_interval_create_from_date_string('7 days'));
    $displayedSecondDate = date_add($date, date_interval_create_from_date_string('6 days'));
    $statistic = [];

    for ($i = 0; $i < $countOfWeeks; $i++) {
      $firstDate = $firstDate->format('Y-m-d');
      $secondDate = $secondDate->format('Y-m-d');
      $displayedSecondDate = $displayedSecondDate->format('Y-m-d');

      if ($i == ($countOfWeeks - 1)) {
        $secondDate = $endDate->format('Y-m-d');
        $displayedSecondDate = $endDate->format('Y-m-d');
      }

      $selector = $this->getContributeSelector($listOfContactId, $params,  $firstDate, $secondDate);

      $statistic[] = $this->transformStatistic($selector->getSummary(), $firstDate, $displayedSecondDate);

      $firstDate = date_create($secondDate);
      $secondDate = date_create($secondDate);
      $displayedSecondDate = date_create($displayedSecondDate);
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('7 days'));
      $displayedSecondDate = date_add($displayedSecondDate, date_interval_create_from_date_string('7 days'));
    }
    return $statistic;
  }

  /**
   * Get statistic by months
   *
   * @param $listOfContactId
   * @param $params
   * @return array
   */
  public function monthPeriodDivide($listOfContactId, $params) {
    $preparedReceiveDate = $this->getPrepareReceiveDate($params);

    $startDate = date_create($preparedReceiveDate['start_date']);
    $endDate = date_create($preparedReceiveDate['end_date']);
    $interval = date_diff($endDate,$startDate);

    $countOfMonth = $interval->m;
    $countOfDays = $interval->d;

    if (!empty($countOfDays)) {
      $countOfMonth++;
    }

    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = date_create(date("Y-m-t", strtotime($preparedReceiveDate['start_date'])));
    $secondDate = date_add($secondDate, date_interval_create_from_date_string('1 days'));

    $statistic = [];

    for ($i = 0; $i < $countOfMonth; $i++) {
      $firstDate = $firstDate->format('Y-m-d');
      $secondDate = $secondDate->format('Y-m-d');

      if ($i == ($countOfMonth - 1)) {
        $secondDate = $endDate->format('Y-m-d');
      }

      $selector = $this->getContributeSelector($listOfContactId, $params,  $firstDate, $secondDate);

      $statistic[] = $this->transformStatistic($selector->getSummary(), $firstDate, $secondDate);

      $firstDate = date_create($secondDate);
      $secondDate = date_create($secondDate);
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('1 month'));
    }

    return $statistic;
  }

  /**
   * Get statistic by years
   *
   * @param $listOfContactId
   * @param $params
   * @return array
   */
  public function yearPeriodDivide($listOfContactId, $params) {
    $preparedReceiveDate = $this->getPrepareReceiveDate($params);

    $date = date_create($preparedReceiveDate['start_date']);
    $startDate = date_create($preparedReceiveDate['start_date']);
    $endDate = date_create($preparedReceiveDate['end_date']);
    $interval = date_diff($endDate,$startDate);

    $countOfYears = $interval->y;
    $countOfMonth = $interval->m;
    $countOfDays = $interval->d;

    if (!empty($countOfMonth) || !empty($countOfDays)) {
      $countOfYears++;
    }

    $firstDate = date_create($preparedReceiveDate['start_date']);

    $beginningOfNextYear = date_add($date, date_interval_create_from_date_string('1 year'));
    $beginningOfNextYear = $beginningOfNextYear->format('Y');
    $nextYear = date('Y-m-d', strtotime("first day of january $beginningOfNextYear "));
    $secondDate = date_create($nextYear);
    $statistic = [];

    for ($i = 0; $i < $countOfYears; $i++) {
      $firstDate = $firstDate->format('Y-m-d');
      $secondDate = $secondDate->format('Y-m-d');

      if ($i == ($countOfYears - 1)) {
        $secondDate = $endDate->format('Y-m-d');
      }

      $selector = $this->getContributeSelector($listOfContactId, $params,  $firstDate, $secondDate);

      $statistic[] = $this->transformStatistic($selector->getSummary(), $firstDate, $secondDate);

      $firstDate = date_create($secondDate);
      $secondDate = date_create($secondDate);
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('1 year'));
    }

    return $statistic;
  }

  /**
   * Get contribute selector
   *
   * @param $listOfContactId
   * @param $params
   * @param $firstDate
   * @param $secondDate
   * @return CRM_Contribute_Selector_Search
   */
  public function getContributeSelector($listOfContactId, $params, $firstDate, $secondDate) {
    $contactQuery = ["IN" => $listOfContactId];
    $contributionFields = (new CRM_CiviMobileAPI_Utils_ContactsContributionStatistic)->getContributionFieldsParams($params);

    $totalQueryParams = [
      'contact_id' => (!empty($listOfContactId)) ? $contactQuery : ['IS NULL' => 1],
      'receive_date' => [
        'BETWEEN' => [
          $firstDate,
          $secondDate,
        ]
      ],
      'financial_type_id' => $contributionFields['financial_type_id'],
      'payment_instrument_id' => $contributionFields['payment_instrument_id'],
      'contribution_status_id' => $contributionFields['contribution_status_id'],
      'total_amount' => $contributionFields['total_amount'],
    ];

    $selectedContributions = CRM_Contact_BAO_Query::convertFormValues($totalQueryParams);
    $selector = new CRM_Contribute_Selector_Search($selectedContributions);

    return $selector;
  }

  /**
   * Return format statistic
   *
   * @param $statistic
   * @param $firstDate
   * @param $secondDate
   * @return array
   */
  public function transformStatistic($statistic, $firstDate, $secondDate) {
    if (!empty($statistic['total'])) {
      $statistic['total']['amount'] = static::transform($statistic['total']['amount']);
      $statistic['total']['first_date'] = $firstDate;
      $statistic['total']['second_date'] = $secondDate;
    }

    if (!empty($statistic['cancel'])) {
      $statistic['cancel']['amount'] = static::transform($statistic['cancel']['amount']);
    }

    return [
      'amount' => $statistic['total']['amount'],
      'first_date' => $statistic['total']['first_date'],
      'second_date' => $statistic['total']['second_date']
    ];
  }

  /**
   * Explodes and trims string
   *
   * @param $string
   * @return array
   */
  private static function transform($string) {
    if (!empty($string)) {
      $string = str_replace("$", "", $string);
    }
    $result = [];
    $exploded = explode(',', str_replace("&nbsp;", "", $string));
    foreach ($exploded as $item) {
      $result[] = trim($item);
    }

    return $result;
  }

  /**
   * Get minimal contribution date
   *
   * @return string
   */
  public function getDefaultContributionStartDate() {
    $select = 'SELECT MIN(civicrm_contribution.receive_date) as min_receive_date';
    $from = ' FROM civicrm_contribution';
    $sql = $select . $from;

    $receiveStartDate = '';
    try {
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        $receiveStartDate = $dao->min_receive_date;
      }
    } catch (Exception $e) {
      return $receiveStartDate;
    }

    return $receiveStartDate;
  }

  /**
   * Get prepare receive date
   *
   * @param $params
   * @return array
   */
  public function getPrepareReceiveDate($params) {
    $defaultStartDate = (new CRM_CiviMobileAPI_Utils_ContributionChartBar)->getDefaultContributionStartDate();

    if (!empty($params['receive_date']['BETWEEN']) || empty($params['receive_date'])) {
      $startDate = !empty($params['receive_date']['BETWEEN'][0]) ? $params['receive_date']['BETWEEN'][0] : $defaultStartDate;
      $endDate = !empty($params['receive_date']['BETWEEN'][1]) ? $params['receive_date']['BETWEEN'][1] : date('Y-m-d');
    }
    elseif (!empty($params['receive_date']['<'])) {
      $startDate = $defaultStartDate;
      $endDate = $params['receive_date']['<'];
    }
    elseif (!empty($params['receive_date']['>'])) {
      $startDate = $params['receive_date']['>'];
      $endDate = date("Y-m-d", strtotime('tomorrow'));
    }

    return [
      'start_date' => $startDate,
      'end_date' => $endDate
    ];
  }

}
