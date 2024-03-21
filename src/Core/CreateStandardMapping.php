<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use UnitM\Solute\Core\EventTrait;

class CreateStandardMapping implements SoluteConfig
{
    use EventTrait;

    /**
     * @var DatabaseInterface
     */
    private DatabaseInterface $database;

    /**
     * @var array
     */
    private array $shopIdList;

    /**
     * @var string
     */
    private string $sqlCache;

    /**
     * @param DatabaseInterface $database
     * @param array $shopIdList
     */
    public function __construct(
        DatabaseInterface $database,
        array $shopIdList
    ) {
        $this->database = $database;
        $this->shopIdList = $shopIdList;
        $this->sqlCache = '';
    }

    /**
     * @return string
     */
    public function run(): string
    {
        if (empty($this->shopIdList)) {
            return '';
        }

        $this->sqlCache = $this->createSql($this->getDefinitionList(), $this->shopIdList, $this->database);
        return $this->sqlCache;
    }

    /**
     * @param array $valueList
     * @param array $shopIdList
     * @param DatabaseInterface $database
     * @return string
     */
    private function createSql(array $valueList, array $shopIdList, DatabaseInterface $database): string
    {
        if (empty($valueList)) {
            return '';
        }

        $values = [];
        foreach ($shopIdList as $shopId) {
            foreach ($valueList as $item) {
                $id = $shopId . $item[SoluteConfig::UM_ID_ATTRIBUTE_ID] . $item[SoluteConfig::UM_ID_DATA_RESSOURCE_ID];
                $oxid = md5($id);
                $row = [
                    SoluteConfig::OX_COL_ID                 => $oxid,
                    SoluteConfig::UM_COL_OBJECT_ID          => '',
                    SoluteConfig::UM_COL_SHOP_ID            => $shopId,
                    SoluteConfig::UM_COL_ATTRIBUTE_ID       => $item[SoluteConfig::UM_ID_ATTRIBUTE_ID],
                    SoluteConfig::UM_COL_DATA_RESSOURCE_ID  => $item[SoluteConfig::UM_ID_DATA_RESSOURCE_ID],
                    SoluteConfig::UM_COL_MANUAL_VALUE       => ''
                ];
                $values[] = $row;
            }
        }

        return $this->getSqlForInsertFromDataArray($values, SoluteConfig::UM_TABLE_ATTRIBUTE_MAPPING, $database);
    }

    /**
     * @return array
     */
    private function getDefinitionList(): array
    {
        return [
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_MODIFIED_DATE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_MODIFIED_DATE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_AID),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_AID),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ASIN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ASIN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_GTIN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_GTIN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_MPN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_MPN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_NAME),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_NAME),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_BRAND),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_BRAND),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_DESCRIPTION),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_DESCRIPTION),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_LINK),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_LINK),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_TARGET_URL),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_LINK),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_IMAGES),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_IMAGES),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PRICE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PRICE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_OLD_PRICE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_OLD_PRICE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_VOUCHER_PRICE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_VOUCHER_PRICE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SALE_PRICE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SALE_PRICE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SALE_PRICE_EFFECTIVE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SALE_PRICE_PUBLISHING),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SALE_PRICE_PUBLISHING),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PPU),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PPU),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PRICING_MEASURE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PRICING_MEASURE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PRICING_BASE_MEASURE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SUBSCRIPTION_PERIOD),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SUBSCRIPTION_LENGTH),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SUBSCRIPTION_VALUE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_INSTALLMENT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_INSTALLMENT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_DLV_COST),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_DLV_COST),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_DLV_COST_AT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_DLV_COST_AT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_DLV_TIME),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_DLV_TIME),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_AVAILABILITY),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_AVAILABILITY),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_AVAILABILITY_DATE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_AVAILABILITY_DATE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_STOCK_QUANTITY),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_STOCK_QUANTITY),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SHOP_CAT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SHOP_CAT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_GOOGLE_PRODUCT_CAT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_GOOGLE_PRODUCT_CAT_ID),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PROMO_TEXT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PROMO_TEXT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_VOUCHER_TEXT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_VOUCHER_TEXT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SPECIAL),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SPECIAL),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ENERGY_CLASS),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ENERGY_CLASS),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ENERGY_CLASS_MIN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ENERGY_CLASS_MAX),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ENERGY_CLASS_ILLUMIN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ENERGY_CLASS_ILLUMIN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_CONDITION),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_CONDITION),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ITEM_GROUP_ID),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ITEM_GROUP_ID),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_COMPATIBLE_PRODUCT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_COMPATIBLE_PRODUCT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_QUANTITY_NUMBER),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_QUANTITY_NUMBER),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_IS_BUNDLE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_IS_BUNDLE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SIZE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SIZE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SIZE_SYSTEM),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SIZE_SYSTEM),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_COLOR),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_COLOR),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_GENDER),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_GENDER),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_MATERIAL),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_MATERIAL),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PATTERN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PATTERN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_AGE_RATING),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_AGE_RATING),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_AGE_GROUP),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_AGE_GROUP),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ADULT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ADULT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PLATFORM),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PLATFORM),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PRODUCT_TYPE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PRODUCT_TYPE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_STYLE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_STYLE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PROPERTIES),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PROPERTIES),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_FUNCTIONS),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_FUNCTIONS),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_EQUIPMENT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_EQUIPMENT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_HEIGHT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_HEIGHT),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_WIDTH),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_WIDTH),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_LENGTH),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_LENGTH),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_DEPTH),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_DEPTH),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_PZN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_PZN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_WET_GRIP),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_WET_GRIP),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_FUEL_EFFICIENCY),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_FUEL_EFFICIENCY),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ROLLING_NOISE),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ROLLING_NOISE),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ROLLING_NOISE_CLASS),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_HSN_TSN),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_HSN_TSN),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_SPH),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_SPH),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_DIA),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_DIA),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_BC),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_BC),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_CYL),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_CYL),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_AXIS),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_AXIS),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_ADD),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_ADD),
            ],
            [
                SoluteConfig::UM_ID_ATTRIBUTE_ID        => md5(SoluteConfig::UM_ATTR_WEIGHT),
                SoluteConfig::UM_ID_DATA_RESSOURCE_ID   => md5(SoluteConfig::UM_TITLE_WEIGHT),
            ],
        ];
    }
}
