<?php

namespace UnitM\Solute\Tests;

use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\FeedItem;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

class ValidatorTest // implements SoluteConfig
{
    /**
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): void
    {
        echo "Start Test\n";
        $countTestCase = 0;
        $countValid = 0;
        $countError = 0;
        $timeStart = microtime(true);

        $validatorList = $this->getSchemaList();
        $feetItem = new FeedItem($validatorList);

        $testCaseList = $this->getTestCases();
        foreach ($testCaseList as $testItem) {
            $feetItem->setFeedLine($testItem['testData']);
            $singleTest = false;
            if (array_key_exists('singleTest', $testItem)) {
                $singleTest = $testItem['singleTest'] ?: false;
            }

            $feetItem->setSingleTest($singleTest);

            if ($feetItem->checkData() === $testItem['expectedResult']) {
                echo $testItem['case'] . " - OK\n";
                $countValid++;
            } else {
                echo $testItem['case'] . " - ERROR. Test failed.\n";
                $countError++;
            }

            $countTestCase++;
        }

        $timeEnd = microtime(true);
        $executionTime = $timeEnd - $timeStart;
        $executionTimePerTest = $executionTime / $countTestCase;


        echo "-----------------\n\n";
        echo "Executed Tests: " . $countTestCase . "\n";
        echo "Valid Tests: " . $countValid . "\n";
        echo "Error Tests: " . $countError . "\n";
        echo "Execution time at all: " . number_format($executionTime, 4) . "s\n";
        echo "Average Execution time per test: " . number_format($executionTimePerTest, 4) . "s\n";
        echo "Test End.\n\n";

        $errorLog = $feetItem->getErrorLog();
        if (!empty($errorLog)) {
            echo "Validation Log:\n";
            echo "-----------------\n";
            echo str_replace("\n", '<br />', $errorLog);
        }
    }

    /**
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function getSchemaList(): array
    {
        $select = "SELECT * FROM `" . SoluteConfig::UM_TABLE_ATTRIBUTE_SCHEMA . "`";
        $database = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $result = $database->getAll($select);
        $list = [];
        foreach ($result as $item) {
            $list[$item[SoluteConfig::UM_COL_PRIMARY_NAME]] = $item;
        }
        return $list;
    }

    /**
     * @return array
     */
    private function getTestCases(): array
    {
        return [

            // required Field -------------------------
            [
                'case'              => 'Required Field - 001',
                'testData'          => [SoluteConfig::UM_ATTR_AID => '254863-XY'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => 'Required Field - 100',
                'testData'          => [SoluteConfig::UM_ATTR_AID => ''],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // modified date ------------------------
            [ // full value
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T10:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // without timezone
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T10:35:00'],
                'expectedResult'    => true,
            ],
            [ // diffrent timezone
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T10:35:00Z'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // leap day
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2020-02-29T10:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // wrong month
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-13-25T10:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong day
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-34T10:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // not existing day
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-04-31T10:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // not existing leap day
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2021-02-29T10:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong timezone format
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 104',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T10:35:00+1'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong time format
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 105',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T10:35'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // no time
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 106',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong hour
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 107',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T24:35:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong minute
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 108',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T04:62:00+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong second
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 109',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T04:12:70+02:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // not existing timezone
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 110',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T04:12:00+15:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // not existing timezone
                'case'              => SoluteConfig::UM_ATTR_MODIFIED_DATE . ' - 111',
                'testData'          => [SoluteConfig::UM_ATTR_MODIFIED_DATE => '2019-02-25T04:12:00+02:15'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // EAN ---------------------------------------
            [    // GTIN-13
                'case'              => SoluteConfig::UM_ATTR_GTIN . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_GTIN => '3001234567892'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [   // ISBN
                'case'              => SoluteConfig::UM_ATTR_GTIN . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_GTIN => '978-1455582341'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [   // GTIN-14
                'case'              => SoluteConfig::UM_ATTR_GTIN . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_GTIN => '10856435001702'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [   // GTIN-12
                'case'              => SoluteConfig::UM_ATTR_GTIN . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_GTIN => '323456789012'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],

            // Sale price effective date  ------------------------------
            [ // with end time
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-02-24T13:00-0800/2016-02-29T15:30-0800'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // without end time
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-02-24T13:00-0800'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // only end date without time
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-02-24T13:00-0800/2016-02-29'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong time format
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-02-24 13:00:00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong month value
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-14-24T13:00-0800'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong day value
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-10-34T13:00-0800'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong date
                'case'              => SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE . ' - 104',
                'testData'          => [SoluteConfig::UM_ATTR_SALE_PRICE_EFFECTIVE => '2016-02-30T13:00-0800'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // pricing measure  ------------------------------
            [ // integer value
                'case'              => SoluteConfig::UM_ATTR_PRICING_MEASURE . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_MEASURE => '500 ml'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // without space
                'case'              => SoluteConfig::UM_ATTR_PRICING_MEASURE . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_MEASURE => '250kg'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // float value
                'case'              => SoluteConfig::UM_ATTR_PRICING_MEASURE . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_MEASURE => '5.9 qm'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // wrong decimal devider
                'case'              => SoluteConfig::UM_ATTR_PRICING_MEASURE . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_MEASURE => '5,9 qm'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // not supported unit
                'case'              => SoluteConfig::UM_ATTR_PRICING_MEASURE . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_MEASURE => '500 t'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // pricing base measure  ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '100 ml'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '10 g'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // without space
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '1l'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // special values
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '75cl'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // special values
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 005',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '1000 kg'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // wrong value
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '7 mg'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong unit
                'case'              => SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_PRICING_BASE_MEASURE => '1 t'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // installment  ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_INSTALLMENT . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_INSTALLMENT => '12:35.00'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // minimum
                'case'              => SoluteConfig::UM_ATTR_INSTALLMENT . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_INSTALLMENT => '1:0.01'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // possible maximum
                'case'              => SoluteConfig::UM_ATTR_INSTALLMENT . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_INSTALLMENT => '9876:12345.67'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // no float at the end
                'case'              => SoluteConfig::UM_ATTR_INSTALLMENT . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_INSTALLMENT => '2:1'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // float at the front
                'case'              => SoluteConfig::UM_ATTR_INSTALLMENT . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_INSTALLMENT => '2.3:24.00'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong decimal devider
                'case'              => SoluteConfig::UM_ATTR_INSTALLMENT . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_INSTALLMENT => '12:24,50'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // Stock quantity ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '1'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '1678'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // float
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '1.68'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong float, no int
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '34,8'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // with unit
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '2 Stück'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // with alphanumeric
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 104',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '4x'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // leading zero
                'case'              => SoluteConfig::UM_ATTR_STOCK_QUANTITY . ' - 105',
                'testData'          => [SoluteConfig::UM_ATTR_STOCK_QUANTITY => '0168'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // link -------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https://shop-name.de/product/15153 ?utm_source=billiger.de&utm_medium=cpc&utm_campaign=Preisvergleich&ref=132'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https://beispieltracker.com/xfgrkgmAfGeBLhG'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https://shop-name.de/product/15153'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https://www.shop-name.de/product/15153'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // no ssl
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https://shop-name.de/product/15153'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // no colon
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https//shop-name.de/product/15153'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // one slash
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https:/shop-name.de/product/15153'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // no domain
                'case'              => SoluteConfig::UM_ATTR_LINK . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_LINK => 'https://shop-namede/product/15153'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // shop_cat ----------------------------
            [ // > separated
                'case'              => SoluteConfig::UM_ATTR_SHOP_CAT . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_SHOP_CAT => 'Küche, Haushalt & Wohnen > Kaffee, Tee & Espresso > Espressokocher'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // ; separated
                'case'              => SoluteConfig::UM_ATTR_SHOP_CAT . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_SHOP_CAT => 'Bekleidung & Accessoires; Bekleidung; Oberbekleidung; Mäntel & Jacken'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // root category (no separation sign)
                'case'              => SoluteConfig::UM_ATTR_SHOP_CAT . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_SHOP_CAT => 'Küche, Haushalt & Wohnen'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // last sign is separator >
                'case'              => SoluteConfig::UM_ATTR_SHOP_CAT . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_SHOP_CAT => 'Küche, Haushalt & Wohnen>'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // last sign is separator ;
                'case'              => SoluteConfig::UM_ATTR_SHOP_CAT . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_SHOP_CAT => 'Küche, Haushalt & Wohnen;'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // google shop cat id ---------------------------
            [ // 3 digits
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '123'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // 4 digits
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '1234'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // 5 digits
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '12345'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // 6 digits
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '123456'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // 2 digits
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '56'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // 7 digits
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '5645915'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // float
                'case'              => SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_GOOGLE_PRODUCT_CAT_ID => '56.45'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // voucher text ----------------------------
            [ //
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7SPAREN (7% Rabatt ab 1 EUR – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher)'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // code with special character
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7-SPAREN (7% Rabatt ab 1 EUR – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher)'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // missing space between code and explanation
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7SPAREN(7% Rabatt ab 1 EUR – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher)'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // missing ( between code and explanation
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7SPAREN 7% Rabatt ab 1 EUR – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher)'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // missing ) at the end
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7SPAREN (7% Rabatt ab 1 EUR – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // space in voucher code
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7 SPAREN (7% Rabatt ab 1 EUR – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher)'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // with HTML
                'case'              => SoluteConfig::UM_ATTR_VOUCHER_TEXT . ' - 104',
                'testData'          => [SoluteConfig::UM_ATTR_VOUCHER_TEXT => '7SPAREN (7% Rabatt <b>ab 1 EUR</b> – 10.000 EUR Einkaufswert, keine Einlösung auf Bücher)'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // energy class -------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A+++'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A++'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A+'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 005',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'B'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 006',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'C'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 007',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'D'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 008',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'E'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 009',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'F'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 010',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'G'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'G+'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_ENERGY_CLASS . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A-B'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // rolling noise -------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_ROLLING_NOISE . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_ROLLING_NOISE => '72dB'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // with space
                'case'              => SoluteConfig::UM_ATTR_ROLLING_NOISE . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_ROLLING_NOISE => '36 dB'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // 3 digits value
                'case'              => SoluteConfig::UM_ATTR_ROLLING_NOISE . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_ROLLING_NOISE => '136 dB'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // double space
                'case'              => SoluteConfig::UM_ATTR_ROLLING_NOISE . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_ROLLING_NOISE => '72  dB'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong unit
                'case'              => SoluteConfig::UM_ATTR_ROLLING_NOISE . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_ROLLING_NOISE => '72 DB'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // float value
                'case'              => SoluteConfig::UM_ATTR_ROLLING_NOISE . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_ROLLING_NOISE => '72.5 dB'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],


            // ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '-2.75'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '-1.5'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '+2.25'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '2.25'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 005',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '0'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 006',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '04.00'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 007',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '5'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '+2,25'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '-45'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '80'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_SPH . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_SPH => '2.33'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            // ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '13.0'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '14.4'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '15.0'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '12'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '15.5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '-8.5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_DIA . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_DIA => '9.05'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            // ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '8.3'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '8.9'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '9.0'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '9'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 005',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '+8.6'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '-5.0'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '5.0'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '9.1'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_BC . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_BC => '8,4'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '-0.25'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '-2.75'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '-5'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '-10'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // more than max
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '0'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // less than min
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '-12'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // positive value
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '3'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong step
                'case'              => SoluteConfig::UM_ATTR_CYL . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_CYL => '-6.1'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // axis ------------------------------
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '20'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '130'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '0'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 004',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '180'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '-5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '7'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '200'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [
                'case'              => SoluteConfig::UM_ATTR_AXIS . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_AXIS => '20.0'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // add ------------------------------
            [ // min
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 001',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '1'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // max
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 002',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '4'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // step
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 003',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '2.5'],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // less than min
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 100',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '0.5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // more than max
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 101',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '4.5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong step
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 102',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '2.25'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // negative value
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 103',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '-2.5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // wrong decimal devider
                'case'              => SoluteConfig::UM_ATTR_ADD . ' - 104',
                'testData'          => [SoluteConfig::UM_ATTR_ADD => '2,5'],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // Condition field tests ---------------------------------
            // #01
            [ // gtin
                'case'              => 'Condition 01 GTIN/MPN - 001',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_MPN => '',
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // gtin + mpn
                'case'              => 'Condition 01 GTIN/MPN - 002',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_MPN => '12mx200hl',
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // only mpn
                'case'              => 'Condition 01 GTIN/MPN - 003',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '',
                    SoluteConfig::UM_ATTR_MPN => '12mx200hl',
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // no values
                'case'              => 'Condition 01 GTIN/MPN - 100',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '',
                    SoluteConfig::UM_ATTR_MPN => '',
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // #02
            [ // both values are set
                'case'              => 'Condition 02 voucher - 001',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_VOUCHER_TEXT => '9DAYS (TEST)',
                    SoluteConfig::UM_ATTR_VOUCHER_PRICE => '13.00',
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // no value is set
                'case'              => 'Condition 02 voucher - 002',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_VOUCHER_TEXT => '',
                    SoluteConfig::UM_ATTR_VOUCHER_PRICE => '',
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // only text is set
                'case'              => 'Condition 02 voucher - 100',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_VOUCHER_TEXT => '9DAYS (TEST)',
                    SoluteConfig::UM_ATTR_VOUCHER_PRICE => '',
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // #03
            [ // all values are set + full range
                'case'              => 'Condition 03 energy label - 001',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A+++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'G'
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // nothing is set
                'case'              => 'Condition 03 energy label - 002',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => '',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => '',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => ''
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // range border high
                'case'              => 'Condition 03 energy label - 003',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'E'
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // range border low
                'case'              => 'Condition 03 energy label - 004',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'E',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'E'
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // all equal
                'case'              => 'Condition 03 energy label - 005',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'C',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'C',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'C'
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // class is not set
                'case'              => 'Condition 03 energy label - 100',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => '',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A+++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'G'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // class min is not set
                'case'              => 'Condition 03 energy label - 101',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A+++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => '',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'G'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // class max is not set
                'case'              => 'Condition 03 energy label - 102',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A+++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A+++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => ''
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // class min and max are not set
                'case'              => 'Condition 03 energy label - 103',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A+++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => '',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => ''
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // range min > max
                'case'              => 'Condition 03 energy label - 104',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'B',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'A++'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // range value < min
                'case'              => 'Condition 03 energy label - 105',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'A++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'D'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // range value < min
                'case'              => 'Condition 03 energy label - 106',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS => 'E',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MIN => 'A++',
                    SoluteConfig::UM_ATTR_ENERGY_CLASS_MAX => 'B'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // #04
            [ // all values are set
                'case'              => 'Condition 04 wheels - 001',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => 'A',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => 'B',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '72 dB',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => '1'
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // no values are set
                'case'              => 'Condition 04 wheels - 002',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => '',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => '',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => ''
                ],
                'singleTest'        => true,
                'expectedResult'    => true,
            ],
            [ // wet grip is missing
                'case'              => 'Condition 04 wheels - 100',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => '',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => 'B',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '72 dB',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => '1'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // fuel efficiency is missing
                'case'              => 'Condition 04 wheels - 101',
                'testData'          => [
                    SoluteConfig::UM_ATTR_WET_GRIP => 'A',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => '',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '72 dB',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => '1'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // rooling noise is missing
                'case'              => 'Condition 04 wheels - 102',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => 'A',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => 'B',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => '1'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // rolling noise class
                'case'              => 'Condition 04 wheels - 103',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => 'A',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => 'B',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '72 dB',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => ''
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // two values are missing
                'case'              => 'Condition 04 wheels - 104',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => '',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => 'B',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => '1'
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],
            [ // three values are missing
                'case'              => 'Condition 04 wheels - 105',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_WET_GRIP => '',
                    SoluteConfig::UM_ATTR_FUEL_EFFICIENCY => 'B',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE => '',
                    SoluteConfig::UM_ATTR_ROLLING_NOISE_CLASS => ''
                ],
                'singleTest'        => true,
                'expectedResult'    => false,
            ],

            // required fields ------------------
            [ // all values are set
                'case'              => 'Required Fields - 001',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_AID => 'ABCDEF6789',
                    SoluteConfig::UM_ATTR_NAME => 'Produktbezeichnung',
                    SoluteConfig::UM_ATTR_DESCRIPTION => 'Langbeschreibung des Artikels',
                    SoluteConfig::UM_ATTR_LINK => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_TARGET_URL => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_IMAGES => 'https://www.shop.de/images/image-1.jpg,https://www.shop.de/images/image-2.jpg',
                    SoluteConfig::UM_ATTR_PRICE => '29.98',
                    SoluteConfig::UM_ATTR_DLV_COST => '5.99',
                    SoluteConfig::UM_ATTR_DLV_TIME => '5 Tage',
                    SoluteConfig::UM_ATTR_SHOP_CAT => 'Hauptkategorie > Unterkategorie > Endkategorie',
                ],
                'expectedResult'    => true,
            ],
            [
                'case'              => 'Required Fields - 100',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '',
                    SoluteConfig::UM_ATTR_AID => 'ABCDEF6789',
                    SoluteConfig::UM_ATTR_NAME => 'Produktbezeichnung',
                    SoluteConfig::UM_ATTR_DESCRIPTION => 'Langbeschreibung des Artikels',
                    SoluteConfig::UM_ATTR_LINK => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_TARGET_URL => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_IMAGES => 'https://www.shop.de/images/image-1.jpg,https://www.shop.de/images/image-2.jpg',
                    SoluteConfig::UM_ATTR_PRICE => '29.98',
                    SoluteConfig::UM_ATTR_DLV_COST => '5.99',
                    SoluteConfig::UM_ATTR_DLV_TIME => '5 Tage',
                    SoluteConfig::UM_ATTR_SHOP_CAT => 'Hauptkategorie > Unterkategorie > Endkategorie',
                ],
                'expectedResult'    => false,
            ],
            [
                'case'              => 'Required Fields - 101',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_AID => '',
                    SoluteConfig::UM_ATTR_NAME => 'Produktbezeichnung',
                    SoluteConfig::UM_ATTR_DESCRIPTION => 'Langbeschreibung des Artikels',
                    SoluteConfig::UM_ATTR_LINK => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_TARGET_URL => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_IMAGES => 'https://www.shop.de/images/image-1.jpg,https://www.shop.de/images/image-2.jpg',
                    SoluteConfig::UM_ATTR_PRICE => '29.98',
                    SoluteConfig::UM_ATTR_DLV_COST => '5.99',
                    SoluteConfig::UM_ATTR_DLV_TIME => '5 Tage',
                    SoluteConfig::UM_ATTR_SHOP_CAT => 'Hauptkategorie > Unterkategorie > Endkategorie',
                ],
                'expectedResult'    => false,
            ],
            [
                'case'              => 'Required Fields - 102',
                'testData'          => [
                    SoluteConfig::UM_ATTR_GTIN => '978-1455582341',
                    SoluteConfig::UM_ATTR_AID => 'ABCDEF6789',
                    SoluteConfig::UM_ATTR_NAME => 'Produktbezeichnung',
                    SoluteConfig::UM_ATTR_DESCRIPTION => '',
                    SoluteConfig::UM_ATTR_LINK => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_TARGET_URL => 'https://www.shop.de/Kategorie/Artikel.html',
                    SoluteConfig::UM_ATTR_IMAGES => 'https://www.shop.de/images/image-1.jpg,https://www.shop.de/images/image-2.jpg',
                    SoluteConfig::UM_ATTR_PRICE => '29.98',
                    SoluteConfig::UM_ATTR_DLV_COST => '5.99',
                    SoluteConfig::UM_ATTR_DLV_TIME => '5 Tage',
                    SoluteConfig::UM_ATTR_SHOP_CAT => 'Hauptkategorie > Unterkategorie > Endkategorie',
                ],
                'expectedResult'    => false,
            ],
        ];
    }
}

if (php_sapi_name() === 'cli') {
    require_once(__DIR__ . '/../../../../../source/bootstrap.php');
    $test = new ValidatorTest();
    $test->run();
}
