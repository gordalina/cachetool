<?php

namespace CacheTool\Util;

class FormatterTest extends \PHPUnit\Framework\TestCase
{
    private $tz;
    private $envTz;

    protected function setUp(): void
    {
        if (get_cfg_var('date.timezone')) {
            $this->markTestSkipped('date.timezone is set in php.ini');
        }

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

    public function testDateUsesTzEnv()
    {
        putenv('TZ=America/New_York');
        $this->assertSame('Wed, 02 Oct 2024 08:19:07 -0400', Formatter::date('1727871547', 'U'));
    }

    public function testDateRespectsDefaultTimezone()
    {
        putenv('TZ=America/New_York');
        date_default_timezone_set('Europe/Lisbon');
        $this->assertSame('Wed, 02 Oct 2024 13:19:07 +0100', Formatter::date('1727871547', 'U'));
    }

    public function testInvalidTzEnvFallsBackToUtc()
    {
        putenv('TZ=Not/AZone');
        $this->assertSame('UTC', Formatter::timezone());
    }
}
