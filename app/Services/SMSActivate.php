<?php

namespace App\Services;

use Illuminate\Support\Facades\Http; 
use Exception;
use InvalidArgumentException;

class RequestError extends Exception
{
    private $responseCode;

    protected $errorCodes = [
        'ACCESS_ACTIVATION' => 'Сервис успешно активирован',
        'ACCESS_CANCEL' => 'активация отменена',
        'ACCESS_READY' => 'Ожидание нового смс',
        'ACCESS_RETRY_GET' => 'Готовность номера подтверждена',
        'ACCOUNT_INACTIVE' => 'Свободных номеров нет',
        'ALREADY_FINISH' => 'Аренда уже завершена',
        'ALREADY_CANCEL' => 'Аренда уже отменена',
        'BAD_ACTION' => 'Некорректное действие (параметр action)',
        'BAD_SERVICE' => 'Некорректное наименование сервиса (параметр service)',
        'BAD_KEY' => 'Неверный API ключ доступа',
        'BAD_STATUS' => 'Попытка установить несуществующий статус',
        'BANNED' => 'Аккаунт заблокирован',
        'CANT_CANCEL' => 'Невозможно отменить аренду (прошло более 20 мин.)',
        'ERROR_SQL' => 'Один из параметров имеет недопустимое значение',
        'NO_NUMBERS' => 'Нет свободных номеров для приёма смс от текущего сервиса',
        'NO_BALANCE' => 'Закончился баланс',
        'NO_ID_RENT' => 'Не указан id аренды',
        'NO_ACTIVATION' => 'Указанного id активации не существует',
        'STATUS_CANCEL' => 'Активация/аренда отменена',
        'STATUS_FINISH' => 'Аренда оплачена и завершена',
        'STATUS_WAIT_CODE' => 'Ожидание первой смс',
        'STATUS_WAIT_RETRY' => 'ожидание уточнения кода',
        'SQL_ERROR' => 'Один из параметров имеет недопустимое значение',
        'INVALID_PHONE' => 'Номер арендован не вами (неправильный id аренды)',
        'INCORECT_STATUS' => 'Отсутствует или неправильно указан статус',
        'WRONG_SERVICE' => 'Сервис не поддерживает переадресацию',
        'WRONG_SECURITY' => 'Ошибка передачи ID без переадресации',
    ];

    public function __construct($errorCode)
    {
        $this->responseCode = $errorCode;
        parent::__construct($this->errorCodes[$errorCode] ?? 'Unknown error');
    }

    public function getResponseCode()
    {
        return $this->errorCodes[$this->responseCode] ?? $this->responseCode;
    }
}

class ErrorCodes extends RequestError
{
    public function checkExist($errorCode)
    {
        return array_key_exists($errorCode, $this->errorCodes);
    }
}

class SMSActivate
{
    private $url = 'https://api.sms-activate.org/stubs/handler_api.php';
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getPrices($country = null, $service = null)
    {
        $params = [
            'api_key' => $this->apiKey,
            'action' => 'getPrices'
        ];
        if ($country !== null) $params['country'] = $country;
        if ($service) $params['service'] = $service;

        return $this->request($params, 'GET', true);
    }
public function getNumber($service, $country)
{
    $response = Http::get("https://api.sms-activate.ae/stubs/handler_api.php", [
        'api_key' => $this->apiKey,
        'action' => 'getNumber',
        'service' => $service,
        'country' => $country,
    ]);

    $data = $response->body();

    if (str_starts_with($data, 'ACCESS_NUMBER')) {
        [$status, $id, $number] = explode(':', $data);
        return [
            'id' => $id,
            'number' => $number
        ];
    }

    return null;
}

    private function request($data, $method, $parseAsJSON = false)
    {
        $method = strtoupper($method);
        $query = http_build_query($data);

        $result = ($method === 'GET')
            ? file_get_contents("$this->url?$query")
            : file_get_contents($this->url, false, stream_context_create([
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => $query
                ]
            ]));

        $errorCheck = new ErrorCodes($result);
        if ($errorCheck->checkExist($result)) {
            return $errorCheck->getResponseCode();
        }

        return $parseAsJSON ? json_decode($result, true) : $result;
    }
}
