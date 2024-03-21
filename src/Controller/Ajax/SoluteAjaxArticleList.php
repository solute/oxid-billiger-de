<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Service\ModuleSettingsInterface;
use UnitM\Solute\Traits\ServiceContainer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SoluteAjaxArticleList extends FrontendController
{
    use ServiceContainer;

    /**
     * @var int
     */
    private int $limit;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../Core/SoluteConfig.php');
        $moduleSettings = $this->getServiceFromContainer(ModuleSettingsInterface::class);
        $this->limit = $moduleSettings->getCountArticleList();

        parent::__construct();
    }

    /**
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run(): string
    {
        $start  = (int) Registry::get(Request::class)->getRequestParameter('start') ? : 0;
        $shopId = (int) Registry::get(Request::class)->getRequestParameter('shopId') ? : 1;

        $ajaxBase = new AjaxBase();
        $articleCount = $ajaxBase->getArticleCount($shopId);
        $result = $ajaxBase->getArticleList($shopId, $this->limit, $start);
        $data = $ajaxBase->convertMessage($result);

        $response = [
            'data'      => $data,
            'limit'     => $this->limit,
            'max'       => $articleCount,
            'translation' => [
                'headline'  => [
                    'articlenumber'     => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_VALIDATION_LIST_ARTICLENUMBER'),
                    'category'          => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_VALIDATION_LIST_CATEGORY'),
                    'title'             => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_VALIDATION_LIST_TITLE'),
                    'shortdescription'  => Registry::getLang()->translateString('UMSOLUTE_HEADLINE_VALIDATION_LIST_SHORTDESCRIPTION'),
                ],
            ],
        ];
        echo json_encode($response);
        die;
    }
}
