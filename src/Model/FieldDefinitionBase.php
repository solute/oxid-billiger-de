<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;

class FieldDefinitionBase
{
    /**
     * @param array $tableList
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getTableList(array $tableList): array
    {
        $list = [];
        foreach ($tableList as $table) {
            $list[] = [
                'table' => $table,
                'fields' => $this->getFieldList($table)
            ];
        }
        return $list;
    }

    /**
     * @param string $table
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getFieldList(string $table): array
    {
        if (empty($table)) {
            return [];
        }

        $select = "SELECT `COLUMN_NAME` FROM `information_schema`.`COLUMNS` WHERE `TABLE_NAME` =  '" . $table
            . "' ORDER BY `COLUMN_NAME`";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
        $list = [];
        foreach ($result as $row) {
            if ($this->isTranslationColumn($row['COLUMN_NAME'])) {
                continue;
            }

            $list[] = [
                'title' => $row['COLUMN_NAME'],
                'value' => $row['COLUMN_NAME'],
            ];
        }

        return $list;
    }

    /**
     * @param string $string
     * @return bool
     */
    private function isTranslationColumn(string $string): bool
    {
        $parts = explode('_', $string);
        $end = end($parts);
        $int = (int) $end;
        if ($int > 0 && strlen($int) === strlen($end)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getAttributeLabelList(): array
    {
        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $tableNameOxattribute = $tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_ATTRIBUTE);

        $select = "
            SELECT 
                `" . SoluteConfig::OX_COL_TITLE . "` 
            FROM 
                `" . $tableNameOxattribute . "`
            ORDER BY 
                `" . SoluteConfig::OX_COL_TITLE . "`;
                ";
        $result = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
        $list = [];
        foreach ($result as $row) {
            $list[] = [
                'title' => $row[SoluteConfig::OX_COL_TITLE],
                'value' => $row[SoluteConfig::OX_COL_TITLE]
            ];
        }

        return $list;
    }
}
