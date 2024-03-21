<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Model\RestApi;
use UnitM\Solute\Core\SoluteConfig;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnitM\Solute\Controller\Ajax\AjaxTrait;

class SoluteAjaxCheckArticleOnApi extends FrontendController
{
    use AjaxTrait;

    /**
     * @var AjaxBase
     */
    private AjaxBase $ajaxBase;

    /**
     *
     */
    public function __construct()
    {
        require_once(__DIR__ . '/../../Core/SoluteConfig.php');
        $this->ajaxBase = new AjaxBase();
        parent::__construct();
    }

    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws NotFoundExceptionInterface
     */
    public function run(): string
    {
        $articleListJson = (string) Registry::get(Request::class)->getRequestParameter('articleList') ? : '';
        $articleListRaw = json_decode($articleListJson, true);

        $batchData = [];
        foreach ($articleListRaw['data'] as $item) {
            $batchData[] = $item['articleId'];
        }

        $api = new RestApi();
        $api->setBatchData($batchData);
        $api->check();

        $response = $this->convertResponse($api->getResponse());

        echo json_encode($response);
        die;
    }

    /**
     * @param array $response
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function convertResponse(array $response): array
    {
        return $this->convertResponseDetail($response, SoluteConfig::UM_LOG_API_CHECK);
    }
}
