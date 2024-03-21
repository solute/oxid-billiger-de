<?php

namespace UnitM\Solute\Model;

use OxidEsales\Eshop\Core\Registry;
use UnitM\Solute\Core\SoluteConfig;

class SoluteSession implements SoluteConfig
{
    /**
     * @var string
     */
    public string $clickId = '';

    /**
     * @var int
     */
    public int $timestamp = 0;

    /**
     * @var string
     */
    public string $url = '';

    /**
     * @return bool
     */
    public function getSoluteDataFromSession(): bool
    {
        $this->clickId = (string) Registry::getSession()->getVariable(SoluteConfig::SOLUTE_CLICK_ID) ?: '';
        $this->timestamp = (int) Registry::getSession()->getVariable(SoluteConfig::SOLUTE_TIMESTAMP) ?: 0;
        $this->url = (string) Registry::getSession()->getVariable(SoluteConfig::SOLUTE_URL) ?: '';

        return $this->isDataSet();
    }

    /**
     * @return void
     */
    public function setSoluteDataToSession(): void
    {
        Registry::getSession()->setVariable(SoluteConfig::SOLUTE_CLICK_ID, $this->clickId);
        Registry::getSession()->setVariable(SoluteConfig::SOLUTE_TIMESTAMP, $this->timestamp);
        Registry::getSession()->setVariable(SoluteConfig::SOLUTE_URL, $this->url);
    }

    /**
     * @return void
     */
    public function removeSoluteDataFromSession(): void
    {
        Registry::getSession()->deleteVariable(SoluteConfig::SOLUTE_CLICK_ID);
        Registry::getSession()->deleteVariable(SoluteConfig::SOLUTE_TIMESTAMP);
        Registry::getSession()->deleteVariable(SoluteConfig::SOLUTE_URL);
    }

    /**
     * @return bool
     */
    public function isSoluteDataExpired(): bool
    {
        $now = time();
        if (($now - $this->timestamp) > SoluteConfig::SOLUTE_SESSION_TIMEOUT) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function isDataSet(): bool
    {
        if (!empty($this->clickId) && !empty($this->timestamp) && !empty($this->url)) {
            return true;
        }

        return false;
    }
}
