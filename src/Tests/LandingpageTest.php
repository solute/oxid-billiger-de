<?php

use UnitM\Solute\Model\RestApi;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LandingpageTest
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
        $availability = 1;
        $articlePrice = 879.00;

        $restApi = new RestApi();
        $restApi->sendLanding($url, $availability, $articlePrice);
        if ($restApi->getResponseState() === $restApi::STATE_NO_CONTENT) {
            return "Test sending landingpage OK.\n";
        }
        return "Test sending landingpage failed. State: " . $restApi->getResponseState() . "\n";
    }
}

if (php_sapi_name() === 'cli') {
    require_once(__DIR__ . '/../../../../../bootstrap.php');
    $send = new LandingpageTest();
    echo $send->run();
}
