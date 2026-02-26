<?php

namespace App\Api;

use Bcn\Component\Json\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Потоковая запись JSON для отправки больших JSON-объектов.
 */
class ApiStreamedResponse extends StreamedResponse
{
    /**
     * @var int
     */
    private $responseType = Writer::TYPE_OBJECT;

    /**
     * @param callable $jsonCb function (\Bcn\Component\Json\Writer $writer) {}
     * @param int $status
     * @param array $headers
     */
    public function __construct(callable $jsonCb, int $status = 200, array $headers = [])
    {
        $streamCb = function () use ($jsonCb) {
            // Записываем ответ сначала в темп, а не отправляем сразу, чтоб если на полпути случилась ошибка,
            // то можно было бы ее вывести.
            // The memory limit of php://temp can be controlled by appending /maxmemory:NN,
            // where NN is the maximum amount of data to keep in memory before using a temporary file,
            // in bytes.
            $fp = fopen("php://temp", "wb+");
            if ($fp === false) {
                throw new \Exception("Не удалось открыть поток");
            }
            $jsonWriter = new Writer($fp, JSON_UNESCAPED_UNICODE);
            $jsonWriter->enter('', Writer::TYPE_OBJECT);
            $jsonWriter->enter("response", $this->responseType);
            $jsonCb($jsonWriter);
            $jsonWriter->leave(); // "response"
            $jsonWriter->leave(); // root

            // перемотаем назад
            fseek($fp, 0);
            // отправим в браузер
            $out = fopen('php://output', 'wb');
            stream_copy_to_stream($fp, $out);
            fclose($out);
            fclose($fp);
        };
        parent::__construct($streamCb, $status, $headers);
        $this->headers->set(
            'Content-Type',
            'application/json'
        );
    }

    /**
     * Тип ответа. Одно из Writer :: TYPE_ *
     * По-умолчанию - объект.
     * @param int $responseType
     * @return ApiStreamedResponse
     */
    public function setResponseType(int $responseType): ApiStreamedResponse
    {
        $this->responseType = $responseType;
        return $this;
    }
}
