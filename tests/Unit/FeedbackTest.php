<?php

namespace Tests\Unit;

use App\Support\Feedback;
use PHPUnit\Framework\TestCase;

class FeedbackTest extends TestCase
{
    public function test_business_messages_are_preserved(): void
    {
        $this->assertSame('Department masih memiliki anggota.', Feedback::message('Department masih memiliki anggota.'));
    }

    public function test_infrastructure_details_and_secrets_are_not_disclosed(): void
    {
        foreach (['SQLSTATE[23000]: duplicate', 'Stack trace: vendor/file.php', '<script>alert(1)</script>', 'token=secret', null] as $message) {
            $this->assertSame('Aman', Feedback::message($message, 'Aman'));
        }
    }
}
