<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Model\AjaxMapping;

class SoluteAjaxCategoryMapping extends FrontendController
{
    /**
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): string
    {
        $shopId        = (int) Registry::get(Request::class)->getRequestParameter('shopId') ? : 1;
        $categoryId    = (string) Registry::get(Request::class)->getRequestParameter('categoryId') ? : '';

        if (empty($categoryId)) {
            return '';
        }

        $mapping = new AjaxMapping();

        $response = [
            'data'              => $mapping->getMappingList(),
            'fieldSelection'    => $mapping->getFieldSelection($shopId),
            'mapping'           => $mapping->getActualMapping($shopId, $categoryId),
            'translation'       => [
                'headline' => [
                    'identifier'  => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_MAPPING_LIST_IDENTIFIER'),
                    'mappinglist' => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_MAPPING_LIST_MAPPINGLIST'),
                    'manualvalue' => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_MAPPING_LIST_MANUALVALUE'),
                    'description' => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_MAPPING_LIST_DESCRIPTION'),
                ],
                'dropdown' => [
                    'dropdown_choose'           => Registry::getLang()->translateString('UMSOLUTE_DROPDOWN_CHOOSE'),
                    'dropdown_choose_no_map'    => Registry::getLang()->translateString('UMSOLUTE_DROPDOWN_CHOOSE_NO_MAP'),
                ],
            ]
        ];
        echo json_encode($response);
        die;
    }
}
