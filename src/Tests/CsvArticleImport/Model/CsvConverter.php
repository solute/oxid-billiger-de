<?php

namespace UnitM\Solute\Tests\CsvArticleImport\Model;

use UnitM\Solute\Core\SoluteConfig;

class CsvConverter
{
    private const col_Article_Number        = 8; // I
    private const col_Article_Ean           = 9; // J
    private const col_Article_Mpn           = 10; // K
    private const col_Article_Title         = 11; // L
    private const col_Article_Price         = 12; // M
    private const col_Article_TPrice        = 13; // N
    private const col_Article_Pic1          = 14; // O
    private const col_Article_Pic2          = 15; // P
    private const col_Article_Pic3          = 16; // Q
    private const col_Article_Pic4          = 17; // R
    private const col_Article_Weight        = 18; // S
    private const col_Article_Stock         = 19; // T
    private const col_Article_Length        = 20; // U
    private const col_Article_Width         = 21; // V
    private const col_Article_Height        = 22; // W
    private const col_Manufacturer_Title    = 23; // X
    private const col_Extends_Longdesc      = 24; // Y

    private const col_Rootcategory          = 'col_Rootcategory';
    private const col_Category_Level_1      = 'col_Category_Level_1';
    private const col_Category_Level_2      = 'col_Category_Level_2';
    private const col_Category_Level_3      = 'col_Category_Level_3';

    /**
     * @var array
     */
    private array $dataRow;

    /**
     * @param array $dataRow
     */
    public function __construct(
        array $dataRow
    )
    {
        $this->dataRow = $dataRow;
    }

    /**
     * @param string $table
     * @param string $field
     * @return string
     */
    public function getArticleValue(string $table, string $field): string
    {
        switch ($table) {
            case SoluteConfig::OX_TABLE_ARTICLE:
                return $this->getField($this->getArticleMapping(), $field);
            case SoluteConfig::OX_TABLE_MANUFACTURER:
                return $this->getField($this->getManufacturerMapping(), $field);
            case SoluteConfig::OX_TABLE_ARTICLE_EXTEND:
                return $this->getField($this->getArticleExtendsMapping(), $field);
        }

        return '';
    }

    /**
     * @param array $mapping
     * @param string $field
     * @return string
     */
    private function getField(array $mapping, string $field): string
    {
        if (    array_key_exists($field, $mapping)
            &&  array_key_exists($mapping[$field], $this->dataRow)
        ) {
            $valueRaw = trim($this->dataRow[$mapping[$field]]);
            return utf8_encode($valueRaw);
        }

        return '';
    }

    /**
     * @param string $attribute
     * @return string
     */
    public function getAttributeValue(string $attribute): string
    {
        return $this->getField($this->getAttributeMapping(), $attribute);
    }

    /**
     * @return string
     */
    public function getRootCategory(): string
    {
        return $this->getField($this->getCategoryMapping(), self::col_Rootcategory);
    }

    /**
     * @return string
     */
    public function getCategoryLevel1(): string
    {
        return $this->getField($this->getCategoryMapping(), self::col_Category_Level_1);
    }

    /**
     * @return string
     */
    public function getCategoryLevel2(): string
    {
        return $this->getField($this->getCategoryMapping(), self::col_Category_Level_2);
    }

    /**
     * @return string
     */
    public function getCategoryLevel3(): string
    {
        return $this->getField($this->getCategoryMapping(), self::col_Category_Level_3);
    }

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return (int) $this->getField($this->getShopMapping(), SoluteConfig::OX_COL_SHOPID);
    }

    /**
     * @return int[]
     */
    private function getShopMapping(): array
    {
        return [
          SoluteConfig::OX_COL_SHOPID   => 3,
        ];
    }

    /**
     * @return int[]
     */
    private function getArticleMapping(): array
    {
        return [
            SoluteConfig::OX_COL_ARTICLENUMBER  => self::col_Article_Number,
            SoluteConfig::OX_COL_EAN            => self::col_Article_Ean,
            SoluteConfig::OX_COL_MPN            => self::col_Article_Mpn,
            SoluteConfig::OX_COL_TITLE          => self::col_Article_Title,
            SoluteConfig::OX_COL_PRICE          => self::col_Article_Price,
            SoluteConfig::OX_COL_PRICE_RETAIL   => self::col_Article_TPrice,
            SoluteConfig::OX_COL_PICTURE_1      => self::col_Article_Pic1,
            SoluteConfig::OX_COL_PICTURE_2      => self::col_Article_Pic2,
            SoluteConfig::OX_COL_PICTURE_3      => self::col_Article_Pic3,
            SoluteConfig::OX_COL_PICTURE_4      => self::col_Article_Pic4,
            SoluteConfig::OX_COL_WEIGHT         => self::col_Article_Weight,
            SoluteConfig::OX_COL_STOCK          => self::col_Article_Stock,
            SoluteConfig::OX_COL_LENGTH         => self::col_Article_Length,
            SoluteConfig::OX_COL_WIDTH          => self::col_Article_Width,
            SoluteConfig::OX_COL_HEIGHT         => self::col_Article_Height
        ];
    }

    /**
     * @return int[]
     */
    private function getManufacturerMapping(): array
    {
        return [
            SoluteConfig::OX_COL_TITLE          => self::col_Manufacturer_Title,
        ];
    }

    /**
     * @return int[]
     */
    private function getArticleExtendsMapping(): array
    {
        return [
            SoluteConfig::OX_COL_LONGEDESCRIPTION => self::col_Extends_Longdesc,
        ];
    }

    /**
     * @return int[]
     */
    private function getAttributeMapping(): array
    {
        return [
            SoluteConfig::UM_ATTR_ASIN                  => 25, // Z
            SoluteConfig::UM_ATTR_VOUCHER_PRICE         => 26, // AA
            SoluteConfig::UM_ATTR_SALE_PRICE            => 27, // AB
            SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE  => 28, // AC
            SoluteConfig::UM_ATTR_SALE_PRICE_PUBLISHING => 29, // AD
            SoluteConfig::UM_ATTR_PPU                   => 30, // AE
            SoluteConfig::UM_ATTR_PRICING_MEASURE       => 31, // AF
            SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE  => 32, // AG
            SoluteConfig::UM_ATTR_SUBSCRIPTION_PERIOD   => 33, // AH
            SoluteConfig::UM_ATTR_SUBSCRIPTION_LENGTH   => 34, // AI
            SoluteConfig::UM_ATTR_SUBSCRIPTION_VALUE    => 35, // AJ
            SoluteConfig::UM_ATTR_INSTALLMENT           => 36, // AK
            SoluteConfig::UM_ATTR_DLV_COST              => 37, // AL
            SoluteConfig::UM_ATTR_DLV_COST_AT           => 38, // AM
            SoluteConfig::UM_ATTR_DLV_TIME              => 39, // AN
            SoluteConfig::UM_ATTR_AVAILABILITY_DATE     => 40, // AO
            SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT    => 41, // AP
            SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => 42, // AQ
            SoluteConfig::UM_ATTR_PROMO_TEXT            => 43, // AR
            SoluteConfig::UM_ATTR_VOUCHER_TEXT          => 44, // AS
            SoluteConfig::UM_ATTR_SPECIAL               => 45, // AT
            SoluteConfig::UM_ATTR_ENERGY_CLASS          => 46, // AU
            SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN      => 47, // AV
            SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX      => 48, // AW
            SoluteConfig::UM_ATTR_ENERGY_CLASS_ILLUMIN  => 49, // AX
            SoluteConfig::UM_ATTR_CONDITION             => 50, // AY
            SoluteConfig::UM_ATTR_ITEM_GROUP_ID         => 51, // AZ
            SoluteConfig::UM_ATTR_COMPATIBLE_PRODUCT    => 52, // BA
            SoluteConfig::UM_ATTR_QUANTITY_NUMBER       => 53, // BB
            SoluteConfig::UM_ATTR_IS_BUNDLE             => 54, // BC
            SoluteConfig::UM_ATTR_SIZE                  => 55, // BD
            SoluteConfig::UM_ATTR_SIZE_SYSTEM           => 56, // BE
            SoluteConfig::UM_ATTR_COLOR                 => 57, // BF
            SoluteConfig::UM_ATTR_GENDER                => 58, // BG
            SoluteConfig::UM_ATTR_MATERIAL              => 59, // BH
            SoluteConfig::UM_ATTR_PATTERN               => 60, // BI
            SoluteConfig::UM_ATTR_AGE_RATING            => 61, // BJ
            SoluteConfig::UM_ATTR_AGE_GROUP             => 62, // BK
            SoluteConfig::UM_ATTR_ADULT                 => 63, // BL
            SoluteConfig::UM_ATTR_PLATFORM              => 64, // BM
            SoluteConfig::UM_ATTR_PRODUCT_TYPE          => 65, // BN
            SoluteConfig::UM_ATTR_STYLE                 => 66, // BO
            SoluteConfig::UM_ATTR_PROPERTIES            => 67, // BP
            SoluteConfig::UM_ATTR_FUNCTIONS             => 68, // BQ
            SoluteConfig::UM_ATTR_EQUIPMENT             => 69, // BR
            SoluteConfig::UM_ATTR_DEPTH                 => 70, // BS
            SoluteConfig::UM_ATTR_PZN                   => 71, // BT
            SoluteConfig::UM_ATTR_WET_GRIP              => 72, // BU
            SoluteConfig::UM_ATTR_FUEL_EFFICIENCY       => 73, // BV
            SoluteConfig::UM_ATTR_ROLLING_NOISE         => 74, // BW
            SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS   => 75, // BX
            SoluteConfig::UM_ATTR_HSN_TSN               => 76, // BY
            SoluteConfig::UM_ATTR_SPH                   => 77, // BZ
            SoluteConfig::UM_ATTR_DIA                   => 78, // CA
            SoluteConfig::UM_ATTR_BC                    => 79, // CB
            SoluteConfig::UM_ATTR_CYL                   => 80, // CC
            SoluteConfig::UM_ATTR_AXIS                  => 81, // CD
            SoluteConfig::UM_ATTR_ADD                   => 82, // CE
        ];
    }

    /**
     * @return int[]
     */
    private function getCategoryMapping(): array
    {
        return [
            self::col_Rootcategory      => 4, // E
            self::col_Category_Level_1  => 5, // F
            self::col_Category_Level_2  => 6, // G
            self::col_Category_Level_3  => 7, // H
        ];
    }
}
