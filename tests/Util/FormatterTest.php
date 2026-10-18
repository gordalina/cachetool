<?php

namespace CacheTool\Util;

class FormatterTest extends \PHPUnit\Framework\TestCase
{
    private $tz;
    private $envTz;

    protected function setUp(): void
    {
        $this->tz = date_default_timezone_get();
        $this->envTz = getenv('TZ');
        date_default_timezone_set('UTC');
    }

    protected function tearDown(): void
    {
        if ($this->tz !== null) {
            date_default_timezone_set($this->tz);
            putenv($this->envTz === false ? 'TZ' : "TZ={$this->envTz}");
        }
    }

    public function testSystemTimezoneUsesTzEnv()
    {
        putenv('TZ=America/New_York');
        $this->assertSame('America/New_York', Formatter::systemTimezone());
    }

    public function testSystemTimezoneUsesZoneinfoPath()
    {
        putenv('TZ=:/usr/share/zoneinfo/Europe/Lisbon');
        $this->assertSame('Europe/Lisbon', Formatter::systemTimezone());
    }

    public function testDateRespectsDefaultTimezone()
    {
        putenv('TZ=America/New_York');
        date_default_timezone_set('Europe/Lisbon');
        $this->assertSame('Wed, 02 Oct 2024 13:19:07 +0100', Formatter::date('1727871547', 'U'));
    }

    public function testInvalidTzEnvFallsBackToDefault()
    {
        putenv('TZ=Not/AZone');
        $this->assertSame('UTC', Formatter::systemTimezone());
    }

    public function testDateWithoutFormat()
    {
        putenv('TZ=UTC');
        $this->assertSame('Wed, 02 Oct 2024 12:19:07 +0000', Formatter::date('2024-10-02 12:19:07'));
    }
}
