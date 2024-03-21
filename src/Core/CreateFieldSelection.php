<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Core\Registry;
use UnitM\Solute\Core\EventTrait;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

class CreateFieldSelection implements SoluteConfig
{
    use EventTrait;

    /**
     * @var DatabaseInterface
     */
    private DatabaseInterface $database;

    /**
     * @var string
     */
    private string $sqlCache;

    /**
     * @param DatabaseInterface $database
     */
    public function __construct(
        DatabaseInterface $database
    ) {
        $this->database = $database;
        $this->sqlCache = '';
    }

    /**
     * @return string
     */
    public function run(): string
    {
        $this->sqlCache = $this->createSql($this->getDefinitionList(), $this->database);
        return $this->sqlCache;
    }

    /**
     * @param array $valueList
     * @param DatabaseInterface $database
     * @return string
     */
    private function createSql(array $valueList, DatabaseInterface $database): string
    {
        if (empty($valueList)) {
            return '';
        }

        $values = [];

        $shopList = Registry::getConfig()->getShopIds();
        foreach ($shopList as $shopId) {
            foreach ($valueList as $item) {
                $row = [
                    SoluteConfig::OX_COL_ID                 => md5($item[SoluteConfig::UM_ID_TITLE]),
                    SoluteConfig::UM_COL_SHOP_ID            => $shopId,
                    SoluteConfig::UM_COL_DATA_RESSOURCE     => json_encode($item[SoluteConfig::UM_ID_DATA_RESSOURCE]),
                    SoluteConfig::UM_COL_ATTRIBUTE_GROUP_ID => md5($item[SoluteConfig::UM_ID_ATTRIBUTE_GROUP]),
                    SoluteConfig::UM_COL_FIELD_TITLE        => $item[SoluteConfig::UM_ID_TITLE]
                ];
                $values[] = $row;
            }
        }

        return $this->getSqlForInsertFromDataArray(
            $values,
            SoluteConfig::UM_TABLE_FIELD_SELECTION,
            $database
        );
    }

    /**
     * @return array
     */
    private function getDefinitionList(): array
    {
        return [
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_MODIFIED_DATE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_TIMESTAMP,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => SoluteConfig::UM_CONV_MYSQL2ISO8601
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_AID,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_ARTICLENUMBER,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ASIN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ASIN',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_GTIN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_EAN,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_MPN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_MPN,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_NAME,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_TITLE,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_BRAND,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_RELATIONFIELD => [
                        SoluteConfig::UM_DR_RELATION_TABLE  => SoluteConfig::OX_TABLE_MANUFACTURER,
                        SoluteConfig::UM_DR_RELATION_FIELD  => SoluteConfig::OX_COL_TITLE,
                        SoluteConfig::UM_DR_RELATION_ID     => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_MANUFACTURER_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_DESCRIPTION,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_RELATIONFIELD => [
                        SoluteConfig::UM_DR_RELATION_TABLE  => SoluteConfig::OX_TABLE_ARTICLE_EXTEND,
                        SoluteConfig::UM_DR_RELATION_FIELD  => SoluteConfig::OX_COL_LONGEDESCRIPTION,
                        SoluteConfig::UM_DR_RELATION_ID     => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_LINK,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_GENERATEDFIELD => [
                        SoluteConfig::UM_DR_GENERATED   => SoluteConfig::UM_GEN_PRODUCT_URL,
                        SoluteConfig::UM_DR_CONVERT     => SoluteConfig::UM_CONV_ERASE_SID
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_IMAGES,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_COMMON,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_GENERATEDFIELD => [
                        SoluteConfig::UM_DR_GENERATED   => SoluteConfig::UM_GEN_IMAGE_URL,
                        SoluteConfig::UM_DR_CONVERT     => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PRICE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_PRICE,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_OLD_PRICE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_PRICE_RETAIL,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => 'Preis A',
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_PRICE_A,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => 'Preis B',
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_PRICE_B,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => 'Preis C',
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_PRICE_C,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_VOUCHER_PRICE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_VOUCHER_PRICE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SALE_PRICE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SALE_PRICE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SALE_PRICE_EFFECTIVE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SALE_PRICE_EFFECTIVE_DATE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SALE_PRICE_PUBLISHING,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SALE_PRICE_PUBLISHING_DATE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PPU,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PPU',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PRICING_MEASURE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_UNIT_PRICING_MEASURE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PRICING_BASE_MEASURE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_UNIT_PRICING_BASE_MEASURE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SUBSCRIPTION_PERIOD,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SUBSCRIPTION_COST_PERIOD',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SUBSCRIPTION_LENGTH,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SUBSCRIPTION_COST_PERIOD_LENGTH',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SUBSCRIPTION_VALUE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SUBSCRIPTION_COST_AMOUNT_VALUE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_INSTALLMENT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_PERIOD_PRICE,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_INSTALLMENT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_DLV_COST,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_DLV_COST',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_DLV_COST_AT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_DLV_COST_AT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_DLV_TIME,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_GENERATEDFIELD => [
                        SoluteConfig::UM_DR_GENERATED   => SoluteConfig::UM_GEN_DELIVERY_TIME,
                        SoluteConfig::UM_DR_CONVERT     => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_AVAILABILITY,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_GENERATEDFIELD => [
                        SoluteConfig::UM_DR_GENERATED   => SoluteConfig::UM_GEN_AVAILABILITY,
                        SoluteConfig::UM_DR_CONVERT     => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_AVAILABILITY_DATE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_AVAILABILITY_DATE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_STOCK_QUANTITY,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DELIVERY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_STOCK,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SHOP_CAT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_CATEGORY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_GENERATEDFIELD => [
                        SoluteConfig::UM_DR_GENERATED   => SoluteConfig::UM_GEN_BREADCRUMB,
                        SoluteConfig::UM_DR_CONVERT     => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_GOOGLE_PRODUCT_CAT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_CATEGORY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_GOOGLE_PRODUCT_CATEGORY',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_GOOGLE_PRODUCT_CAT_ID,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_CATEGORY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_GOOGLE_PRODUCT_CATEGORY_ID',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PROMO_TEXT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_VOUCHER,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PROMO_TEXT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_VOUCHER_TEXT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_VOUCHER,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_VOUCHER_TEXT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SPECIAL,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_VOUCHER,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SPECIAL',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ENERGY_CLASS,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ENERGY_EFFICIENCY_CLASS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ENERGY_CLASS_MIN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_MIN_ENERGY_EFFICIENCY_CLASS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ENERGY_CLASS_MAX,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_MAX_ENERGY_EFFICIENCY_CLASS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ENERGY_CLASS_ILLUMIN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_ENERGY,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ENERGY_EFFICIENCY_CLASS_ILLUMINANT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_CONDITION,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_CONDITION',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ITEM_GROUP_ID,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ITEM_GROUP_ID',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_COMPATIBLE_PRODUCT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_COMPATIBLE_PRODUCTS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_QUANTITY_NUMBER,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_QUANTITY_NUMBER',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_IS_BUNDLE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_IS_BUNDLE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SIZE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SIZE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SIZE_SYSTEM,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SIZE_SYSTEM',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_COLOR,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_COLOR',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_GENDER,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_GENDER',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_MATERIAL,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_MATERIAL',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PATTERN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PATTERN',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_AGE_RATING,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_AGE_RATING',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_AGE_GROUP,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_AGE_GROUP',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ADULT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ADULT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PLATFORM,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PLATFORM',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PRODUCT_TYPE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PRODUCT_TYPE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_STYLE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_STYLE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PROPERTIES,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PROPERTIES',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_FUNCTIONS,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_FUNCTIONS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_EQUIPMENT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_DESCRIPTION,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_EQUIPMENT',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_HEIGHT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_HEIGHT,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_WIDTH,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_WIDTH,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_LENGTH,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_LENGTH,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_DEPTH,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_DEPTH',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_PZN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_MEDICAL,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_PZN',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_WET_GRIP,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_WET_GRIP',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_FUEL_EFFICIENCY,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_FUEL_EFFICIENCY',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ROLLING_NOISE,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_EXTERNAL_ROLLING_NOISE',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ROLLING_NOISE_CLASS,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_WHEELS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ROLLING_NOISE_CLASS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_HSN_TSN,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_CAR,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_HSN_TSN',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_SPH,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_SPH_PWR',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_DIA,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_DIA',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_BC,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_BC',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_CYL,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_CYL',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_AXIS,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_AXIS',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_ADD,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_EYELENS,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_ATTRIBUTE => [
                        SoluteConfig::UM_DR_LABEL   => 'SOLUTE_ADD',
                        SoluteConfig::UM_DR_CONVERT => ''
                    ]
                ]
            ],
            [
                SoluteConfig::UM_ID_TITLE => SoluteConfig::UM_TITLE_WEIGHT,
                SoluteConfig::UM_ID_ATTRIBUTE_GROUP => SoluteConfig::UM_ATTR_GROUP_MESSUREMENT,
                SoluteConfig::UM_ID_DATA_RESSOURCE => [
                    SoluteConfig::UM_DR_TYPE_FIELD => [
                        SoluteConfig::UM_DR_PRIMARY_TABLE   => SoluteConfig::OX_TABLE_ARTICLE,
                        SoluteConfig::UM_DR_PRIMARY_FIELD   => SoluteConfig::OX_COL_WEIGHT,
                        SoluteConfig::UM_DR_PRIMARY_ID      => SoluteConfig::OX_COL_ID,
                        SoluteConfig::UM_DR_CONVERT         => ''
                    ]
                ]
            ],
        ];
    }
}
