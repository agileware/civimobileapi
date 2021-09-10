<?php

class CRM_CiviMobileAPI_Utils_ContributionChartBar {

  /**
   * Separator for currency formatting
   */
  private static $currencySeparator = '___';

  /**
   * Add amounts with extra currencies to statistics
   *
   * @param $statistics
   * @return mixed
   */
  private static function addExtraAmountsToStatistics($statistics) {
    $currencies = [];

    foreach ($statistics as $stat) {
      foreach ($stat['amounts'] as $amount) {
        if (!in_array($amount['currency'], $currencies)) {
          $currencies[] = $amount['currency'];
        }
      }
    }

    foreach ($statistics as &$stat) {
      $availableCurrencies = [];
      foreach ($stat['amounts'] as $amount) {
        $availableCurrencies[] = $amount['currency'];
      }

      $additionalCurrencies = array_diff($currencies, $availableCurrencies);
      foreach ($additionalCurrencies as $currency) {
        $stat['amounts'][] = [
          'amount' => 0,
          'currency' => $currency,
          'formattedAmount' => CRM_Utils_Money::format(0, $currency),
        ];
      }
    }

    return $statistics;
  }

  /**
   * Get statistic by period
   *
   * @param $listOfContactId
   * @param $params
   * @return array
   */
  public function periodDivide($listOfContactId, $params) {
    $preparedReceiveDate = $this->getPrepareReceiveDate($params);
    $period = $this->findPeriod($preparedReceiveDate['start_date'], $preparedReceiveDate['end_date']);

    $statistics = [];

    if ($period == 'days') {
      $statistics = $this->dayPeriodDivide($listOfContactId, $params);
    }
    elseif ($period == 'weeks') {
      $statistics = $this->weekPeriodDivide($listOfContactId, $params);
    }
    elseif ($period == 'months') {
      $statistics = $this->monthPeriodDivide($listOfContactId, $params);
    }
    elseif ($period == 'years') {
      $statistics = $this->yearPeriodDivide($listOfContactId, $params);
    }

    return self::addExtraAmountsToStatistics($statistics);
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
    $isNextYear = $interval->y > 0 && !($interval->y == 1 && $endDate->format('m-d') == '01-01');

    if ($interval->days <= 14) {
      return 'days';
    }
    elseif ($interval->days > 14 && $interval->days <= 60) {
      return 'weeks';
    }
    elseif ($interval->days > 60 && !$isNextYear) {
      return 'months';
    }
    elseif ($isNextYear) {
      return 'years';
    }
    else
      return FALSE;
  }

  /**
   * Get Summary with Selector
   *
   * @param $selector
   * @return mixed
   */
  private static function getSummary($selector) {
    $config = CRM_Core_Config::singleton();
    $configMoneyFormat = $config->moneyformat;
    $monetaryThousandSeparator = $config->monetaryThousandSeparator;
    $monetaryDecimalPoint = $config->monetaryDecimalPoint;
    $config->moneyformat = '%C' . self::$currencySeparator . '%a';
    $config->monetaryThousandSeparator = '';
    $config->monetaryDecimalPoint = '.';

    $summary = $selector->getSummary();
    $config->moneyformat = $configMoneyFormat;
    $config->monetaryThousandSeparator = $monetaryThousandSeparator;
    $config->monetaryDecimalPoint = $monetaryDecimalPoint;

    return $summary;
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

    $endDate = date_create($preparedReceiveDate['end_date']);
    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = clone $firstDate;
    $statistic = [];

    while ($secondDate->getTimestamp() < $endDate->getTimestamp()) {
      $secondDate = date_add($secondDate, date_interval_create_from_date_string("1 days"));

      if ($secondDate->getTimestamp() > $endDate->getTimestamp()) {
        $secondDate = clone $endDate;
      }

      $formattedFirstDate = $firstDate->format('Y-m-d');
      $formattedSecondDate = $secondDate->format('Y-m-d');

      $selector = $this->getContributeSelector($listOfContactId, $params,  $formattedFirstDate, $formattedFirstDate . ' 23:59:59');
      $statistic[] = $this->transformStatistic(self::getSummary($selector), $formattedFirstDate, $formattedSecondDate);
      $firstDate = clone $secondDate;
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
    $endDate = date_create($preparedReceiveDate['end_date']);

    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = clone $firstDate;
    $statistic = [];

    while ($secondDate->getTimestamp() < $endDate->getTimestamp()) {
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('7 days'));

      if ($secondDate->getTimestamp() > $endDate->getTimestamp()) {
        $secondDate = clone $endDate;
      }

      $formattedFirstDate = $firstDate->format('Y-m-d');
      $prevSecondDate = clone $secondDate;
      $prevSecondDate->modify('-1 second');
      $prevDay = clone $secondDate;
      $prevDay->modify('-1 days');

      $selector = $this->getContributeSelector($listOfContactId, $params,  $formattedFirstDate, $prevSecondDate->format('Y-m-d H:i:s'));
      $statistic[] = $this->transformStatistic(self::getSummary($selector), $formattedFirstDate, $prevDay->format('Y-m-d'));

      $firstDate = clone $secondDate;
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

    $endDate = date_create($preparedReceiveDate['end_date']);

    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = date_create(date("Y-m", strtotime($preparedReceiveDate['start_date'])) . '-01');

    $statistic = [];

    while ($secondDate->getTimestamp() < $endDate->getTimestamp()) {
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('1 months'));

      if ($secondDate->getTimestamp() > $endDate->getTimestamp()) {
        $secondDate = clone $endDate;
      }

      $formattedFirstDate = $firstDate->format('Y-m-d');
      $prevSecondDate = clone $secondDate;
      $prevSecondDate->modify('-1 second');

      $selector = $this->getContributeSelector($listOfContactId, $params,  $formattedFirstDate, $prevSecondDate->format('Y-m-d H:i:s'));
      $statistic[] = $this->transformStatistic(self::getSummary($selector), $formattedFirstDate, $secondDate->format('Y-m-d'));

      $firstDate = clone $secondDate;
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
    $endDate = date_create($preparedReceiveDate['end_date']);

    $firstDate = date_create($preparedReceiveDate['start_date']);
    $secondDate = date_create(date('Y', $date->getTimestamp()) . '-01-01');
    $statistic = [];

    while ($secondDate->getTimestamp() < $endDate->getTimestamp()) {
      $secondDate = date_add($secondDate, date_interval_create_from_date_string('1 year'));

      if ($secondDate->getTimestamp() > $endDate->getTimestamp()) {
        $secondDate = clone $endDate;
      }

      $formattedFirstDate = $firstDate->format('Y-m-d');
      $prevSecondDate = clone $secondDate;
      $prevSecondDate->modify('-1 second');

      $selector = $this->getContributeSelector($listOfContactId, $params,  $formattedFirstDate, $prevSecondDate->format('Y-m-d H:i:s'));
      $statistic[] = $this->transformStatistic(self::getSummary($selector), $formattedFirstDate, $secondDate->format('Y-m-d'));

      $firstDate = clone $secondDate;
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
    $newAmount = [];

    if (!empty($statistic['total'])) {
      $statistic['total']['amount'] = static::transform($statistic['total']['amount']);
      $clearAmounts = [];
      foreach ($statistic['total']['amount'] as $moneyString) {
        $amountParts = explode(self::$currencySeparator, $moneyString);
        $currencyName = $amountParts[0];
        $clearAmount = $amountParts[1];
        $formattedAmount = CRM_Utils_Money::format($clearAmount, $currencyName);
        $clearAmounts[] = $clearAmount;

        $newAmount[] = [
          'amount' => (float) $clearAmount,
          'currency' => $currencyName,
          'formattedAmount' => $formattedAmount,
        ];
      }
      $statistic['total']['amount'] = $clearAmounts;
      $statistic['total']['first_date'] = $firstDate;
      $statistic['total']['second_date'] = $secondDate;
    }

    if (!empty($statistic['cancel'])) {
      $statistic['cancel']['amount'] = static::transform($statistic['cancel']['amount']);
    }

    return [
      'amount' => $statistic['total']['amount'],
      'amounts' => $newAmount,
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
    return !empty($string) ? explode(",&nbsp;", $string) : [];
  }

  /**
   * Returns contribution date interval
   *
   * @return array
   */
  public static function getDefaultContributionDateInterval() {
    $select = 'SELECT MIN(civicrm_contribution.receive_date) as min_receive_date, MAX(civicrm_contribution.receive_date) as max_receive_date';
    $from = ' FROM civicrm_contribution';
    $sql = $select . $from;

    $dates = [
      'min_receive_date' => '',
      'max_receive_date' => ''
    ];

    try {
      $dao = CRM_Core_DAO::executeQuery($sql);

      if ($dao->fetch()) {
        $dates['min_receive_date'] = $dao->min_receive_date;
        $dates['max_receive_date'] = ((int)date('Y', strtotime($dao->max_receive_date)) + 1) . '-01-01';
      }
    } catch (Exception $e) {}

    return $dates;
  }

  /**
   * Returns prepared receive date
   *
   * @param $params
   * @return array
   */
  public function getPrepareReceiveDate($params) {
    $defaultIntervals = CRM_CiviMobileAPI_Utils_ContributionChartBar::getDefaultContributionDateInterval();

    if (!empty($params['receive_date']['BETWEEN']) || empty($params['receive_date'])) {
      $startDate = !empty($params['receive_date']['BETWEEN'][0]) ? $params['receive_date']['BETWEEN'][0] : $defaultIntervals['min_receive_date'];
      $endDate = !empty($params['receive_date']['BETWEEN'][1]) ? $params['receive_date']['BETWEEN'][1] : $defaultIntervals['max_receive_date'];
    } elseif (!empty($params['receive_date']['<'])) {
      $startDate = $defaultIntervals['min_receive_date'];
      $endDate = $params['receive_date']['<'];
    } elseif (!empty($params['receive_date']['>'])) {
      $startDate = $params['receive_date']['>'];
      $endDate = ((int)date('Y') + 1) . '-01-01';
    }

    return [
      'start_date' => $startDate,
      'end_date' => $endDate
    ];
  }

}
