<?php

namespace OpenAdminCore\Admin\Tests\Unit;

use OpenAdminCore\Admin\Tests\TestCase;

class ExampleTest extends TestCase
{
    /** @test */
    public function it_has_config_file()
    {
        $config = config('admin');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('name', $config);
        $this->assertArrayHasKey('route', $config);
    }
}