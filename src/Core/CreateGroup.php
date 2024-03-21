<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use UnitM\Solute\Core\EventTrait;

class CreateGroup implements SoluteConfig
{
    use EventTrait;

    /**
     * @var Database
     */
    private DatabaseInterface $database;

    /**
     * @var string
     */
    private string $sqlCache;

    /**
     * @param DatabaseInterface $database
     */
    public function __construct(
        DatabaseInterface $database
    ) {
        $this->database = $database;
        $this->sqlCache = '';
    }

    /**
     * @return string
     */
    public function run(): string
    {
        $this->sqlCache = $this->createSql($this->getDefinitionList(), $this->database);
        return $this->sqlCache;
    }

    /**
     * @param array $valueList
     * @param DatabaseInterface $database
     * @return string
     */
    private function createSql(array $valueList, DatabaseInterface $database): string
    {
        if (empty($valueList)) {
            return '';
        }

        $i = 1;
        $values = [];
        foreach ($valueList as $item) {
            $row = [
                SoluteConfig::OX_COL_ID     => md5($item),
                SoluteConfig::UM_COL_SORT   => $i * 10,
                SoluteConfig::UM_COL_TITLE  => $item
            ];
            $values[] = $row;
            $i++;
        }

        return $this->getSqlForInsertFromDataArray($values, SoluteConfig::UM_TABLE_ATTRIBUTE_GROUPS, $database);
    }

    /**
     * @return string[]
     */
    private function getDefinitionList(): array
    {
        return [
            SoluteConfig::UM_ATTR_GROUP_COMMON,
            SoluteConfig::UM_ATTR_GROUP_PRICE,
            SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
            SoluteConfig::UM_ATTR_GROUP_DELIVERY,
            SoluteConfig::UM_ATTR_GROUP_CATEGORY,
            SoluteConfig::UM_ATTR_GROUP_VOUCHER,
            SoluteConfig::UM_ATTR_GROUP_ENERGY,
            SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
            SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
            SoluteConfig::UM_ATTR_GROUP_MEDICAL,
            SoluteConfig::UM_ATTR_GROUP_WHEELS,
            SoluteConfig::UM_ATTR_GROUP_CAR,
            SoluteConfig::UM_ATTR_GROUP_EYELENS
        ];
    }
}
