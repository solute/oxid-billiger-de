<?php

namespace UnitM\Solute\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\MappingController;

class ShopMappingController extends AdminController implements SoluteConfig
{
    /**
     * @var string
     */
    protected $_sThisTemplate = '@oxid_solute/templates/solute_shop_mapping';

    /**
     * @var array
     */
    private array $resultMessage = [];

    /**
     * @var string
     */
    private string $sqlCache = '';

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function __construct()
    {
        $saveTrigger = (bool) Registry::get(Request::class)->getRequestParameter('saveMapping');
        if ($saveTrigger) {
            $this->saveMapping();
        }

        parent::__construct();
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return Registry::getConfig()->getShopId();
    }

    /**
     * @return array|null
     */
    public function getResultMessage()
    {
        if (empty($this->resultMessage)) {
            return null;
        }

        return $this->resultMessage;
    }

    /**
     * @return string
     */
    public function getActionUrl(): string
    {
        $shopUrl = Registry::getUtilsUrl()
            ->processUrl(Registry::getConfig()->getSslShopUrl() . 'admin/index.php', false);
        return $shopUrl . 'cl=ShopMappingController';
    }

    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function saveMapping(): void
    {

        $mappingController = new MappingController();
        $attributeSchema = $mappingController->getAttributeList();
        $shopId = $this->getShopId();
        $data = $mappingController->getPostValues($attributeSchema, $shopId);
        $this->sqlCache .= $mappingController->createSqlDeleteOldData($shopId);
        $this->sqlCache .= $mappingController->saveNewData($data, $shopId);
        $this->resultMessage = $mappingController->executeTransaction($this->sqlCache);
    }
}
