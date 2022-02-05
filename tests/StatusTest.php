<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{

    public function testIsWorking(): void
    {
        $this->assertEquals(
            'user@example.com',
            'user@example.com'
        );
    }

}
