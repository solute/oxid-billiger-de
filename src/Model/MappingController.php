<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;

class MappingController
{
    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getAttributeList(): array
    {
        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $tableNameAttributeSchema = $tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA);

        $select = "
            SELECT
                `" . SoluteConfig::OX_COL_ID . "`,
                `" . SoluteConfig::UM_COL_PRIMARY_NAME . "`
            FROM
                `" . $tableNameAttributeSchema . "`
        ";
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
    }

    /**
     * @param int $shopId
     * @param string $objectId
     * @return string
     */
    public function createSqlDeleteOldData(int $shopId, string $objectId = ''): string
    {
        return "
            DELETE FROM `" . SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING . "`
            WHERE 
                    `" . SoluteConfig::UM_COL_OBJECT_ID . "` = '" . $objectId . "'
                AND `" . SoluteConfig::UM_COL_SHOP_ID . "` = '" . $shopId . "';
        ";
    }

    /**
     * @param array $attributeSchema
     * @param int $shopId
     * @param string $objectId
     * @return array
     */
    public function getPostValues(array $attributeSchema, int $shopId, string $objectId = ''): array
    {
        $data = [];
        foreach ($attributeSchema as $attribute) {
            $valueName = $attribute[SoluteConfig::UM_COL_PRIMARY_NAME]; // . '_manualValue';
            $valueManual = Registry::get(Request::class)->getRequestParameter($valueName . '_manualValue');
            $valueRessourceId = Registry::get(Request::class)->getRequestParameter($valueName);

            if (empty($valueManual) && empty($valueRessourceId)) {
                continue;
            }

            $data[$valueName] = [
                SoluteConfig::UM_COL_ATTRIBUTE_ID       => $attribute[SoluteConfig::OX_COL_ID],
                SoluteConfig::UM_COL_DATA_RESSOURCE_ID  => $valueRessourceId,
                SoluteConfig::UM_COL_MANUAL_VALUE       => $valueManual,
                SoluteConfig::UM_COL_SHOP_ID            => $shopId,
                SoluteConfig::UM_COL_OBJECT_ID          => $objectId,
            ];
        }

        return $data;
    }

    /**
     * @param array $data
     * @param int $shopId
     * @param string $objectId
     * @return string
     */
    public function saveNewData(array $data, int $shopId, string $objectId = ''): string
    {
        $sqlCache = 'INSERT INTO `' . SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING . '` (';
        $sqlCache .= '`' . SoluteConfig::OX_COL_ID . '`,';
        $sqlCache .= '`' . SoluteConfig::UM_COL_OBJECT_ID . '`,';
        $sqlCache .= '`' . SoluteConfig::UM_COL_SHOP_ID . '`,';
        $sqlCache .= '`' . SoluteConfig::UM_COL_ATTRIBUTE_ID . '`,';
        $sqlCache .= '`' . SoluteConfig::UM_COL_DATA_RESSOURCE_ID . '`,';
        $sqlCache .= '`' . SoluteConfig::UM_COL_MANUAL_VALUE . '`';
        $sqlCache .= ') VALUES ';
        $insert = '';
        foreach ($data as $row) {
            $oxid = $this->getAttributeMappingId(
                $shopId,
                $row[SoluteConfig::UM_COL_ATTRIBUTE_ID],
                $row[SoluteConfig::UM_COL_DATA_RESSOURCE_ID],
                $objectId
            );
            if (!empty($insert)) {
                $insert .= ',';
            }
            $insert .= "(";
            $insert .= "'" . $oxid . "',";
            $insert .= "'" . $row[SoluteConfig::UM_COL_OBJECT_ID] . "',";
            $insert .= "'" . $shopId . "',";
            $insert .= "'" . $row[SoluteConfig::UM_COL_ATTRIBUTE_ID] . "',";
            $insert .= "'" . $row[SoluteConfig::UM_COL_DATA_RESSOURCE_ID] . "',";
            $insert .= "'" . $row[SoluteConfig::UM_COL_MANUAL_VALUE] . "'";
            $insert .= ")";
        }
        $sqlCache .= $insert . ';';

        return $sqlCache;
    }

    /**
     * @param string $sql
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function executeTransaction(string $sql): array
    {
        if (empty($sql)) {
            return [];
        }
        $transaction = 'START TRANSACTION;';
        $transaction .= $sql;
        $transaction .= 'COMMIT;';

        try {
            DatabaseProvider::getDb()->execute($transaction);
        } catch (DatabaseErrorException $exception) {
            DatabaseProvider::getDb()->execute('ROLLBACK;');
            return [
                'result' => false,
                'message' => 'Fehler beim Speichern der Daten. | ' . $exception->getMessage(),
            ];
        }

        return [
            'result' => true,
            'message' => 'Daten gespeichert.'
        ];
    }

    /**
     * @param int $shopId
     * @param string $attributeId
     * @param string $dataRessource
     * @param string $objectId
     * @return string
     */
    public function getAttributeMappingId(
        int $shopId,
        string $attributeId,
        string $dataRessource,
        string $objectId = ''
    ): string
    {
        if (empty($shopId) || empty($attributeId)) {
            $data = [
                'shopId' => $shopId,
                'attributeId' => $attributeId,
                'dataRessource' => $dataRessource,
                'objectId' => $objectId
            ];
            $message = get_class($this) . '->' . __FUNCTION__ . '(' . __LINE__ . '): Missing value(s).';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR, $data);
            echo "FATAL ERROR. Stop execution. See solute logfile.";
            die;
        }
        return md5($shopId . $attributeId . $dataRessource . $objectId);
    }
}
