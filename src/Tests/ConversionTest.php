<?php

use UnitM\Solute\Model\RestApi;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ConversionTest
{
    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(): string
    {
        $host = 'https://oxid65ce-mamp.loc/';
        $url = $host . 'Kiteboarding/Kites/Kite-CORE-GTS.html?soluteclid=10ae2359b7fa4f0399c16db8e7575dc2';
        $netOrderValue = 234.56;
        $orderId = 'fb598cb494b6e98253995d800fab908d';

        $restApi = new RestApi();
        $restApi->sendConversion($url, $netOrderValue, $orderId);
        if ($restApi->getResponseState() === $restApi::STATE_NO_CONTENT) {
            return "Test sending conversion OK.\n";
        }
        return "Test sending conversion failed. State: " . $restApi->getResponseState() . "\n";
    }
}

if (php_sapi_name() === 'cli') {
    require_once(__DIR__ . '/../../../../../bootstrap.php');
    $send = new ConversionTest();
    echo $send->run();
}
