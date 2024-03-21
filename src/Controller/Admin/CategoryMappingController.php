<?php

namespace UnitM\Solute\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Application\Model\Category;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\MappingController;

class CategoryMappingController extends AdminController implements SoluteConfig
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'solute_category_mapping.tpl';

    /**
     * @var array
     */
    private array $resultMessage = [];

    /**
     * @var string
     */
    private string $sqlCache = '';

    /**
     * @var Category
     */
    private Category $category;

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function __construct()
    {
        $categoryOxid = Registry::get(Request::class)->getRequestParameter('oxid');
        $category = oxNew(Category::class);
        $category->load($categoryOxid);
        $this->category = $category;

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
     * @return string
     */
    public function getSoluteCategoryId(): string
    {
        return $this->category->getId() ?: '';
    }

    /**
     * @return array|null
     */
    public function getResultMessage()
    {
        if (empty($this->resultMessage)) {
            return null;
        } else {
            return $this->resultMessage;
        }
    }

    /**
     * @return string
     */
    public function getActionUrl(): string
    {
        $shopUrl = Registry::getUtilsUrl()->processUrl(Registry::getConfig()->getSslShopUrl()
            . 'admin/index.php', false);
        return $shopUrl . 'cl=CategoryMappingController';
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
        $data = $mappingController->getPostValues($attributeSchema, $shopId, $this->getSoluteCategoryId());
        $this->sqlCache .= $mappingController->createSqlDeleteOldData($shopId, $this->getSoluteCategoryId());
        $this->sqlCache .= $mappingController->saveNewData($data, $shopId, $this->getSoluteCategoryId());
        $this->resultMessage = $mappingController->executeTransaction($this->sqlCache);
    }
}
