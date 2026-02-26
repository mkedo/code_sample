<?php

namespace App\Nsi;

/**
 * Представляет один файл внутри архива справочника.
 */
class NsiVocabEntry
{
    /**
     * Имя архива.
     * @var string
     */
    private $archiveName;

    /**
     * Имя файла в архиве.
     * @var string
     */
    private $entryName;

    /**
     * Содержимое файла.
     * @var \SimpleXMLElement
     */
    private $xmlObject;

    /**
     * @param string $archiveName
     * @param string $entryName
     * @param \SimpleXMLElement $xmlObject
     */
    public function __construct(string $archiveName, string $entryName, \SimpleXMLElement $xmlObject)
    {
        $this->archiveName = $archiveName;
        $this->entryName = $entryName;
        $this->xmlObject = $xmlObject;
    }

    /**
     * @return string
     */
    public function getArchiveName(): string
    {
        return $this->archiveName;
    }

    /**
     * @return string
     */
    public function getEntryName(): string
    {
        return $this->entryName;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getXmlObject(): \SimpleXMLElement
    {
        return $this->xmlObject;
    }
}
