<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\Logger;
use UnitM\Solute\Model\RestApi;
use UnitM\Solute\Model\SoluteSession;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Config extends Config_parent
{
    private const HTTP_USER_AGENT   = 'HTTP_USER_AGENT';
    private const REQUEST_SCHEME    = 'REQUEST_SCHEME';
    private const SERVER_NAME       = 'SERVER_NAME';
    private const REQUEST_URI       = 'REQUEST_URI';
    private const NEEDLE_BOT        = 'bot';
    private const ARTICLE_OXID      = 'anid';

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function initializeShop(): void
    {
        parent::initializeShop();
        $this->setSoluteData();
    }

    /**
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function setSoluteData(): void
    {
        if ($this->isBot()) {
            return;
        }
        $session = new SoluteSession();
        if ($session->getSoluteDataFromSession() && $session->isSoluteDataExpired()) {
            $session->removeSoluteDataFromSession();
        }

        $soluteClickId = $this->getSoluteClickId();
        if (empty($soluteClickId)) {
            return;
        }

        $url = $_SERVER[self::REQUEST_SCHEME] . '://' . $_SERVER[self::SERVER_NAME] . $_SERVER[self::REQUEST_URI];

        $session->clickId = $soluteClickId;
        $session->timestamp = time();
        $session->url = $url;
        $session->setSoluteDataToSession();

        $this->sendDataToSolute($session);
    }

    /**
     * @param SoluteSession $session
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function sendDataToSolute(SoluteSession $session): void
    {
        $articleOxid = Registry::get(Request::class)->getRequestParameter(self::ARTICLE_OXID) ?: '';
        if (empty($articleOxid)) {
            Logger::addLog('No article oxid given.', SoluteConfig::UM_LOG_ERROR);
            return;
        }

        $article = oxNew(Article::class);
        if (!$article->load($articleOxid)) {
            Logger::addLog('Could not load article with id ' . $articleOxid, SoluteConfig::UM_LOG_ERROR);
            return;
        }

        $articlePrice = $article->getPrice()->getPrice();
        $availability = 0;
        if ($article->getStockStatus() >= 0) {
            $availability = 1;
        }

        $restApi = new RestApi();
        $restApi->sendLanding($session->url, $availability, $articlePrice);
    }

    /**
     * @return bool
     */
    private function isBot(): bool
    {
        if (!array_key_exists(self::HTTP_USER_AGENT, $_SERVER)) {
            return false;
        }

        $userAgent = strtolower($_SERVER[self::HTTP_USER_AGENT]);
        if (strstr($userAgent, self::NEEDLE_BOT)) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getSoluteClickId(): string
    {
        if (array_key_exists(SoluteConfig::SOLUTE_CLICK_ID, $_REQUEST)) {
            return (string)$_REQUEST[SoluteConfig::SOLUTE_CLICK_ID];
        }

        return '';
    }
}
