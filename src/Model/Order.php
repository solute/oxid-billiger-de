<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\SoluteSession;
use UnitM\Solute\Model\RestApi;
use OxidEsales\Eshop\Application\Model\Basket;
use Exception;

class Order extends Order_parent implements SoluteConfig
{
    /**
     * @param Basket $oBasket
     * @param $oUser
     * @param $blRecalculatingOrder
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false): mixed
    {
        $iRet = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
        /*
        * 3: error order already executed
        * 2: error payment
        * 1: OK
        * 0: error on send email but order is valid executed
        */
        if ($iRet < 2) {
            $this->setSoluteData();
        }
        return $iRet;
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function setSoluteData(): void
    {
        $session = new SoluteSession();
        if ($session->getSoluteDataFromSession()) {
            $oid = $this->createOid();
            $this->sendDataToSolute($session, $oid);
            $session->removeSoluteDataFromSession();
            $this->saveOid($oid);
        }
    }

    /**
     * @param string $oid
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function saveOid(string $oid): void
    {
        $orderOxid = $this->getId();
        $update = "
            UPDATE `" . SoluteConfig::OX_TABLE_ORDER . "` 
            SET `" . SoluteConfig::UM_COL_OID . "` = '" . $oid . "' 
            WHERE `" . SoluteConfig::OX_COL_ID . "` = '" . $orderOxid . "'";
        DatabaseProvider::getDb()->execute($update);
    }

    /**
     * @param SoluteSession $session
     * @param string $oid
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function sendDataToSolute(SoluteSession $session, string $oid): void
    {
        $netOrderValue = (float) $this->oxorder__oxtotalnetsum->value;
        $restApi = new RestApi();
        $restApi->sendConversion($session->url, $netOrderValue, $oid);
    }

    /**
     * @return string
     * @throws Exception
     */
    private function createOid(): string
    {
        $oid = '';
        for ($i = 0; $i < 12; $i++) {
            $oid .= chr($this->getRandInt());
        }

        return $oid;
    }

    /**
     * @return int
     * @throws Exception
     */
    private function getRandInt(): int
    {
        $chr = 0;
        while ($chr === 0 || ($chr > 57 && $chr < 65)) {
            $chr = random_int(48, 90);
        }
        return $chr;
    }
}
