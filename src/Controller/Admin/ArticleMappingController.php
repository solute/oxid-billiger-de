<?php

namespace UnitM\Solute\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Application\Model\Article;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\MappingController;

class ArticleMappingController extends AdminController implements SoluteConfig
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'solute_article_mapping.tpl';

    /**
     * @var array
     */
    private array $resultMessage = [];

    /**
     * @var string
     */
    private string $sqlCache = '';

    /**
     * @var Article
     */
    private Article $article;

    /**
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function __construct()
    {
        $articleOxid = Registry::get(Request::class)->getRequestParameter('oxid');
        $article = oxNew(Article::class);
        $article->load($articleOxid);
        $this->article = $article;

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
    public function getArticleNumber(): string
    {
        return $this->article->oxarticles__oxartnum->value ?: '';
    }

    /**
     * @return string
     */
    public function getArticleId(): string
    {
        return $this->article->getId() ?: '';
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
        return $shopUrl . 'cl=ArticleMappingController';
    }

    /**
     * @return void
     * @throws DatabaseErrorException
     */
    private function saveMapping(): void
    {
        $mappingController = new MappingController();
        $attributeSchema = $mappingController->getAttributeList();
        $shopId = $this->getShopId();
        $data = $mappingController->getPostValues($attributeSchema, $shopId, $this->getArticleId());
        $this->sqlCache .= $mappingController->createSqlDeleteOldData($shopId, $this->getArticleId());
        $this->sqlCache .= $mappingController->saveNewData($data, $shopId, $this->getArticleId());
        $this->resultMessage = $mappingController->executeTransaction($this->sqlCache);
    }
}
