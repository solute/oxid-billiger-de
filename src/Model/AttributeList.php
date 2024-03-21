<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\TableViewNameGenerator;
use UnitM\Solute\Core\SoluteConfig;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

class AttributeList extends AttributeList_parent implements SoluteConfig
{
    /**
     * Load attributes by article Id
     *
     * @param $sArticleId
     * @param $sParentId
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function loadAttributes($sArticleId, $sParentId = null)
    {
        if ($sArticleId) {
            $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
            $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);

            $sAttrViewName = $tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_ATTRIBUTE);
            $sViewName = $tableViewNameGenerator->getViewName(SoluteConfig::OX_TABLE_OBJECT2ATTRIBUTE);

            $sSelect = "select {$sAttrViewName}.`oxid`, {$sAttrViewName}.`oxtitle`, o2a.`oxvalue` from {$sViewName} as o2a ";
            $sSelect .= "left join {$sAttrViewName} on {$sAttrViewName}.oxid = o2a.oxattrid ";
            $sSelect .= "where o2a.oxobjectid = :oxobjectid and o2a.oxvalue != '' ";
            $sSelect .= "order by o2a.oxpos, {$sAttrViewName}.oxpos";

            $aAttributes = $oDb->getAll($sSelect, [
                ':oxobjectid' => $sArticleId
            ]);

            if ($sParentId) {
                $aParentAttributes = $oDb->getAll($sSelect, [
                    ':oxobjectid' => $sParentId
                ]);
                $aAttributes = $this->_mergeAttributes($aAttributes, $aParentAttributes);
            }

            $aAttributes = $this->filterListFromSoluteAttributes($aAttributes);
            $this->assignArray($aAttributes);
        }
    }

    /**
     * @param array $attributeList
     * @return array
     */
    private function filterListFromSoluteAttributes(array $attributeList) : array
    {
        if (empty($attributeList)) {
            return [];
        }

        $filteredList = [];
        foreach ($attributeList as $attribute) {
            if (mb_substr($attribute[SoluteConfig::OX_COL_TITLE], 0, 7) !== 'SOLUTE_') {
                $filteredList[] = $attribute;
            }
        }

        return $filteredList;
    }
}
