<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Model\Logger;
use UnitM\Solute\Model\MappingController;
use UnitM\Solute\Core\SoluteConfig;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

class SoluteAjaxSaveMapping extends FrontendController
{
    /**
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): string
    {
        $mappingController = new MappingController();
        $attributeSchema = $mappingController->getAttributeList();
        $shopId = (int) Registry::get(Request::class)->getRequestParameter('SoluteModuleShopId');

        $object = (string) Registry::get(Request::class)->getRequestParameter('SoluteObject');
        if ($object === 'article') {
            $objectId = Registry::get(Request::class)->getRequestParameter('SoluteArticleId');
        } elseif ($object === 'category') {
            $objectId = Registry::get(Request::class)->getRequestParameter('SoluteCategoryId');
        }

        if (empty($objectId)) {
            Logger::addLog('No object id found.', SoluteConfig::UM_LOG_ERROR);
            die;
        }

        $sqlCache = $mappingController->createSqlDeleteOldData($shopId, $objectId);

        $data = $mappingController->getPostValues($attributeSchema, $shopId, $objectId);
        if (!empty($data)) {
            $sqlCache .= $mappingController->saveNewData($data, $shopId, $objectId);
        }

        $resultMessage = $mappingController->executeTransaction($sqlCache);

        echo json_encode($resultMessage);
        die;
    }
}
