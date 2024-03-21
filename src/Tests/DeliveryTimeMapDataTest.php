<?php

use OxidEsales\Eshop\Core\Registry;
use UnitM\Solute\Core\SoluteConfig;

class DeliveryTimeMapDataTest
{
    private const STATE_SUCCESS     = 'SUCCESS';
    private const STATE_ERROR       = 'ERROR';
    private const STATE_INFO        = 'INFO';

    /**
     * @var array|true[]
     */
    private array $validDeliveryTimeUnits = [
        SoluteConfig::UM_DELIVERY_TIME_UNIT_DAY     => true,
        SoluteConfig::UM_DELIVERY_TIME_UNIT_WEEK    => true,
        SoluteConfig::UM_DELIVERY_TIME_UNIT_MONTH   => true,
    ];

    public function run(): void
    {
        $this->start();

        foreach ($this->getTestData() as $testCaseKey => $testCase) {
            $generatedResult = $this->getDeliveryTime(
                $testCase['minDeliveryTime'],
                $testCase['maxDeliveryTime'],
                $testCase['deliveryTimeUnit']
            );

            $message = 'TestCase: ' . $testCaseKey . ' | expected: ' . $testCase['result']
                . ' | generated: ' . $generatedResult;

            if ($generatedResult === $testCase['result'] && $testCase['resultState'] === self::STATE_SUCCESS) {
                $this->log($message, self::STATE_SUCCESS);
            } else {
                $this->log($message, self::STATE_ERROR);
            }
        }

        $this->end();
    }

    private function start(): void
    {
        $this->log();
        $this->log('Start test', self::STATE_INFO);
    }

    private function end(): void
    {
        $this->log('End test', self::STATE_INFO);
        $this->log();
    }

    private function log(string $message = '', string $state = ''): void
    {
        if (empty($message)) {
            echo "\n";
            return;
        }

        echo '[' . date('Y-m-d H:i:s') . '] ';

        switch ($state) {
            case '':
            default:
                echo $message . "\n";
                break;

            case self::STATE_SUCCESS:
                echo "\033[32m" . '['. $state . '] ' . $message . "\033[0m" . "\n";
                break;

            case self::STATE_ERROR:
                echo "\033[31m" . '['. $state . '] ' . $message . "\033[0m" . "\n";
                break;

            case self::STATE_INFO:
                echo "\033[33m" . '['. $state . '] ' . $message . "\033[0m" . "\n";
                break;
        }
    }

    /**
     * @param int $minDeliveryTime
     * @param int $maxDeliveryTime
     * @param $deliveryTimeUnit
     * @return string
     */
    private function getDeliveryTime(int $minDeliveryTime, int $maxDeliveryTime, $deliveryTimeUnit): string
    {
        if (empty($deliveryTimeUnit) || !array_key_exists($deliveryTimeUnit, $this->validDeliveryTimeUnits)) {
            return 'error-wrong_time_unit';
        }

        if ($minDeliveryTime === 0 && $maxDeliveryTime === 0) {
            return 'error-min_and_max_no_value';
        }

        if ($maxDeliveryTime !== 0 && $minDeliveryTime > $maxDeliveryTime) {
            return 'error-min_greater_max';
        }

        if (empty($minDeliveryTime) || empty($maxDeliveryTime)) {
            $deliveryTimeValue = $minDeliveryTime + $maxDeliveryTime;
            $unit = $this->getDeliveryUnitTranslation($deliveryTimeUnit, $deliveryTimeValue);

            return $deliveryTimeValue . ' ' . $unit;
        }

        if ($minDeliveryTime === $maxDeliveryTime) {
            $unit = $this->getDeliveryUnitTranslation($deliveryTimeUnit, $maxDeliveryTime);

            return $maxDeliveryTime . ' ' . $unit;
        }

        $unit = $this->getDeliveryUnitTranslation($deliveryTimeUnit, $maxDeliveryTime);

        return $minDeliveryTime . '-' . $maxDeliveryTime . ' ' . $unit;
    }

    /**
     * @param string $unit
     * @param int $amount
     * @return string
     */
    private function getDeliveryUnitTranslation(string $unit, int $amount): string
    {
        if ($amount === 0 || empty($unit)) {
            return '';
        }

        if ($amount === 1) {
            $numerus = SoluteConfig::UM_TRANSLATION_NUMERUS_SINGULAR;
        } else {
            $numerus = SoluteConfig::UM_TRANSLATION_NUMERUS_PLURAL;
        }

        $translationKey = 'UMSOLUTE_' . $unit . '_' . $numerus;

        return (string) Registry::getLang()->translateString($translationKey);
    }

    private function getTestData (): array
    {
        return [
            0 => [ // different times, day plural
                'minDeliveryTime'   => 1,
                'maxDeliveryTime'   => 2,
                'deliveryTimeUnit'  => 'DAY',
                'result'            => '1-2 Tage',
                'resultState'       => self::STATE_SUCCESS
            ],
            1 => [ // different times, week plural
                'minDeliveryTime'   => 3,
                'maxDeliveryTime'   => 4,
                'deliveryTimeUnit'  => 'WEEK',
                'result'            => '3-4 Wochen',
                'resultState'       => self::STATE_SUCCESS
            ],
            2 => [ // different times, month plural
                'minDeliveryTime'   => 5,
                'maxDeliveryTime'   => 6,
                'deliveryTimeUnit'  => 'MONTH',
                'result'            => '5-6 Monate',
                'resultState'       => self::STATE_SUCCESS
            ],
            3 => [ // only min value, week singular
                'minDeliveryTime'   => 1,
                'maxDeliveryTime'   => 0,
                'deliveryTimeUnit'  => 'WEEK',
                'result'            => '1 Woche',
                'resultState'       => self::STATE_SUCCESS
            ],
            4 => [ // only max value, month singular
                'minDeliveryTime'   => 0,
                'maxDeliveryTime'   => 1,
                'deliveryTimeUnit'  => 'MONTH',
                'result'            => '1 Monat',
                'resultState'       => self::STATE_SUCCESS
            ],
            5 => [ // min and max equal value, day singular
                'minDeliveryTime'   => 1,
                'maxDeliveryTime'   => 1,
                'deliveryTimeUnit'  => 'DAY',
                'result'            => '1 Tag',
                'resultState'       => self::STATE_SUCCESS
            ],
            10 => [ // error wrong time unit
                'minDeliveryTime'   => 0,
                'maxDeliveryTime'   => 1,
                'deliveryTimeUnit'  => 'MONTHS',
                'result'            => 'error-wrong_time_unit',
                'resultState'       => self::STATE_SUCCESS
            ],
            11 => [ // error no time unit
                'minDeliveryTime'   => 2,
                'maxDeliveryTime'   => 5,
                'deliveryTimeUnit'  => '',
                'result'            => 'error-wrong_time_unit',
                'resultState'       => self::STATE_SUCCESS
            ],
            12 => [ // error min & max no value
                'minDeliveryTime'   => 0,
                'maxDeliveryTime'   => 0,
                'deliveryTimeUnit'  => 'WEEK',
                'result'            => 'error-min_and_max_no_value',
                'resultState'       => self::STATE_SUCCESS
            ],
            13 => [  // error min & max no value
                'minDeliveryTime'   => 6,
                'maxDeliveryTime'   => 5,
                'deliveryTimeUnit'  => 'MONTH',
                'result'            => 'error-min_greater_max',
                'resultState'       => self::STATE_SUCCESS
            ],
        ];
    }
}

if (php_sapi_name() === 'cli') {
    require_once(__DIR__ . '/../../../../../bootstrap.php');
    $deliveryTimeMapDataTest = new DeliveryTimeMapDataTest();
    $deliveryTimeMapDataTest->run();
}
