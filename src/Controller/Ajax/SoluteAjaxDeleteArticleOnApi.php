<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use UnitM\Solute\Core\SoluteConfig;
use UnitM\Solute\Model\RestApi;
use UnitM\Solute\Controller\Ajax\AjaxBase;
use UnitM\Solute\Model\Hash;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SoluteAjaxDeleteArticleOnApi extends FrontendController
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
        $api->delete();

        $response = $this->convertResponse($api->getResponse());
        $this->deleteHashes($batchData);

        echo json_encode($response);
        die;
    }

    /**
     * @param array $articleList
     * @return void
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function deleteHashes(array $articleList): void
    {
        $hash = new Hash();
        foreach ($articleList as $articleId) {
            $hash->deleteHash($articleId);
        }
        $hash->persist();
    }

    /**
     * @param array $response
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function convertResponse(array $response): array
    {
        return $this->convertResponseDetail($response, SoluteConfig::UM_LOG_API_DELETE);
    }
}
