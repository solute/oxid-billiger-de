<?php

namespace UnitM\Solute\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminController;
use OxidEsales\Eshop\Core\Registry;

class ValidatorController extends AdminController
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'solute_article_list.tpl';

    /**
     * @return int
     */
    public function getShopId(): int
    {
        return Registry::getConfig()->getShopId();
    }
}
