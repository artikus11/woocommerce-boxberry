<?php
/**
 *
 *  * This file is part of Boxberry Api.
 *  *
 *  * (c) 2016, T. I. R. Ltd.
 *  * Evgeniy Mosunov, Alexander Borovikov
 *  *
 *  * For the full copyright and license information, please view LICENSE
 *  * file that was distributed with this source code
 *  *
 *  * File: Client.php
 *  * Created: 26.07.2016
 *  *
 */

namespace Boxberry\Client;

use Boxberry\Client\Exceptions\BadSettingsException;
use Boxberry\Client\Exceptions\UnknownTypeException;
use Boxberry\Requests;
use boxberry\Requests\DadataSuggestionsRequest;
use Boxberry\Requests\DeliveryCalculationRequest;
use Boxberry\Requests\Request;

/**
 * Базовый класс, который создает объекты запросов и исполняет их.
 * Class Client
 * @package Boxberry\Client
 */
class Client
{
    /**
     * @var string
     */
    protected $key = null;

    /**
     * @var string
     */
    protected $api_url;

    /**
     * @var string
     */
    protected $production_url = 'https://api.boxberry.ru/json.php';

    /**
     * @var string
     */
    protected $debug_url = 'https://test-ecom.boxberry.ru/json.php';
    /**
     * @var string
     */
    protected $dadataApiUrl = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';
    /**
     * @var string
     */
    protected $dadataApiToken = 'Token a105367bc6479ffb2a355fad7536e0fb504c1b97';

    /**
     * @var bool
     */
    protected $debug_mode_enabled;

    public function __construct()
    {
        $this->api_url = $this->production_url;
    }

    public function enableDebugMode()
    {
        $this->api_url = $this->debug_url;
        $this->debug_mode_enabled = true;
    }

    public function disableDebugMode()
    {
        $this->api_url = $this->production_url;
        $this->debug_mode_enabled = false;
    }

    public function isDebugModeEnabled()
    {
        return $this->debug_mode_enabled;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->api_url;
    }

    /**
     * @param string $api_url
     */
    public function setApiUrl($api_url)
    {
        if ($this->isDebugModeEnabled()) {
            $this->debug_url = $api_url;
        } else {
            $this->production_url = $api_url;
        }
        $this->api_url = $api_url;
    }

    public function getDadataHeaders() {
        return [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => $this->dadataApiToken
        ];
    }

    /**
     * @return DadataSuggestionsRequest
     */
    public function getDadataSuggestions()
    {
        return new Requests\DadataSuggestionsRequest();
    }

    /**
     * @return DeliveryCalculationRequest
     */
    public function getDeliveryCalculation()
    {
        return new Requests\DeliveryCalculationRequest();
    }

    /**
     * @return Requests\CourierListCitiesRequest
     */
    public static function getCourierListCities()
    {
        return new Requests\CourierListCitiesRequest();
    }

    /**
     * @return Requests\DeliveryCostsRequest
     */
    public function getDeliveryCosts()
    {
        return new Requests\DeliveryCostsRequest();
    }

    /**
     * @return Requests\ListCitiesRequest
     */
    public static function getListCities()
    {
        return new Requests\ListCitiesRequest();
    }

    /**
     * @return Requests\ListCitiesFullRequest
     */
    public function getListCitiesFull()
    {
        return new Requests\ListCitiesFullRequest();
    }

    /**
     * @return Requests\ListPointsRequest
     */
    public static function getListPoints()
    {
        return new Requests\ListPointsRequest();
    }

    /**
     * @return Requests\ListServicesRequest
     */
    public static function getListServices()
    {
        return new Requests\ListServicesRequest();
    }

    /**
     * @return Requests\ListStatusesRequest
     */
    public static function getListStatuses()
    {
        return new Requests\ListStatusesRequest();
    }

    /**
     * @return Requests\ListStatusesFullRequest
     */
    public static function getListStatusesFull()
    {
        return new Requests\ListStatusesFullRequest();
    }

    /**
     * @return Requests\ListZipsRequest
     */
    public static function getListZips()
    {
        return new Requests\ListZipsRequest();
    }

    /**
     * @return Requests\ZipCheckRequest
     */
    public static function getZipCheck()
    {
        return new Requests\ZipCheckRequest();
    }

    /**
     * @return Requests\ParselCheckRequest
     */
    public static function getParselCheck()
    {
        return new Requests\ParselCheckRequest();
    }

    /**
     * @return Requests\ParselCreateRequest
     */
    public static function getParselCreate()
    {
        return new Requests\ParselCreateRequest();
    }

    /**
     * @return Requests\ParselDelRequest
     */
    public static function getParselDel()
    {
        return new Requests\ParselDelRequest();
    }

    /**
     * @return Requests\ParselListRequest
     */
    public static function getParselList()
    {
        return new Requests\ParselListRequest();
    }

    /**
     * @return Requests\ParselSendRequest
     */
    public static function getParselSend()
    {
        return new Requests\ParselSendRequest();
    }

    /**
     * @return Requests\ParselSendStoryRequest
     */
    public static function getParselSendStory()
    {
        return new Requests\ParselSendStoryRequest();
    }

    /**
     * @return Requests\ParselStoryRequest
     */
    public static function getParselStory()
    {
        return new Requests\ParselStoryRequest();
    }

    public function setDayForPeriod($count, $form1, $form2, $form3)
    {
        $count = abs($count) % 100;
        $lcount = $count % 10;
        if ($count >= 11 && $count <= 19) return($form3);
        if ($lcount >= 2 && $lcount <= 4) return($form2);
        if ($lcount == 1) return($form1);
        return $form3;
    }

    /**
     * @return Requests\PointsByPostcodeRequest
     */
    public static function getPointsByPostCode()
    {
        return new Requests\PointsByPostcodeRequest();
    }

    /**
     * @return Requests\PointsByPostcodeRequest
     */
    public static function getPointsDescription()
    {
        return new Requests\PointsByPostcodeRequest();
    }

    /**
     * @return Requests\PointsForParcelsRequest
     */
    public static function getPointsForParcels()
    {
        return new Requests\PointsForParcelsRequest();
    }

    /**
     * @return Requests\GetKeyIntegrationRequest
     */
    public static function getKeyIntegration()
    {
        return new Requests\GetKeyIntegrationRequest();
    }

    /**
     * @return Requests\WidgetSettingsRequest
     */
    public function getWidgetSettings()
    {
        return new Requests\WidgetSettingsRequest();
    }

    /**
     * Проверяет правильно ли заполнен request, подготавливает запрос для отправки.
     * Возвращается тип, который ожидался в нужном request.
     * @param Request $request
     * @return Object|bool
     * @throws BadSettingsException
     * @throws Requests\Exceptions\RequiredFieldsNullException
     * @throws UnknownTypeException
     * @throws Exceptions\BadResponseException
     */
    public function execute(Request $request)
    {

        if ($this->api_url === null || $this->key === null) {
            throw new BadSettingsException('Проверьте секретный ключ и адрес апи');
        }
        if ($request->checkRequiredFields() === false) {
            throw new Requests\Exceptions\RequiredFieldsNullException('Не все обязательные параметры заполнены');
        }

        $serializer = new Serializer();

        $credentials = [
            'token'  => $this->key,
            'method' => $request->getClassName(),
        ];


        if (property_exists($request, 'method') && $request->method === 'POST')
        {

            $args = [];

            $headers = [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json'
            ];

            if ($request->getClassName() === 'ParselCreate')
            {
                $sdata = array(
                    'sdata' => $serializer->toArray($request)
                );

                $data = array_merge($credentials, $sdata);
                HTTP::setCacheTimeLife(0);
            }

            if ($request->getClassName() === 'DeliveryCalculation')
            {
                $data = array_merge($credentials, $serializer->toArray($request));
                HTTP::setCacheTimeLife(86400);
            }

            if ($request->getClassName() === 'DadataSuggestions')
            {
                $data          = $serializer->toArray($request);
                $headers       = $this->getDadataHeaders();
                $this->api_url = $this->dadataApiUrl;
            }

            $args['headers'] = $headers;
            $args['body'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            $answer = HTTP::post($this->api_url, $args)->getAnswer();

        } else {
            switch ( $credentials['method'] ) {
                case 'WidgetSettings':
                case 'ListCitiesFull':
                case 'GetKeyIntegration':
                    HTTP::setCacheTimeLife( 86400 );
                    break;
                case 'ListStatuses':
                    HTTP::setCacheTimeLife( 1800 );
                    break;
            }

            $data = array_merge($credentials, $serializer->toArray($request));
            $answer = HTTP::get($this->api_url, $data)->getAnswer();

        }

        unset($serializer);
        $answerClass = $request->getResultClass();
        $type = $this->getType($answerClass);

        if ($type === false) {
            throw new UnknownTypeException('Неизвестная ошибка');
        }

        if ($type == 'class') {
            return new $answerClass($answer);
        } elseif ($type == 'bool') {
            return $answer['text'] == 'ok';
        } else {
            throw new UnknownTypeException('Неизвестная ошибка');
        }
    }

    /**
     * Проверяет принадлежит ли возвращаемый тип классу или типу bool, иначе возвращает false
     * @param $type
     * @return bool|string
     */
    protected function getType($type)
    {
        if (class_exists($type)) {
            return 'class';
        } elseif ($type == 'bool') {
            return 'bool';
        }
        return false;
    }
}