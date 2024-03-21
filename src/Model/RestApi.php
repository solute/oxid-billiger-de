<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\ShopVersion;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Core\RestConfig;
use UnitM\Solute\Model\Logger;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Facts\Facts;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use UnitM\Solute\Service\ModuleSettingsInterface;
use UnitM\Solute\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Exception;

class RestApi implements SoluteConfig, RestConfig
{
    use ServiceContainer;

    private const REST_GET                  = 'GET';
    private const REST_PUT                  = 'PUT';

    private const OPERATION_INSERT          = 'insert';
    private const OPERATION_GET             = 'get';
    private const OPERATION_DELETE          = 'delete';

    public const REST_STATE_HTTP_CODE       = 'http_code';
    public const STATE_NO_CONTENT           = 204;

    /**
     * @var string
     */
    private string $referer;

    /**
     * @var bool
     */
    private bool $isDebug;

    /**
     * @var string
     */
    private string $baererToken;

    /**
     * @var string
     */
    private string $soluteShopId;

    /**
     * @var string
     */
    private string $soluteFeedId;

    /**
     * @var string
     */
    private string $endpoint;

    /**
     * @var array
     */
    private array $response;

    /**
     * @var array
     */
    private array $responseInfo;

    /**
     * @var array
     */
    private array $headers;

    /**
     * @var array
     */
    private array $options;

    /**
     * @var array
     */
    private array $batchData;

    /**
     * @var array
     */
    private array $offers;

    /**
     * @var string
     */
    private string $currency;

    /**
     * @var string
     */
    private string $language;

    /**
     * @var string
     */
    private string $country;

    /**
     * @var mixed
     */
    private $moduleSettings;

    /**
     *
     */
    public function __construct()
    {
        $this->referer = Registry::getConfig()->getShopURL();

        $this->moduleSettings = $this->getServiceFromContainer(ModuleSettingsInterface::class);
        $this->isDebug = $this->moduleSettings->getDebugMode();
        $endpointSystem = $this->moduleSettings->getApiEndpointSelection();

        $this->endpoint = $this->moduleSettings->getApiEndpoint($endpointSystem);
        $this->baererToken = $this->moduleSettings->getApiBaerertoken($endpointSystem);
        $this->soluteShopId = $this->moduleSettings->getApiShopId($endpointSystem);
        $this->soluteFeedId = $this->moduleSettings->getApiFeedId($endpointSystem);
        $this->response = [];
        $this->responseInfo = [];

        // ToDo: Load from system in further version:
        $this->language = 'DE';
        $this->country = 'DE';
        $this->currency = 'EUR';
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function insert(): void
    {
        if (!$this->checkData() || empty($this->batchData)) {
            return;
        }

        $this->setHeaders();
        $this->setOptions($this->endpoint, $this->headers, self::REST_PUT);
        $emptyArticles = '';

        foreach ($this->batchData as $articleId => $dataItem) {
            if (empty($dataItem)) {
                if (!empty($emptyArticles)) {
                    $emptyArticles .= ', ';
                }
                $emptyArticles .= $articleId;
                $this->response['responses'][] = [
                    'document_id' => $articleId . '_' . $this->soluteShopId,
                    'feed_id' => $this->soluteFeedId,
                    'message' => 'No changes deteced. Data feed not sent.'
                    ];
                continue;
            }
            $document = $this->convertDataItemToDocument($dataItem);
            $this->offers[] = $this->prepareEntry(self::OPERATION_INSERT, $articleId, $document);
        }

        if (empty($this->offers)) {
            if ($this->isDebug) {
                $message = 'No datas to sent for article(s) ' . $emptyArticles . '.';
                Logger::addLog($message, SoluteConfig::UM_LOG_INFO);
            }
            return;
        }
        $payload = ['entries' => $this->offers];

        if ($this->isDebug) {
            $message = 'Send insert request: Options:' . json_encode($this->options) . ' | Offers: '
                . json_encode($payload);
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO);
        }

        $this->response = array_merge($this->response, $this->send($this->options, $payload));

        if ($this->isDebug) {
            Logger::addLog('Response: ' . json_encode($this->response), SoluteConfig::UM_LOG_INFO);
        }
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function check(): void
    {
        if (!$this->checkData()) {
            return;
        }

        $this->setHeaders();
        $this->setOptions($this->endpoint, $this->headers, self::REST_PUT);

        $entryList = [];
        foreach ($this->batchData as $articleId) {
            $entryList[] = [
                'operation' => self::OPERATION_GET,
                'feed_id' => $this->soluteFeedId,
                'shop_id' => $this->soluteShopId,
                'document_id' => $articleId . '_' . $this->soluteShopId,
            ];
        }
        $payload = ['entries' => $entryList];

        if ($this->isDebug) {
            $message = 'Send check request: Options:' . json_encode($this->options) . ' | Data: '
                . json_encode($payload);
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO);
        }

        $this->response = $this->send($this->options, $payload);

        if ($this->isDebug) {
            Logger::addLog('Response: ' . json_encode($this->response), SoluteConfig::UM_LOG_INFO);
        }
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function delete(): void
    {
        if (!$this->checkData()) {
            return;
        }

        $this->setHeaders();
        $this->setOptions($this->endpoint, $this->headers, self::REST_PUT);

        $entryList = [];
        foreach ($this->batchData as $articleId) {
            $entryList[] = [
                'operation' => self::OPERATION_DELETE,
                'feed_id' => $this->soluteFeedId,
                'shop_id' => $this->soluteShopId,
                'document_id' => $articleId . '_' . $this->soluteShopId,
            ];
        }
        $payload = ['entries' => $entryList];

        if ($this->isDebug) {
            $message = 'Send delete request: Options:' . json_encode($this->options) . ' | Data: '
                . json_encode($payload);
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO);
        }

        $this->response = $this->send($this->options, $payload);

        if ($this->isDebug) {
            Logger::addLog('Response: ' . json_encode($this->response), SoluteConfig::UM_LOG_INFO);
        }
    }

    /**
     * @param string $url
     * @param int $availability
     * @param float $articlePrice
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sendLanding(string $url, int $availability, float $articlePrice): void
    {
        if (empty($url) || $availability < 0 || $availability > 1 || $articlePrice < 0) {
            $message = 'Error while sending landingpage data to solute. Invalid value(s).';
            $data = [
                'url' => $url,
                'availability' => $availability,
                'articlePrice' => $articlePrice,
            ];
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR, $data);
            return;
        }

        $endpoint = $this->moduleSettings->getTrackingLandingEndpoint();
        if (empty($endpoint)) {
            $message = 'Error while sending landingpage data to solute. Value for endpoint is empty.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return;
        }

        $url = $this->filterSid($url);
        $target = $endpoint . '?';
        $target .= 'url=' . urlencode($url);
        $target .= '&avail=' . $availability;
        $target .= '&price=' . urlencode($articlePrice);

        if ($this->isDebug) {
            $message = 'Send landingpage data to solute.';
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO, [$target]);
        }

        $this->setOptions($target, [], self::REST_GET);
        $this->response = $this->send($this->options, []);

        if ($this->getResponseState() !== self::STATE_NO_CONTENT) {
            $message = 'Response state from api: ' . $this->getResponseState();
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
        }

        if ($this->isDebug) {
            $message = 'Sent landingpage data to solute. Response-State: ' . $this->getResponseState();
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO, $this->response);
        }
    }

    /**
     * @param string $url
     * @param float $netOrderValue
     * @param string $orderId
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function sendConversion(string $url, float $netOrderValue, string $orderId): void
    {
        if (empty($url) || empty($orderId) || $netOrderValue <= 0) {
            $message = 'Error while sending conversion data to solute. Invalid value(s).';
            $data = [
                'url' => $url,
                'orderId' => $orderId,
                'netOrderValue' => $netOrderValue
            ];
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR, $data);
            return;
        }

        $endpoint = $this->moduleSettings->getTrackingConversionEndpoint();
        if (empty($endpoint)) {
            $message = 'Error while sending conversion data to solute. Value for endpoint is empty.';
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
            return;
        }

        $url = $this->filterSid($url);
        $target = $endpoint . '?';
        $target .= 'val=' . $netOrderValue;
        $target .= '&oid=' . $orderId;
        $target .= '&factor=1';
        $target .= '&url=' . urlencode($url);

        if ($this->isDebug) {
            $message = 'Send conversion data to solute.';
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO, [$target]);
        }

        $this->setOptions($target, [], self::REST_GET);
        $this->response = $this->send($this->options, []);

        if ($this->getResponseState() !== self::STATE_NO_CONTENT) {
            $message = 'Response state from api: ' . $this->getResponseState();
            Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
        }

        if ($this->isDebug) {
            $message = 'Sent conversion data to solute. Response-State: ' . $this->getResponseState();
            Logger::addLog($message, SoluteConfig::UM_LOG_INFO, $this->response);
        }
    }

    /**
     * @param array $dataItem
     * @return array
     */
    private function convertDataItemToDocument(array $dataItem): array
    {
        $document = [];
        $document[RestConfig::DOC_TITLE]                = $dataItem[SoluteConfig::UM_ATTR_NAME] ?: '';
        $document[RestConfig::DOC_DESCRIPTION]          = $dataItem[SoluteConfig::UM_ATTR_DESCRIPTION] ?: '';
        $document[RestConfig::DOC_LINK]                 = $dataItem[SoluteConfig::UM_ATTR_LINK] ?: '';

        $images = $dataItem[SoluteConfig::UM_ATTR_IMAGES] ?: '';
        if (!empty($images)) {
            $imageList = explode(',', $images);
            $document[RestConfig::DOC_IMAGELINK] = array_shift($imageList);

            if (!empty($imageList)) {
                $document[RestConfig::DOC_ADDITIONAL_IMAGES] = $imageList;
            }
        }

        $document[RestConfig::DOC_CONTENT_LANGUAGE]     = $this->language;
        $document[RestConfig::DOC_TARGET_COUNTRY]       = $this->country;
        $document[RestConfig::DOC_ADULT]                = $this->getBoolean($dataItem, SoluteConfig::UM_ATTR_ADULT);
        $document[RestConfig::DOC_BRAND]                = $this->getArrayValue($dataItem,SoluteConfig::UM_ATTR_BRAND);
        $document[RestConfig::DOC_COLOR]                = $this->getArrayValue($dataItem,SoluteConfig::UM_ATTR_COLOR);
        $document[RestConfig::DOC_GOOGLE_CATEGORY_ID]   = $this->getGoogleCategoriy($dataItem);
        $document[RestConfig::DOC_GTIN]                 = $this->getArrayValue($dataItem,SoluteConfig::UM_ATTR_GTIN);
        $document[RestConfig::DOC_MPN]                  = $this->getArrayValue($dataItem,SoluteConfig::UM_ATTR_MPN);
        $document[RestConfig::DOC_PRICE]                = $this->getPrice($dataItem, SoluteConfig::UM_ATTR_PRICE);
        $document[RestConfig::DOC_SALE_PRICE]           = $this->getPrice($dataItem, SoluteConfig::UM_ATTR_SALE_PRICE);
        $document[RestConfig::DOC_SALE_EFFECTIVE_DATE]  = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE);
        $document[RestConfig::DOC_UNIT_PRICE]           = $this->getUnitValue($dataItem, SoluteConfig::UM_ATTR_PRICING_MEASURE, SoluteConfig::UM_TYPE_INT);
        $document[RestConfig::DOC_UNIT_BASE_PRICE]      = $this->getUnitValue($dataItem, SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE, SoluteConfig::UM_TYPE_INT);
        $document[RestConfig::DOC_SHIPPING]             = $this->getShipping($dataItem);
        $document[RestConfig::DOC_ITEMGROUPID]          = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_ITEM_GROUP_ID);
        $document[RestConfig::DOC_MATERIAL]             = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_MATERIAL);
        $document[RestConfig::DOC_PATTERN]              = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_PATTERN);
        $document[RestConfig::DOC_SIZES]                = $this->getList($dataItem, SoluteConfig::UM_ATTR_SIZE);
        $document[RestConfig::DOC_HEIGHT]               = $this->getUnitValue($dataItem, SoluteConfig::UM_ATTR_HEIGHT, SoluteConfig::UM_TYPE_FLOAT);
        $document[RestConfig::DOC_LENGTH]               = $this->getUnitValue($dataItem, SoluteConfig::UM_ATTR_LENGTH, SoluteConfig::UM_TYPE_FLOAT);
        $document[RestConfig::DOC_WIDTH]                = $this->getUnitValue($dataItem, SoluteConfig::UM_ATTR_WIDTH, SoluteConfig::UM_TYPE_FLOAT);
        $document[RestConfig::DOC_WEIGHT]               = $this->getUnitValue($dataItem, SoluteConfig::UM_ATTR_WEIGHT, SoluteConfig::UM_TYPE_FLOAT);
        $document[RestConfig::DOC_CUSTOM_ATTRIBUTES]    = $this->getCustomAttributes($dataItem);
        $document[RestConfig::DOC_INSTALLMENT]          = $this->getInstallment($dataItem, SoluteConfig::UM_ATTR_INSTALLMENT);
        $document[RestConfig::DOC_MULTIPACK]            = $this->getArrayValue($dataItem,SoluteConfig::UM_ATTR_QUANTITY_NUMBER);
        $document[RestConfig::DOC_IS_BUNDLE]            = $this->getBoolean($dataItem, SoluteConfig::UM_ATTR_IS_BUNDLE);
        $document[RestConfig::DOC_PRODUCT_TYPE]         = $this->getList($dataItem, SoluteConfig::UM_ATTR_PRODUCT_TYPE);
        $document[RestConfig::DOC_AGE_GROUP]            = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_AGE_GROUP);
        $document[RestConfig::DOC_AVAILABILITY]         = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_AVAILABILITY);
        $document[RestConfig::DOC_CONDITION]            = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_CONDITION);
        $document[RestConfig::DOC_GENDER]               = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_GENDER);
        $document[RestConfig::DOC_SIZE_SYSTEM]          = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_SIZE_SYSTEM);
        $document[RestConfig::DOC_ENERGY_CLASS]         = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_ENERGY_CLASS);
        $document[RestConfig::DOC_ENERGY_CLASS_MIN]     = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN);
        $document[RestConfig::DOC_ENERGY_CLASS_MAX]     = $this->getArrayValue($dataItem, SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX);
        $document[RestConfig::DOC_SUBSCRIPTIONCOST]     = $this->getSubscriptionCost($dataItem);

        return $this->eraseEmptyEntries($document);
    }

    /**
     * @param array $array
     * @param string $key
     * @return string
     */
    private function getArrayValue(array $array, string $key): string
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        return '';
    }

    /**
     * @param array $dataItem
     * @return string
     */
    private function getGoogleCategoriy(array $dataItem): string
    {
        if (
            array_key_exists(SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID, $dataItem)
            &&  !empty($dataItem[SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID])
        ) {
            return $dataItem[SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID];
        } elseif (
            array_key_exists(SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT, $dataItem)
                &&  !empty($dataItem[SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT])
        ) {
            return $dataItem[SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT];
        }

        return '';
    }

    /**
     * @param $dataItem
     * @return array
     */
    private function getCustomAttributes($dataItem): array
    {
        $customList = [];
        $fieldList = [
            SoluteConfig::UM_ATTR_MODIFIED_DATE,
            SoluteConfig::UM_ATTR_AID,
            SoluteConfig::UM_ATTR_ASIN,
            SoluteConfig::UM_ATTR_OLD_PRICE,
            SoluteConfig::UM_ATTR_VOUCHER_PRICE,
            SoluteConfig::UM_ATTR_SALE_PRICE_PUBLISHING,
            SoluteConfig::UM_ATTR_PPU,
            SoluteConfig::UM_ATTR_AVAILABILITY_DATE,
            SoluteConfig::UM_ATTR_STOCK_QUANTITY,
            SoluteConfig::UM_ATTR_SHOP_CAT,
            SoluteConfig::UM_ATTR_PROMO_TEXT,
            SoluteConfig::UM_ATTR_VOUCHER_TEXT,
            SoluteConfig::UM_ATTR_SPECIAL,
            SoluteConfig::UM_ATTR_ENERGY_CLASS_ILLUMIN,
            SoluteConfig::UM_ATTR_COMPATIBLE_PRODUCT,
            SoluteConfig::UM_ATTR_SIZE,
            SoluteConfig::UM_ATTR_COLOR,
            SoluteConfig::UM_ATTR_MATERIAL,
            SoluteConfig::UM_ATTR_PATTERN,
            SoluteConfig::UM_ATTR_AGE_RATING,
            SoluteConfig::UM_ATTR_PLATFORM,
            SoluteConfig::UM_ATTR_STYLE,
            SoluteConfig::UM_ATTR_PROPERTIES,
            SoluteConfig::UM_ATTR_FUNCTIONS,
            SoluteConfig::UM_ATTR_EQUIPMENT,
            SoluteConfig::UM_ATTR_DEPTH,
            SoluteConfig::UM_ATTR_PZN,
            SoluteConfig::UM_ATTR_WET_GRIP,
            SoluteConfig::UM_ATTR_FUEL_EFFICIENCY,
            SoluteConfig::UM_ATTR_ROLLING_NOISE,
            SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS,
            SoluteConfig::UM_ATTR_HSN_TSN,
            SoluteConfig::UM_ATTR_SPH,
            SoluteConfig::UM_ATTR_DIA,
            SoluteConfig::UM_ATTR_BC,
            SoluteConfig::UM_ATTR_CYL,
            SoluteConfig::UM_ATTR_AXIS,
            SoluteConfig::UM_ATTR_ADD
        ];

        foreach ($fieldList as $id) {
            if (array_key_exists($id, $dataItem) && !empty($dataItem[$id])) {
                $customList[] = [
                    RestConfig::DOC_NAME => $id,
                    RestConfig::DOC_VALUE => $dataItem[$id]
                ];
            }
        }

        return $customList;
    }

    /**
     * @param array $dataItem
     * @param string $id
     * @return array
     */
    private function getList(array $dataItem, string $id): array
    {
        if (!array_key_exists($id, $dataItem)) {
            return [];
        }
        return [
          $dataItem[$id]
        ];
    }

    /**
     * @param array $dataItem
     * @param string $id
     * @return bool|string
     */
    private function getBoolean(array $dataItem, string $id)
    {
        if (!array_key_exists($id, $dataItem)) {
            return '';
        }

        if ($dataItem[$id] === 'yes') {
            return true;
        }

        return false;
    }

    /**
     * @param array $dataItem
     * @return array
     */
    private function getSubscriptionCost(array $dataItem): array
    {
        if (
            !array_key_exists(SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD, $dataItem)
            ||  !array_key_exists(SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH, $dataItem)
            ||  !array_key_exists(SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE, $dataItem)
            ||  empty($dataItem[SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD])
            ||  empty($dataItem[SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH])
            ||  empty($dataItem[SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE])
        ) {
            return [];
        }

        return [
            RestConfig::DOC_SUBSCRIPTION_PERIOD => $dataItem[SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD],
            RestConfig::DOC_SUBSCRIPTION_PERIOD_LENGTH => $dataItem[SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH],
            RestConfig::DOC_SUBSCRIPTION_AMOUNT => $this->getPrice($dataItem, SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE)
        ];
    }

    /**
     * @param array $dataItem
     * @param $id
     * @return array
     */
    private function getInstallment(array $dataItem, $id): array
    {
        if (!array_key_exists($id, $dataItem)) {
            return [];
        }

        $dataList = explode(':', $dataItem[$id]);

        return [
            RestConfig::DOC_MONTHS => (int) $dataList[0],
            RestConfig::DOC_AMOUNT => $this->getPrice(['price' => $dataList[1]], 'price')
        ];
    }

    /**
     * @param array $dataItem
     * @return array
     */
    private function getShipping(array $dataItem): array
    {
        if ($this->country === 'AT') {
            $price = $this->getPrice($dataItem, SoluteConfig::UM_ATTR_DLV_COST_AT);
        } else {
            $price = $this->getPrice($dataItem, SoluteConfig::UM_ATTR_DLV_COST);
        }

        $item = [];
        $delivery = $this->getDeliveryTime($dataItem[SoluteConfig::UM_ATTR_DLV_TIME]);
        if (!empty($delivery['min'])) {
            $item[RestConfig::DOC_MIN_TRANSIT_TIME] = $delivery['min'];
            $item[RestConfig::DOC_MIN_HANDLING_TIME] = $delivery['min'];
        }
        if (!empty($delivery['max'])) {
            $item[RestConfig::DOC_MAX_TRANSIT_TIME] = $delivery['max'];
            $item[RestConfig::DOC_MAX_HANDLING_TIME] = $delivery['max'];
        }
        $item[RestConfig::DOC_PRICE] = $price;
        $item[RestConfig::DOC_COUNTRY] = $this->country;
        $list[] = $item;

        return $list;
    }

    /**
     * @param array $dataItem
     * @param string $id
     * @return array
     */
    private function getPrice(array $dataItem, string $id): array
    {
        if (
            !array_key_exists($id, $dataItem)
            || (float) $dataItem[$id] <= 0
        ) {
            return [];
        }

        return [
            RestConfig::DOC_VALUE => (float) $dataItem[$id],
            RestConfig::DOC_CURRENCY => $this->currency
        ];
    }

    /**
     * @param array $dataItem
     * @param string $id
     * @param string $type
     * @return array
     */
    private function getUnitValue(array $dataItem, string $id, string $type): array
    {
        if (
            !array_key_exists($id, $dataItem)
        ) {
            return [];
        }

        $data = [
            0 => '',
            1 => ''
        ];
        $valueRaw = $dataItem[$id];
        for ($i = 0; $i <= strlen($valueRaw); $i++) {
            $position = mb_substr($valueRaw, $i, 1);

            if ($position === ' ') {
                continue;
            }

            $positionChr = ord($position);
            if (
                ($positionChr >= 48 && $positionChr <= 57)
                || $positionChr === 46
            ) {
                $data[0] .= $position;
            } else {
                $data[1] .= $position;
            }
        }

        if ($type === self::UM_TYPE_INT) {
            $value = (int) $data[0];
        } elseif ($type === SoluteConfig::UM_TYPE_FLOAT) {
            $value = (float) $data[0];
        }

        return [
            RestConfig::DOC_VALUE => $value,
            RestConfig::DOC_UNIT => $data[1]
        ];
    }

    /**
     * @param string $deliveryTime
     * @return int[]|string[]
     */
    private function getDeliveryTime(string $deliveryTime): array
    {
        $deliveryTime = trim($deliveryTime);
        if (empty($deliveryTime)) {
            return [
                'min' => '',
                'max' => '',
            ];
        }
        $deliveryTime = str_replace('bis', '-', $deliveryTime);
        if (strpos($deliveryTime, '-') !== false) {
            $days = explode('-', $deliveryTime);
            $values['min'] = (int)$days[0];
            if (array_key_exists(1, $days)) {
                $values['max'] = (int)$days[1];
            } else {
                $values['max'] = (int)$days[0];
            }
        } else {
            $values['min'] = (int)$deliveryTime;
            $values['max'] = (int)$deliveryTime;
        }

        return $values;
    }

    /**
     * @param string $operation
     * @param string $articleId
     * @param array $document
     * @return array
     */
    private function prepareEntry(string $operation, string $articleId, array $document = []): array
    {
        $documentId = $articleId . '_' . $this->soluteShopId;
        $entry = [
            'operation'     => $operation,
            'feed_id'       => $this->soluteFeedId,
            'shop_id'       => $this->soluteShopId,
            'document_id'   => $documentId,
        ];
        if (!empty($document)) {
            $entry['document'] = $document;
        }

        return $entry;
    }

    /**
     * @param string $endpoint
     * @param array $headers
     * @param string $command
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setOptions(string $endpoint, array $headers, string $command): void
    {
        $this->options = [
            CURLOPT_URL             => $endpoint,
            CURLOPT_HTTPHEADER      => $headers,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_CUSTOMREQUEST   => $command,
            CURLOPT_USERAGENT       => $this->getUserAgent(),
            CURLOPT_REFERER         => $this->referer,
            ];
    }

    /**
     * @return void
     */
    private function setHeaders(): void
    {
        $this->headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->baererToken,
        ];
    }

    /**
     * @return bool
     */
    private function checkData(): bool
    {
        $fields  = [
            'Endpoint'          => $this->endpoint,
            'Baerer Token'      => $this->baererToken,
            'Solute Shop Id'    => $this->soluteShopId,
            'Solute Feed Id'    => $this->soluteFeedId,
            'Data'              => $this->batchData
        ];

        foreach ($fields as $id => $field) {
            if (empty($field)) {
                $message = 'REST API: Value for ' . $id . ' is missing.';
                Logger::addLog($message, SoluteConfig::UM_LOG_ERROR);
                return false;
            }
        }

        return true;
    }

    /**
     * @param array $options
     * @param array $payload
     * @return array
     */
    private function send(array $options, array $payload): array
    {
        $cUrl = curl_init();
        curl_setopt_array($cUrl, $options);
        curl_setopt($cUrl, CURLOPT_POSTFIELDS, json_encode($payload));

        $responseRaw = curl_exec($cUrl);
        $response = json_decode($responseRaw, true);
        $this->responseInfo = curl_getInfo($cUrl);

        curl_close($cUrl);

        if ($response === null) {
            $response = [];
        }

        return $response;
    }

    /**
     * @param array $document
     * @return array
     */
    private function eraseEmptyEntries(array $document): array
    {
        foreach ($document as $key => $item) {
            if (empty($item)) {
                unset($document[$key]);
            }
        }

        if (empty($document)) {
            return [];
        }

        return $document;
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function getUserAgent(): string
    {
        $container = ContainerFactory::getInstance()->getContainer();
        $moduleConfiguration = $container
            ->get(ModuleConfigurationDaoBridgeInterface::class)
            ->get(SoluteConfig::UM_MODULE_ID);
        $shopVersion = oxNew(ShopVersion::class)->getVersion();
        $edition = oxNew(Facts::class)->getEdition();

        $userAgent = SoluteConfig::UM_USERAGENT . '/' . $moduleConfiguration->getVersion();
        $userAgent .= '(OXID ' . $edition  . '; ' . $shopVersion . ')';

        return $userAgent;
    }

    /**
     * @param string $urlRaw
     * @return string
     */
    private function filterSid(string $urlRaw): string
    {
        $urlList = explode('?', $urlRaw);
        $getList = explode('&', $urlList[1]);
        foreach ($getList as $key => $get) {
            if (strstr($get, 'force_sid') !== false) {
                unset($getList[$key]);
            }
        }

        $filteredUrl = $urlList[0];
        if (!empty($getList)) {
            $gets = '';
            foreach ($getList as $get) {
                if (!empty($gets)) {
                    $gets .= '&';
                }
                $gets .= $get;
            }
            $filteredUrl .= '?' . $gets;
        }
        return $filteredUrl;
    }

    /**
     * @param array $batchData
     *
     * format: [ articleId (=oxarticles.OXID) => [datafeed (mapData->getData())], ... ]
     */
    public function setBatchData(array $batchData): void
    {
        $this->batchData = $batchData;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return int
     */
    public function getResponseState(): int
    {
        if (array_key_exists(self::REST_STATE_HTTP_CODE, $this->responseInfo)) {
            return $this->responseInfo[self::REST_STATE_HTTP_CODE];
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->isDebug;
    }
}
