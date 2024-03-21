<?php

namespace UnitM\Solute\Cron;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use UnitM\Solute\Controller\Ajax\AjaxBase;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\Hash;
use UnitM\Solute\Model\RestApi;
use UnitM\Solute\Model\mapData;
use UnitM\Solute\Service\ModuleSettingsInterface;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CronSendToApi
{
    /**
     * @var array
     */
    private array $argvList;

    /**
     * @var int
     */
    private int $shopId;

    /**
     * @var int
     */
    private int $languageId;

    /**
     * @var int
     */
    private int $requestLimit;

    /**
     * @var int
     */
    private int $articleCount;

    /**
     * @var Hash
     */
    private Hash $hash;

    /**
     * @var AjaxBase
     */
    private AjaxBase $ajaxBase;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../Core/SoluteConfig.php');
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsInterface::class);
        $this->requestLimit = $moduleSettings->getApiRequestLimit();
        $this->argvList = $this->getArgvList();
        $this->shopId = $this->getShopId();
        $this->languageId = $this->getLanguageId();
        $this->hash = new Hash();
        $this->ajaxBase = new AjaxBase();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    public function run(): void
    {
        $this->log('## Start cron script for shop-id ' . $this->shopId . ' #################');

        $this->articleCount = $this->ajaxBase->getArticleCount($this->shopId);
        $this->log('   ' . $this->articleCount . ' articles found for shop-id ' . $this->shopId);

        $maxLoops = ceil($this->articleCount / $this->requestLimit);

        for ($loopNumber = 0; $loopNumber < $maxLoops; $loopNumber++) {
           $this->proceedLoop($loopNumber);
        }

        $this->log('## Finished cron script for shop-id ' . $this->shopId . ' #################');
    }

    /**
     * @param int $loopNumber
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    private function proceedLoop(int $loopNumber): void
    {
        $countValidHash = 0;
        $countValidationError = 0;
        $countValidationSuccess = 0;
        $countChangedArticle = 0;

        $offset = $loopNumber * $this->requestLimit;
        $list = $this->ajaxBase->getArticleList($this->shopId, $this->requestLimit, $offset);

        $this->log('## Start loop ' . ($loopNumber + 1) . ' with ' . count($list) . ' items.');

        $batchData = [];
        $articleHashList = [];
        foreach ($list as $article) {
            if (empty($article[SoluteConfig::OX_COL_ID])) {
                $article[SoluteConfig::OX_COL_ID] = '';
            }
            if (empty($article[SoluteConfig::OX_COL_CATEGORY_ID])) {
                $article[SoluteConfig::OX_COL_CATEGORY_ID] = '';
            }

            // Validation
            $mapData = new mapData (
                $this->shopId,
                $this->languageId,
                $article[SoluteConfig::OX_COL_ID],
                $article[SoluteConfig::OX_COL_CATEGORY_ID],
                $this->ajaxBase->getAttributeSchema()
            );

            $result = $this->ajaxBase->executeValidation($mapData);

            // validation valid?
            if ($result['response']['result'] === false) {
                $countValidationError++;
                continue;
            }

            $countValidationSuccess++;

            // Is Hash value from validation equal hash value from db?
            if ($this->hash->existsHashValue($article[SoluteConfig::OX_COL_ID], $result['response']['valueHash'])) {
                unset ($batchData[$article[SoluteConfig::OX_COL_ID]]);
                unset ($articleHashList[$article[SoluteConfig::OX_COL_ID]]);
                $this->ajaxBase->logEvent(
                    true,
                    [],
                    Registry::getLang()->translateString('UMSOLUTE_LOG_API_NOTSEND'),
                    $article[SoluteConfig::OX_COL_ID],
                    SoluteConfig::UM_LOG_API_NOT_SEND
                );
                $countValidHash++;
                continue;
            }

            $batchData[$article[SoluteConfig::OX_COL_ID]] = $result['data'];
            $articleHashList[$article[SoluteConfig::OX_COL_ID]] = $result['response']['valueHash'];
            $countChangedArticle++;
        }

        $this->log('   ' . $countValidationError . ' of ' . count($list) . ' articles have a validation error.');
        $this->log('   ' . $countValidationSuccess . ' of ' . count($list) . ' articles passed the shop validation.');
        $message = '   ' . $countValidHash
            . ' articles, which passed the validation, have no changes and will not be transfered to API.';
        $this->log($message);

        if (empty($batchData)) {
            $this->log('   ' . 'No valid datas transfered in this loop.');
            return;
        }

        $this->log('   ' . $countChangedArticle . ' articles will be send to the API.');
        $this->sendToApi($batchData, $articleHashList);
        $this->log('## End loop ' . ($loopNumber + 1));
    }

    /**
     * @param array $batchData
     * @param array $articleHashList
     * @return void
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    private function sendToApi(array $batchData, array $articleHashList):void
    {
        $countApiError = 0;
        $countApiSuccess = 0;
        $countProcessedArticles = 0;

        $api = new RestApi();
        $api->setBatchData($batchData);
        $api->insert();
        $response = $this->ajaxBase->convertResponse($api->getResponse());

        foreach ($response as $articleId => $item) {
            $countProcessedArticles++;
            if ($item['result'] === false) {
                $this->hash->deleteHash($articleId);
                $countApiError++;
            } else {
                $this->hash->saveValue($articleId, $articleHashList[$articleId]);
                $countApiSuccess++;
            }
        }

        $this->hash->persist();
        $this->log('   ## API-RESPONSE');
        $this->log('   ' . $countProcessedArticles . ' articles processed.');
        $this->log('   ' . $countApiError . ' articles got an API error response.');
        $this->log('   ' . $countApiSuccess . ' articles were transfered valid to API.');
    }

    /**
     * @return array
     */
    private function getArgvList(): array
    {
        $argvList = [];
        $list = $_SERVER['argv'];
        if (empty($list)) {
            return [];
        }

        foreach ($list as $key => $parameter) {
            if ($key > 0) {
                $item = explode('=', $parameter);
                if (array_key_exists(1, $item)) {
                    $argvList[$item[0]] = $item[1];
                }
            }
        }

        return $argvList;
    }

    /**
     * @return int
     */
    private function getShopId(): int
    {
        if (
            array_key_exists(SoluteConfig::UM_ARGV_SHOPID, $this->argvList)
            && (int) $this->argvList[SoluteConfig::UM_ARGV_SHOPID] > 0
        ) {
            return (int) $this->argvList[SoluteConfig::UM_ARGV_SHOPID];
        }

        return 1;
    }

    /**
     * @return int
     */
    private function getLanguageId(): int
    {
        if (
            array_key_exists(SoluteConfig::UM_ARGV_LANGUAGEID, $this->argvList)
            && (int) $this->argvList[SoluteConfig::UM_ARGV_LANGUAGEID] > 0
        ) {
            return (int) $this->argvList[SoluteConfig::UM_ARGV_LANGUAGEID];
        }

        return 0;
    }

    /**
     * @param string $message
     * @return void
     */
    private function log(string $message): void
    {
        if (!empty($message)) {
            $date = '[' . date('Y-m-d H:i:s') . '] ';
            echo $date . $message . "\n";
        }
    }

    /**
     * @param string $serviceName
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getServiceFromContainer(string $serviceName): mixed
    {
        return ContainerFactory::getInstance()
            ->getContainer()
            ->get($serviceName);
    }
}

if (php_sapi_name() === 'cli') {
    require_once(__DIR__ . '/../../../../../source/bootstrap.php');
    $cron = new CronSendToApi();
    $cron->run();
}
