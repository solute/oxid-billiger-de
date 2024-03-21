<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\Logger;
use UnitM\Solute\Model\mapData;
use UnitM\Solute\Model\RestApi;
use UnitM\Solute\Model\Hash;
use UnitM\Solute\Service\ModuleSettingsInterface;
use UnitM\Solute\Traits\ServiceContainer;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SoluteAjaxSendToApi extends FrontendController
{
    use ServiceContainer;

    /**
     * @var AjaxBase
     */
    private AjaxBase $ajaxBase;

    /**
     * @var Hash
     */
    private Hash $hash;

    /**
     * @var bool
     */
    private bool $isDebug;

    /**
     *
     */
    public function __construct() {
        $this->ajaxBase = new AjaxBase();
        $this->hash = new Hash();
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsInterface::class);
        $this->isDebug = $moduleSettings->getDebugMode();

        parent::__construct();
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    public function run(): string
    {
        $shopId = (int) Registry::get(Request::class)->getRequestParameter('shopId') ? : 1;
        $languageId = (int) Registry::get(Request::class)->getRequestParameter('languageId') ? : 1;
        $articleListJson = (string) Registry::get(Request::class)->getRequestParameter('articleList') ? : '';
        $articleListRaw = json_decode($articleListJson, true);

        $batchData = [];
        $attributeSchema = $this->ajaxBase->getAttributeSchema();

        foreach ($articleListRaw['data'] as $item) {
            if ($this->hash->existsHashValue(
                $item[SoluteConfig::UM_AJAX_ARTILCEID],
                $item[SoluteConfig::UM_AJAX_FEED_HASH])
            ) {
                if ($this->isDebug) {
                    $message = 'Feed for article ' . $item[SoluteConfig::UM_AJAX_ARTILCEID]
                        . ' not send, because no values has changed since last commit.';
                    Logger::addLog($message, SoluteConfig::UM_LOG_INFO);
                }

                $batchData[$item[SoluteConfig::UM_AJAX_ARTILCEID]] = [];
            } else {
                $mapData = new mapData(
                    $shopId,
                    $languageId,
                    $item[SoluteConfig::UM_AJAX_ARTILCEID],
                    $item[SoluteConfig::UM_AJAX_CATEGORYID],
                    $attributeSchema
                );

                $batchData[$item[SoluteConfig::UM_AJAX_ARTILCEID]] = $mapData->getData();
            }
        }

        if (empty($batchData)) {
            $message = 'No batch data given. Propably the article list was empty.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            echo '';
            die;
        }

        $api = new RestApi();
        $api->setBatchData($batchData);
        $api->insert();

        $response = $this->ajaxBase->convertResponse($api->getResponse());
        $this->hash->saveHashesForValidSendArticles($response, $articleListRaw['data']);
        $this->hash->persist();
        echo json_encode($response);
        die;
    }
}
