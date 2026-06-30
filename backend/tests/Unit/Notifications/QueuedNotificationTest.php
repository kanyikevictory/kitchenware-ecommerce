<?php

namespace Tests\Unit\Notifications;

use App\Models\Order;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Notifications\Customer\OrderConfirmationNotification;
use App\Notifications\Customer\WelcomeNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueuedNotificationTest extends TestCase
{
    public static function notifications(): array
    {
        return [
            [new WelcomeNotification],
            [new VerifyEmailNotification],
            [new ResetPasswordNotification('reset-token')],
            [new OrderConfirmationNotification(new Order)],
        ];
    }

    #[DataProvider('notifications')]
    public function test_notification_is_queued_after_commit(object $notification): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $notification);
        $this->assertSame('notifications', $notification->queue);
        $this->assertTrue($notification->afterCommit);
        $this->assertSame(3, $notification->tries);
    }
}
