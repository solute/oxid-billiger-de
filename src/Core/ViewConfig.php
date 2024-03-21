<?php

namespace UnitM\Solute\Core;

use OxidEsales\Eshop\Core\Registry;

class ViewConfig extends ViewConfig_parent
{
    /**
     * @return string|null
     */
    public function getShopUrl()
    {
        return Registry::getConfig()->getShopUrl();
    }
}
