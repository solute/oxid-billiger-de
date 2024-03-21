<?php

namespace UnitM\Solute\Controller\Ajax;

use OxidEsales\Eshop\Core\Registry;
use UnitM\Solute\Core\RestConfig;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;

trait AjaxTrait
{
    /**
     * @param array $responseErrorList
     * @return array
     */
    private function checkRestApiResponseError(array $responseErrorList): array
    {
        $convertedErrorList = [];
        foreach ($responseErrorList as $error) {
            switch ($error['code']) {

                case RestConfig::SERVER_STATE_422:
                    $key = $error['loc'][0];
                    if (array_key_exists(2, $error['loc'])) {
                        $key .= '.' . $error['loc'][2];
                    }
                    $text = Registry::getLang()->translateString('UMSOLUTE_LOG_STATE_422')
                        .' State: ' . $error['code'] . ' | ' . $error['msg'] . ' (' . $error['type'] . ')';
                    $convertedErrorList[] = [
                        'key'  => $key,
                        'text' => $text,
                    ];
                    break;

                case RestConfig::SERVER_STATE_403:
                case RestConfig::SERVER_STATE_404:
                    $key = $error['loc'];
                    $text = Registry::getLang()->translateString('UMSOLUTE_LOG_STATE_' . $error['code'])
                        .' State: ' . $error['code'] . ' | ' . $error['msg'];
                    $convertedErrorList[] = [
                        'key'  => $key,
                        'text' => $text,
                    ];
                    break;
            }
        }

        return $convertedErrorList;
    }

    /**
     * @param array $response
     * @param string $eventId
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    private function convertResponseDetail(array $response, string $eventId): array
    {
        $list = [];
        foreach ($response as $responses) {
            foreach ($responses as $item) {
                $result = true;
                $errors = [];
                $articleId = mb_substr($item['document_id'], 0, 32); // = oxarticle.OXID

                if (array_key_exists('errors', $item)) {
                    $errors = $this->checkRestApiResponseError($item['errors']);
                    if (count($errors) > 0) {
                        $result = false;
                    }
                }

                $list[$articleId] = [
                    'result' => $result,
                    'errors' => $errors,
                ];
                $message = '';
                $eventList = $this->ajaxBase->logEvent(
                    $result,
                    $errors,
                    $message,
                    $articleId,
                    $eventId
                );
                $list[$articleId]['log'] = $this->ajaxBase->getArticleEventListLog($eventList);
            }
        }

        return $list;
    }
}
