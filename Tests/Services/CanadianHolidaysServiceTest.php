<?php
namespace Tests\Services;

use Flagship\Components\Helpers\Services\CanadianHolidaysService;
use PHPUnit\Framework\TestCase;

class CanadianHolidaysServiceTest extends TestCase
{
    public function testEaster()
    {
        $tuesday = CanadianHolidaysService::getBusinessDayFor('2018-03-29', 2, 'QC');
        $this->assertSame($tuesday, '2018-04-03');

        $monday = CanadianHolidaysService::getNextBusinessDayAfter('QC', '2018-03-29');
        $this->assertSame($monday, '2018-04-02');
    }

    public function testJanuary1st()
    {
        $tuesday = CanadianHolidaysService::getBusinessDayFor('2016-12-29', 2, 'QC');
        $this->assertSame($tuesday, '2017-01-03');

        $tuesday = CanadianHolidaysService::getNextBusinessDayAfter('QC', '2016-12-30');
        $this->assertSame($tuesday, '2017-01-03');
    }

    public function testJuly1st()
    {
        $tuesday = CanadianHolidaysService::getBusinessDayFor('2017-06-29', 2, 'QC');
        $this->assertSame($tuesday, '2017-07-04');

        $monday = CanadianHolidaysService::getNextBusinessDayAfter('QC', '2017-06-30');
        $this->assertSame($monday, '2017-07-04');

        $tuesday = CanadianHolidaysService::getBusinessDayFor('2018-06-29', 2, 'QC');
        $this->assertSame($tuesday, '2018-07-03');

        $tueday = CanadianHolidaysService::getNextBusinessDayAfter('QC', '2018-06-29');
        $this->assertSame($tueday, '2018-07-03');
        $tueday = CanadianHolidaysService::getNextBusinessDayAfter('QC', '2018-06-30');
        $this->assertSame($tueday, '2018-07-03');
    }

    public function testRegularDay()
    {
        $day1 = CanadianHolidaysService::getBusinessDayFor('2018-03-13', 3);
        $this->assertSame($day1, '2018-03-16');

        $day2 = CanadianHolidaysService::getNextBusinessDayAfter('', '2018-03-15');
        $this->assertSame($day2, '2018-03-16');
    }

    public function testIsBusinessDay()
    {
        $this->assertFalse(CanadianHolidaysService::isBusinessDay('', '2018-05-21'));
        $this->assertFalse(CanadianHolidaysService::isBusinessDay('', '2018-03-24'));
        $this->assertFalse(CanadianHolidaysService::isBusinessDay('', '2018-03-25'));
        $this->assertFalse(CanadianHolidaysService::isBusinessDay('QC', '2018-05-21'));

        $this->assertTrue(CanadianHolidaysService::isBusinessDay('', '2018-05-14'));
        $this->assertTrue(CanadianHolidaysService::isBusinessDay('NB', '2018-05-21'));
    }
}
