<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Model\FieldDefinitionBase;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

class SoluteAjaxTableFieldList extends FrontendController
{
    /**
     *
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../Core/SoluteConfig.php');

        parent::__construct();
    }

    /**
     * @return string
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
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
