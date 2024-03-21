<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;

class AjaxMapping
{
    /**
     * @var string
     */
    private string $tableNameAttributeGroups;

    /**
     * @var TableViewNameGenerator
     */
    private TableViewNameGenerator $tableViewNameGenerator;

    /**
     *
     */
    public function __Construct()
    {
        require_once(__DIR__ . '/../Core/SoluteConfig.php');
        $this->tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $this->tableNameAttributeGroups = $this->tableViewNameGenerator
            ->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_GROUPS);
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getMappingList(): array
    {
        $tableNameAttributeSchema = $this->tableViewNameGenerator
            ->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA);

        $select = "
            SELECT 
                `ag`.`" . SoluteConfig::UM_COL_TITLE . "`,
                `as`.`" . SoluteConfig::OX_COL_ID . "`,
                `as`.`" . SoluteConfig::UM_COL_PRIMARY_NAME  . "`,
                `as`.`" . SoluteConfig::UM_COL_DESCRIPTION  . "`,
                `as`.`" . SoluteConfig::UM_COL_VALID_VALUES . "`,
                `as`.`" . SoluteConfig::UM_COL_REQUIRED . "`
            FROM
                `" . $tableNameAttributeSchema . "` AS `as`,
                `" . $this->tableNameAttributeGroups . "` AS `ag`
            WHERE
                `as`.`" . SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID . "` = `ag`.`" . SoluteConfig::OX_COL_ID . "`
            ORDER BY
                `ag`.`" . SoluteConfig::UM_COL_SORT . "`, `as`.`" . SoluteConfig::UM_COL_SORT . "`;
        ";

        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);

        foreach ($result as $key => $row) {
            if ($row[SoluteConfig::UM_COL_VALID_VALUES] !== '[]') {
                $result[$key][SoluteConfig::UM_COL_VALID_VALUES]
                    = json_decode($row[SoluteConfig::UM_COL_VALID_VALUES], true);
            } else {
                $result[$key][SoluteConfig::UM_COL_VALID_VALUES] = '';
            }
        }

        return $result;
    }

    /**
     * @param int $shopId
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getFieldSelection(int $shopId): array
    {
        if (empty($shopId)) {
            return [];
        }

        $tableFieldSelection = $this->tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_FIELD_SELECTION);
        $select = "
            SELECT
                `ag`.`" . SoluteConfig::UM_COL_TITLE . "`,
                `fs`.`" . SoluteConfig::OX_COL_ID . "`,
                `fs`.`" . SoluteConfig::UM_COL_FIELD_TITLE . "`,
                `fs`.`" . SoluteConfig::UM_COL_DATA_RESSOURCE . "`
            FROM
                `" . $tableFieldSelection . "` AS `fs`,
                `" . $this->tableNameAttributeGroups . "` AS `ag`
            WHERE
                    `fs`.`" . SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID . "` = `ag`.`" . SoluteConfig::OX_COL_ID . "`
                AND `fs`.`" . SoluteConfig::UM_COL_SHOP_ID . "` = '" . $shopId . "'
        ";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);

        $list = [];
        foreach ($result as $row) {
            $list[$row[SoluteConfig::UM_COL_TITLE]][$row[SoluteConfig::UM_COL_FIELD_TITLE]] =
                [
                    SoluteConfig::UM_COL_TITLE          => $row[SoluteConfig::UM_COL_TITLE],
                    SoluteConfig::OX_COL_ID             => $row[SoluteConfig::OX_COL_ID],
                    SoluteConfig::UM_COL_FIELD_TITLE    => $row[SoluteConfig::UM_COL_FIELD_TITLE],
                    SoluteConfig::UM_COL_DATA_RESSOURCE => json_decode($row[SoluteConfig::UM_COL_DATA_RESSOURCE], true),
                ];
        }

        return $list;
    }

    /**
     * @param int $shopId
     * @param string $object
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getActualMapping(int $shopId, string $object = ''): array
    {
        $tableNameAttributeMapping = $this->tableViewNameGenerator
            ->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING);

        $select = "
            SELECT
                `" . SoluteConfig::OX_COL_ID . "`,
                `" . SoluteConfig::UM_COL_ATTRIBUTE_ID . "`,
                `" . SoluteConfig::UM_COL_DATA_RESSOURCE_ID . "`,
                `" . SoluteConfig::UM_COL_MANUAL_VALUE . "`
            FROM
                `" . $tableNameAttributeMapping . "`
            WHERE
                    `" . SoluteConfig::UM_COL_SHOP_ID . "` = '" . $shopId . "'
                AND `" . SoluteConfig::UM_COL_OBJECT_ID . "` = '" . $object . "' 
        ";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);

        $list = [];
        foreach ($result as $row) {
            $list[$row[SoluteConfig::UM_COL_ATTRIBUTE_ID]] = [
                SoluteConfig::OX_COL_ID => $row[SoluteConfig::OX_COL_ID],
                SoluteConfig::UM_COL_DATA_RESSOURCE_ID => $row[SoluteConfig::UM_COL_DATA_RESSOURCE_ID],
                SoluteConfig::UM_COL_MANUAL_VALUE => $row[SoluteConfig::UM_COL_MANUAL_VALUE]
            ];
        }

        return $list;
    }
}
