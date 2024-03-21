<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\mapData;
use UnitM\Solute\Model\Logger;
use UnitM\Solute\Service\ModuleSettingsInterface;
use UnitM\Solute\Traits\ServiceContainer;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;

class SoluteAjaxValidateArticle extends FrontendController
{
    use ServiceContainer;

    /**
     * @var bool
     */
    private bool $isDebug;

    /**
     *
     */
    public function __Construct()
    {
        require_once(__DIR__ . '/../../Core/SoluteConfig.php');
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsInterface::class);
        $this->isDebug = $moduleSettings->getDebugMode();

        parent::__construct();
    }

    /**
     * @return string
     * @throws DatabaseErrorException
     * @throws DatabaseConnectionException
     */
    public function run(): string
    {
        $shopId = (int) Registry::get(Request::class)->getRequestParameter('shopId') ? : 1;
        $languageId = (int) Registry::get(Request::class)->getRequestParameter('languageId') ? : 1;
        $articleId = (string) Registry::get(Request::class)->getRequestParameter('articleId') ? : '';
        $categoryId = (string) Registry::get(Request::class)->getRequestParameter('categoryId') ? : '';

        if ($this->isDebug) {
            $timeStart = microtime(true);
            $logData = [
                'shopId' => $shopId,
                'languageId' => $languageId,
                'articleId' => $articleId,
                'categoryId' => $categoryId
            ];
            Logger::addLog('Start validation.', SoluteConfig::UM_LOG_INFO, $logData);
        }

        if (empty($articleId) || empty($categoryId)) {
            $response = [
                'result' => false,
                'errorLog' => 'ArticleId or CategoryId is empty. | ' . json_encode($articleId) . ' | '
                    . json_encode($categoryId),
            ];
            echo json_encode($response);
            die;
        }

        $ajaxBase = new AjaxBase();

        $mapData = new mapData (
            $shopId,
            $languageId,
            $articleId,
            $categoryId,
            $ajaxBase->getAttributeSchema()
        );
        $result = $ajaxBase->executeValidation($mapData);
        $response = $result['response'];
        $data = $result['data'];

        if ($this->isDebug) {
            $timeEnd = microtime(true);
            $timeExecution = $timeEnd - $timeStart;

            if ($response['result'] === true) {
                Logger::addLog('Result: Valid.', SoluteConfig::UM_LOG_INFO, $data);
            } else {
                $message = 'Validation failed. Error log: ' . PHP_EOL . '                              '
                    . str_replace("<br />", PHP_EOL . '                              ', $response['errorLog']);
                Logger::addLog($message, SoluteConfig::UM_LOG_ERROR, $data);
            }

            $messsage = 'Validation finished. Execution time: ' . number_format($timeExecution, 4) . 's.';
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO, $logData);
        }
        echo json_encode($response);
        die;
    }
}
