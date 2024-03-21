<?php

/**
 * Test for check delivery Time
 * only to use from Browser, when copy this file to source folder and execute with file name
 *
 * CAUTION:
 * Be aware, that the method getDeliveryTime of this script is the same as in
 * \UnitM\Solute\Model\RestApi::getDeliveryTime
 * Changes have to be copied manual to this file!
 */
class dlvtest
{
    private const CASE = 'case';
    private const EXPECTED_MIN = 'min';
    private const EXPECTED_MAX = 'max';

    private array $cases = [];

    /**
     *
     */
    public function __construct()
    {
        $this->cases = $this->setTestCases();
    }

    /**
     * @return void
     */
    public function run(): void
    {
        echo '<div style="display:grid; grid-template-columns: 80px 120px 80px 80px 80px 80px 80px;">';

        echo "<div>Testcase</div>";
        echo "<div>Testvalue</div>";
        echo "<div>Expected minimum</div>";
        echo "<div>min handling time</div>";
        echo "<div>Expected maximum</div>";
        echo "<div>max handling time</div>";
        echo "<div>Result Test</div>";

        foreach ($this->cases as $id => $valueset) {
            $conversion = $this->getDeliveryTime($valueset[self::CASE]);

            echo "<div>" . $id . "</div>";
            echo "<div>" . $valueset[self::CASE] . "</div>";
            echo "<div>" . $valueset[self::EXPECTED_MIN] . "</div>";
            echo "<div>" . $conversion[self::EXPECTED_MIN] . "</div>";
            echo "<div>" . $valueset[self::EXPECTED_MAX] . "</div>";
            echo "<div>" . $conversion[self::EXPECTED_MAX] . "</div>";
            echo "<div>";
            if ($this->executeCheck($valueset, $conversion)) {
                echo "OK";
            } else {
                echo "ERROR";
            }
            echo "</div>";
        }

        echo "</div>";
    }

    private function executeCheck(array $valueset, array $conversion): bool
    {
        if (   $valueset[self::EXPECTED_MIN] !== $conversion[self::EXPECTED_MIN]
            || $valueset[self::EXPECTED_MAX] !== $conversion[self::EXPECTED_MAX]
        ) {
            return false;
        }

        return true;
    }

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

    private function setTestCases(): array
    {
        return [
            100 => [
                self::CASE          => '7Tag',
                self::EXPECTED_MIN  => 7,
                self::EXPECTED_MAX  => 7,
            ],
            101 => [
                self::CASE          => '7 Tag',
                self::EXPECTED_MIN  => 7,
                self::EXPECTED_MAX  => 7,
            ],
            102 => [
                self::CASE          => '7Tage',
                self::EXPECTED_MIN  => 7,
                self::EXPECTED_MAX  => 7,
            ],
            103 => [
                self::CASE          => '7 Tage',
                self::EXPECTED_MIN  => 7,
                self::EXPECTED_MAX  => 7,
            ],
            110 => [
                self::CASE          => '7',
                self::EXPECTED_MIN  => 7,
                self::EXPECTED_MAX  => 7,
            ],
            111 => [
                self::CASE          => '12',
                self::EXPECTED_MIN  => 12,
                self::EXPECTED_MAX  => 12,
            ],
            120 => [
                self::CASE          => '6Werktage',
                self::EXPECTED_MIN  => 6,
                self::EXPECTED_MAX  => 6,
            ],
            121 => [
                self::CASE          => '6 Werktage',
                self::EXPECTED_MIN  => 6,
                self::EXPECTED_MAX  => 6,
            ],
            122 => [
                self::CASE          => '6Werktag',
                self::EXPECTED_MIN  => 6,
                self::EXPECTED_MAX  => 6,
            ],
            123 => [
                self::CASE          => '6 Werktag',
                self::EXPECTED_MIN  => 6,
                self::EXPECTED_MAX  => 6,
            ],
            200 => [
                self::CASE          => '1-3Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            201 => [
                self::CASE          => '1-3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            202 => [
                self::CASE          => '1- 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            203 => [
                self::CASE          => '1 - 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            210 => [
                self::CASE          => '1 - 3',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            300 => [
                self::CASE          => '1bis3Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            301 => [
                self::CASE          => '1bis3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            302 => [
                self::CASE          => '1bis 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            303 => [
                self::CASE          => '1 bis 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            310 => [
                self::CASE          => '1 bis 3',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],

            400 => [
                self::CASE          => '1Tagbis3Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            401 => [
                self::CASE          => '1Tagbis3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            402 => [
                self::CASE          => '1Tagbis 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            403 => [
                self::CASE          => '1Tag bis 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            404 => [
                self::CASE          => '1 Tag bis 3 Tage',
                self::EXPECTED_MIN  => 1,
                self::EXPECTED_MAX  => 3,
            ],
            405 => [
                self::CASE          => '2 Tage bis 3 Tage',
                self::EXPECTED_MIN  => 2,
                self::EXPECTED_MAX  => 3,
            ],
            500 => [
                self::CASE          => '11 bis 24 Tage',
                self::EXPECTED_MIN  => 11,
                self::EXPECTED_MAX  => 24,
            ],
        ];
    }
}

$test = new dlvtest();
$test->run();
