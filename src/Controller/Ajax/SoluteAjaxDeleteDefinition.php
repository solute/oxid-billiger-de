<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;

class SoluteAjaxDeleteDefinition extends FrontendController
{
    /**
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): string
    {
        $objectId = Registry::get(Request::class)->getRequestParameter('objectId');

        $this->deleteRow($objectId);

        $resultMessage = [];

        echo json_encode($resultMessage);
        die;
    }

    /**
     * @param string $objectId
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function deleteRow(string $objectId): void
    {
        if (empty($objectId)) {
            return;
        }

        $delete = "DELETE FROM `" . SoluteConfig::UM_TABLE_FIELD_SELECTION . "` WHERE `" . SoluteConfig::OX_COL_ID
            . "` = '" . $objectId . "'";
        DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->execute($delete);
    }
}
