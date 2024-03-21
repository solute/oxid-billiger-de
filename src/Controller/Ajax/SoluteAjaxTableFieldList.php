<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Model\FieldDefinitionBase;

class SoluteAjaxTableFieldList extends FrontendController
{
    /**
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): string
    {
        $table = (string) Registry::get(Request::class)->getRequestParameter('table') ? : '';

        $response = [
            'tableList' => $this->getTableFieldList($table),
        ];
        echo json_encode($response);
        die;
    }

    /**
     * @param string $table
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getTableFieldList(string $table): array
    {
        $tableFields = new FieldDefinitionBase();
        return $tableFields->getTableList([$table]);
    }
}
