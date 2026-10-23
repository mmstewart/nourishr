<?php

namespace Tests\Feature;

use Tests\TestCase;

class WhichDatabaseTest extends TestCase
{
    public function test_tests_use_in_memory_sqlite(): void
    {
        $connection = config('database.default');

        fwrite(STDERR, PHP_EOL.'DB: '.$connection.' / '.config("database.connections.$connection.database").PHP_EOL);

        $this->assertSame('sqlite', $connection);
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
    }
}