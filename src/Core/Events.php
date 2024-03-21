<?php

namespace UnitM\Solute\Core;

use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Core\CreateFieldSelection;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DbMetaDataHandler;

class Events implements SoluteConfig, SoluteCreateTableSql
{
    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public static function onActivate()
    {
        $database = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $shopIdList = self::getShopList($database);

        self::executeSql(self::createTables(), $database);
        self::addTablesToMultilangTables();
        self::executeSql(self::changeTables($database), $database);
        self::executeSql(self::setData($database, $shopIdList), $database);

        self::clearTmp();
        self::updateViews();
    }

    /**
     * @return void
     */
    private static function addTablesToMultilangTables(): void
    {
        $tables = [
            SoluteConfig::UM_TABLE_ATTRIBUTE_GROUPS,
            SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING,
            SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA,
            SoluteConfig::UM_TABLE_FIELD_SELECTION,
        ];

        $multiLanguageTables = Registry::getConfig()->getConfigParam('aMultiLangTables') ?? [];
        foreach ($tables as $table) {
            if (!array_key_exists($table, $multiLanguageTables)) {
                $multiLanguageTables[] = $table;
            }
        }

        Registry::getConfig()->saveShopConfVar(
            'arr',
            'aMultiLangTables',
            $multiLanguageTables,
            (string) Registry::getConfig()->getShopId()
        );
    }

    /**
     * @return string
     */
    private static function createTables(): string
    {
        $sqlCache = SoluteCreateTableSql::UM_SOLUTE_CREATE_TABLE_GROUPS;
        $sqlCache .= SoluteCreateTableSql::UM_SOLUTE_CREATE_TABLE_SCHEMA;
        $sqlCache .= SoluteCreateTableSql::UM_SOLUTE_CREATE_TABLE_MAPPING;
        $sqlCache .= SoluteCreateTableSql::UM_SOLUTE_CREATE_TABLE_FIELD_SELECTION;
        $sqlCache .= SoluteCreateTableSql::UM_SOLUTE_CREATE_TABLE_LOG;
        $sqlCache .= SoluteCreateTableSql::UM_SOLUTE_CREATE_TABLE_HASH;

        return $sqlCache;
    }

    /**
     * @param DatabaseInterface $database
     * @return string
     * @throws DatabaseErrorException
     */
    private static function changeTables(DatabaseInterface $database): string
    {
        $sqlCache = self::addFieldToTable(
            $database,
            SoluteConfig::OX_TABLE_ATTRIBUTE,
            SoluteConfig::UM_COL_VISIBILITY,
            SoluteConfig::UM_TYPE_BOOL,
            'hide these attributes from oxid standard',
            'TRUE'
        );
        $sqlCache .= self::addFieldToTable(
            $database,
            SoluteConfig::OX_TABLE_ORDER,
            SoluteConfig::UM_COL_OID,
            SoluteConfig::UM_TYPE_VARCHAR . '(12)',
            'tracking solute order id',
            ''
        );
        return $sqlCache;
    }

    /**
     * @param DatabaseInterface $database
     * @param string $table
     * @param string $field
     * @param string $type
     * @param string $comment
     * @param string $default
     * @return string
     * @throws DatabaseErrorException
     */
    private static function addFieldToTable(
        DatabaseInterface $database,
        string $table,
        string $field,
        string $type,
        string $comment,
        string $default
    ): string
    {
        if (empty($table) || empty($field) || empty($type)) {
            return '';
        }

        $check = "
            SELECT * 
            FROM `information_schema`.`COLUMNS` 
            WHERE `TABLE_SCHEMA` = '" . Registry::getConfig()->getConfigParam('dbName') . "' 
              AND `TABLE_NAME` = '" . $table . "' 
              AND `COLUMN_NAME` = '" . $field . "';";

        if (count($database->getAll($check)) !== 0) {
            return '';
        }

        $sql = "ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $type . " NOT NULL ";
        if (!empty($default)) {
            $sql .= "DEFAULT " . $default . " ";
        }
        if (!empty($comment)) {
            $sql .= "COMMENT '" . $comment . "'";
        }
        $sql .= ";";

        return $sql;
    }

    /**
     * @param DatabaseInterface $database
     * @param array $shopIdList
     * @return string
     */
    private static function setData(DatabaseInterface $database, array $shopIdList): string
    {
        $createGroup = new CreateGroup($database);
        $sqlCache = $createGroup->run();

        $createSchema = new CreateSchema($database, $shopIdList);
        $sqlCache .= $createSchema->run();

        $createFieldSelection = new CreateFieldSelection($database);
        $sqlCache .= $createFieldSelection->run();

        $createStandardMapping = new CreateStandardMapping($database, $shopIdList);
        $sqlCache .= $createStandardMapping->run();

        return $sqlCache;
    }

    /**
     * @param string $sql
     * @param DatabaseInterface $database
     * @return void
     * @throws DatabaseErrorException
     */
    private static function executeSql(string $sql, DatabaseInterface $database): void
    {
        if (empty($sql)) {
            return;
        }

        $sqlCache = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO"; START TRANSACTION;';
        $sqlCache .= $sql;
        $sqlCache .= 'COMMIT;';

        $database->execute($sqlCache);
    }

    /**
     * @param DatabaseInterface $database
     * @return array
     * @throws DatabaseErrorException
     */
    private static function getShopList(DatabaseInterface $database): array
    {
        $query = "SELECT `" . SoluteConfig::OX_COL_ID . "` FROM `" . SoluteConfig::OX_TABLE_SHOPS . "`;";
        $resultList = $database->getAll($query);

        return $resultList[0];
    }

    /**
     * @return void
     */
    private static function clearTmp(): void
    {
        $pattern = Registry::getConfig()->getConfigParam("sCompileDir") . "*";
        $path[]  = $pattern;

        while (count($path) !== 0) {
            $dir = array_shift($path);
            foreach (glob($dir) as $item) {
                if (is_file($item)) {
                    unlink($item);
                }
            }
        }
    }

    /**
     * @return void
     */
    private static function updateViews(): void
    {
        $metaData = oxNew(DbMetaDataHandler::class);
        $metaData->updateViews();
    }

    /**
     * @return void
     */
    public static function onDeactivate()
    {

        $tables = [
            SoluteConfig::UM_TABLE_ATTRIBUTE_GROUPS,
            SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING,
            SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA,
            SoluteConfig::UM_TABLE_FIELD_SELECTION
        ];

        $multiLanguageTables = Registry::getConfig()->getConfigParam('aMultiLangTables') ?? [];
        foreach ($tables as $table) {
            if (array_key_exists($table, $multiLanguageTables)) {
                unset($multiLanguageTables[$table]);
            }
        }

        Registry::getConfig()->saveShopConfVar(
            'arr',
            'aMultiLangTables',
            $multiLanguageTables,
            Registry::getConfig()->getShopId()
        );

        self::clearTmp();
        self::updateViews();
    }
}
