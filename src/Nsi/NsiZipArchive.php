<?php

namespace App\Nsi;


/**
 * Архив с xml-файлами справочниками.
 */
class NsiZipArchive
{
    /**
     * @var \ZipArchive
     */
    private $archive;

    /**
     * Исходное имя файла.
     * @var string
     */
    private $archiveName;

    /**
     * NsiArchive constructor.
     * @param string $file
     * @param string $archiveName
     * @throws \Exception
     */
    public function __construct(string $file, string $archiveName)
    {
        $this->archiveName = $archiveName;
        $this->archive = new \ZipArchive();
        if ($this->archive->open($file) !== true) {
            throw new \Exception("Не удалось открыть архив: $file");
        }
    }

    /**
     * @return \Generator|NsiVocabEntry[]
     * @throws \Exception
     */
    public function entries()
    {
        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            $xmlContent = $this->archive->getFromIndex($i);
            if ($xmlContent === false) {
                throw new \Exception($this->archive->getStatusString());
            }
            $xmlObject = simplexml_load_string($xmlContent);
            unset($xmlContent);
            $entryFileName = $this->archive->getNameIndex($i);
            if ($xmlObject === false) {
                throw new \Exception("Не удалось прочитать xml из " . $entryFileName);
            }
            yield new NsiVocabEntry($this->archiveName, $entryFileName, $xmlObject);
            unset($xmlObject);
        }
    }

    /**
     * Закрыть архив.
     */
    public function close()
    {
        $this->archive->close();
    }
}
