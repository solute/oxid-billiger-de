<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use UnitM\Solute\Model\DataRessource;


class SoluteAjaxSaveDefinition extends FrontendController
{
    /**
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): string
    {
        $dataRessource = new DataRessource();
        $dataRessource->getPostValues();
        $dataRessource->save();

        $resultMessage = [
            'result' => true
        ];

        echo json_encode($resultMessage);
        die;
    }
}
