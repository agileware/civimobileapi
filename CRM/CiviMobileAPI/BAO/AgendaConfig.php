<?php

class CRM_CiviMobileAPI_BAO_AgendaConfig extends CRM_CiviMobileAPI_DAO_AgendaConfig {

  /**
   * Adds params
   *
   * @param $params
   *
   * @return \CRM_Core_DAO
   */
  public static function add(&$params) {
    $entity = new CRM_CiviMobileAPI_DAO_AgendaConfig();
    $entity->copyValues($params);
    return $entity->save();
  }

  /**
   * Creates new row
   *
   * @param $params
   *
   * @return \CRM_Core_DAO
   */
  public static function &create(&$params) {
    $transaction = new self();

    if (!empty($params['id'])) {
      CRM_Utils_Hook::pre('edit', self::getEntityName(), $params['id'], $params);
    }
    else {
      CRM_Utils_Hook::pre('create', self::getEntityName(), NULL, $params);
    }

    $entityData = self::add($params);

    if (is_a($entityData, 'CRM_Core_Error')) {
      $transaction->rollback();
      return $entityData;
    }

    $transaction->commit();

    if (!empty($params['id'])) {
      CRM_Utils_Hook::post('edit', self::getEntityName(), $entityData->id, $entityData);
    }
    else {
      CRM_Utils_Hook::post('create', self::getEntityName(), $entityData->id, $entityData);
    }

    return $entityData;
  }

  /**
   * Deletes row
   *
   * @param $id
   */
  public static function del($id) {
    $entity = new CRM_CiviMobileAPI_DAO_AgendaConfig();
    $entity->id = $id;
    $params = [];
    if ($entity->find(TRUE)) {
      CRM_Utils_Hook::pre('delete', self::getEntityName(), $entity->id, $params);
      $entity->delete();
      CRM_Utils_Hook::post('delete', self::getEntityName(), $entity->id, $entity);
    }
  }

  /**
   * Builds query for receiving data
   *
   * @param string $returnValue
   *
   * @return \CRM_Utils_SQL_Select
   */
  private static function buildSelectQuery($returnValue = 'rows') {
    $query = CRM_Utils_SQL_Select::from(CRM_CiviMobileAPI_DAO_AgendaConfig::getTableName());

    if ($returnValue == 'rows') {
      $query->select('
        id,
        event_id,
        is_active
      ');
    }
    else {
      if ($returnValue == 'count') {
        $query->select('COUNT(id)');
      }
    }

    return $query;
  }

  /**
   * Builds 'where' condition for query
   *
   * @param $query
   * @param array $params
   *
   * @return mixed
   */
  private static function buildWhereQuery($query, $params = []) {
    if (!empty($params['id'])) {
      $query->where('id = #id', ['id' => $params['id']]);
    }
    if (!empty($params['event_id'])) {
      $query->where('event_id = #event_id', ['event_id' => $params['event_id']]);
    }
    if (!empty($params['is_active'])) {
      $query->where('is_active = #is_active', ['is_active' => $params['is_active']]);
    }
    return $query;
  }

  /**
   * Gets all data
   *
   * @param array $params
   *
   * @return array
   */
  public static function getAll($params = []) {
    $query = self::buildWhereQuery(self::buildSelectQuery(), $params);
    return CRM_Core_DAO::executeQuery($query->toSQL())->fetchAll();
  }

}
