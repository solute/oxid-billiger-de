<?php

namespace UnitM\Solute\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;

class FieldDefinitionController extends AdminController implements SoluteConfig
{
    /**
     * @var string
     */
    protected $_sThisTemplate = '@oxid_solute/templates/solute_field_definition_list';

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function getFieldSelection(): array
    {
        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $tableNameFieldSelection = $tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_FIELD_SELECTION);
        $tableNameAttributeGroups = $tableViewNameGenerator->getViewName(SoluteConfig::UM_TABLE_ATTRIBUTE_GROUPS);

        $select = "
            SELECT 
                `ag`.`" . SoluteConfig::UM_COL_TITLE . "`,
                `fs`.*
            FROM 
                `" . $tableNameFieldSelection . "` as `fs`,
                `" . $tableNameAttributeGroups . "` as `ag` 
             WHERE
                    `fs`.`" . SoluteConfig::UM_COL_SHOP_ID . "` = '" . $this->getShopId() . "' 
                AND `fs`.`" . SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID . "` = `ag`.`" . SoluteConfig::OX_COL_ID . "`
            ORDER BY 
                `ag`.`" . SoluteConfig::UM_COL_SORT . "`, `" . SoluteConfig::UM_COL_FIELD_TITLE . "`
                ";

        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($select);
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return Registry::getConfig()->getShopId();
    }
}
