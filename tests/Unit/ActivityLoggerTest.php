<?php

namespace Tests\Unit;

use App\Services\ActivityLogger;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityLoggerTest extends TestCase
{
    #[Test]
    public function it_describes_document_create_and_bulk_publish(): void
    {
        $logger = new ActivityLogger();

        $created = $logger->describe('admin.documents.store', 'POST', ['title' => 'حكم مهم'], 'حكم مهم');
        $this->assertSame('created', $created['action']);
        $this->assertStringContainsString('أضاف وثيقة', $created['description']);
        $this->assertStringContainsString('حكم مهم', $created['description']);

        $published = $logger->describe('admin.documents.bulk-publish', 'POST', [
            'document_ids' => [1, 2, 3],
        ]);
        $this->assertSame('published', $published['action']);
        $this->assertStringContainsString('3', $published['description']);
        $this->assertSame([1, 2, 3], $published['ids']);
    }

    #[Test]
    public function it_describes_delete_and_settings_without_storing_passwords_in_wording(): void
    {
        $logger = new ActivityLogger();

        $deleted = $logger->describe('admin.posts.destroy', 'DELETE', [], 'خبر صحفي');
        $this->assertSame('deleted', $deleted['action']);
        $this->assertStringContainsString('موضوع', $deleted['description']);

        $settings = $logger->describe('admin.settings.update', 'PUT');
        $this->assertSame('updated', $settings['action']);
        $this->assertStringContainsString('إعدادات', $settings['description']);

        $cleared = $logger->describe('admin.users.activity-logs.clear', 'DELETE', [], 'Emad');
        $this->assertSame('deleted', $cleared['action']);
        $this->assertStringContainsString('مسح سجل النشاط', $cleared['description']);
        $this->assertStringContainsString('Emad', $cleared['description']);
    }
};
