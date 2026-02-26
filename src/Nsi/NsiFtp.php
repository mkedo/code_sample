<?php

namespace App\Nsi;

use App\DbConfig\DeferredParam;
use FtpClient\FtpClient;
use Psr\Log\LoggerInterface;

/**
 * Скачивание архивов справочников.
 */
class NsiFtp
{
    /**
     * @var DeferredParam
     */
    private $credentials;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * NsiFtp constructor.
     * @param DeferredParam $credentials
     * @param LoggerInterface $logger
     */
    public function __construct(DeferredParam $credentials, LoggerInterface $logger)
    {
        $this->credentials = $credentials;
        $this->logger = $logger;
    }

    /**
     * Скачать весь справочник в виде zip-архива.
     * @param string $nsiType тип справочника: okei, okved2 и т.п.
     * @param resource $toFilePointer указатель на пустой файл куда будет загружен ахрив
     * @return string Исходное название выгруженного архива
     * @throws \FtpClient\FtpException
     */
    public function downloadFull(string $nsiType, $toFilePointer): string
    {
        $credentials = (array)$this->credentials->get();
        $host = $credentials['host'];
        $username = $credentials['username'];
        $password = $credentials['password'];

        $this->logger->info(
            "Подключаемся к ftp {host} пользователем {username}",
            [
                'host' => $host,
                'username' => $username,
            ]
        );
        $ftp = new FtpClient();
        $ftp->connect($host);
        try {
            $ftp->login($username, $password);
            $ftp->pasv(true);

            // смотрим доступные справочные файлы
            $nsiFullName = 'nsi' . ucfirst($nsiType);
            $nsiDirPath = sprintf("/out/nsi/%s/", $nsiFullName);
            $ftp->chdir($nsiDirPath);
            // выбираем только файлы
            $files = array_filter($ftp->scanDir(), function ($file) use ($nsiFullName) {
                return $file['type'] === 'file' // это файл
                    && strpos($file['name'], $nsiFullName . "_all_") === 0; // и похож по названию
            });
            if (empty($files)) {
                throw new \Exception("Файл с классификатором не найден");
            }
            // оставляем только имена
            $files = array_map(function ($file) {
                return $file['name'];
            }, $files);

            // берем самый свежий
            rsort($files);
            $remoteFile = $files[0];

            // скачиваем
            $this->logger->info("Скачиваем справочник {filename} с ftp", ['filename' => $remoteFile]);
            $ftp->fget($toFilePointer, $remoteFile, FTP_BINARY);
            return $remoteFile;
        } finally {
            $ftp->close();
        }
    }
}
