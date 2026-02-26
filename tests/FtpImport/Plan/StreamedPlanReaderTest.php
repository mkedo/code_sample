<?php

namespace App\Tests\FtpImport\Plan;

use App\FtpImport\Plan\StreamedPlanReader;
use App\Oos\XmlSerializer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class StreamedPlanReaderTest extends WebTestCase
{
    private static function getKernel(): KernelInterface
    {
        if (!static::$kernel) {
            static::bootKernel();
        }
        return static::$kernel;
    }

    public function testPurchasePlanData()
    {
        /**
         * @var $serializer XmlSerializer
         */
        $serializer = self::getKernel()->getContainer()->get(XmlSerializer::class);
        $reader = new StreamedPlanReader($serializer, __DIR__ . "/__REDACTED__.xml");
        $this->assertEquals(StreamedPlanReader::TYPE_PLAN, $reader->getPlanType());
        $planData = $reader->getPurchasePlanData();
        $this->assertEquals(
            "__REDACTED__",
            $planData->getGuid(),
            "План должен загрузится"
        );
        $this->assertNotNull($planData->getCustomer(), "Вложенные элементы должны загрузится");
        $this->assertEquals(
            "__REDACTED__",
            $planData->getCustomer()->getMainInfo()->getShortName()
        );
        $this->assertNotEmpty($planData->getApproveDate(), "Дата должна распарситься");
        $this->assertEquals(
            "2021.09.23",
            $planData->getApproveDate()->format('Y.m.d'),
            "Дата должна распарситься правильно"
        );
        $this->assertEmpty($planData->getPurchasePlanItems(), "Позиции не должны загрузится");
        $this->assertEmpty($planData->getPurchasePlanItemsSMB(), "Позиции __REDACTED__ не должны загрузится");
        $this->assertEmpty($planData->getInnovationPlanItems(), "Инновационные позиции не должны загрузится");
        $this->assertEmpty($planData->getInnovationPlanItemsSMB(), "Инновационные позиции __REDACTED__ не должны загрузится");
    }

    public function testParsPositions()
    {
        /**
         * @var $serializer XmlSerializer
         */
        $serializer = self::getKernel()->getContainer()->get(XmlSerializer::class);
        $reader = new StreamedPlanReader($serializer, __DIR__ . "/__REDACTED__.xml");
        $this->assertEquals(StreamedPlanReader::TYPE_PLAN, $reader->getPlanType());
        $posCnt = 0;
        $guids = [
            '__REDACTED__',
            '__REDACTED__',
            '__REDACTED__',
        ];
        foreach ($reader->getPositions() as [$position, $type]) {
            $posCnt++;
            $this->assertEquals($guids[$posCnt - 1], $position->getGuid(), "Позиция должна распарситься");
        }
        $this->assertEquals(3, $posCnt, "Не хватает позиций xml");
    }

    public function testPurchasePlan()
    {
        /**
         * @var $serializer XmlSerializer
         */
        $serializer = self::getKernel()->getContainer()->get(XmlSerializer::class);
        $reader = new StreamedPlanReader($serializer, __DIR__ . "/__REDACTED__.xml");
        $this->assertEquals(StreamedPlanReader::TYPE_PROJECT_PLAN, $reader->getPlanType());
        $planData = $reader->getPurchasePlanData();
        $this->assertEquals(
            "__REDACTED__",
            $planData->getGuid(),
            "План должен загрузится"
        );
        $this->assertNotNull($planData->getCustomer(), "Вложенные элементы должны загрузится");
        $this->assertEquals(
            "__REDACTED__",
            $planData->getCustomer()->getMainInfo()->getShortName(),
            "Имя должно распарситься"
        );
        $this->assertEquals(
            "0",
            $planData->getAnnualVolumeHiTechSMBPercent(),
            "Последний элемент должен быть считан правильно"
        );
    }

    public function testProjectPositions()
    {
        /**
         * @var $serializer XmlSerializer
         */
        $serializer = self::getKernel()->getContainer()->get(XmlSerializer::class);
        $reader = new StreamedPlanReader($serializer, __DIR__ . "/__REDACTED__.xml");
        $this->assertEquals(StreamedPlanReader::TYPE_PROJECT_PLAN, $reader->getPlanType());
        $posCnt = 0;
        $guids = [
            '__REDACTED__',
            // ...
        ];
        foreach ($reader->getPositions() as [$position, $type]) {
            $posCnt++;
            $this->assertEquals($guids[$posCnt - 1], $position->getGuid(), "Позиция должна распарситься");
        }
        $this->assertEquals(count($guids), $posCnt, "Не хватает позиций xml");
    }
}
