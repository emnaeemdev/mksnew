<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    protected HtmlSanitizer $sanitizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sanitizer = new HtmlSanitizer();
    }

    #[Test]
    public function it_keeps_whitelisted_youtube_iframe(): void
    {
        $html = '<p>شاهد</p><iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" width="560" height="315"></iframe>';
        $out = $this->sanitizer->clean($html);

        $this->assertStringContainsString('<iframe', $out);
        $this->assertStringContainsString('youtube.com/embed/dQw4w9WgXcQ', $out);
        $this->assertStringContainsString('loading="lazy"', $out);
    }

    #[Test]
    public function it_keeps_google_drive_iframe(): void
    {
        $html = '<iframe src="https://drive.google.com/file/d/abc/preview"></iframe>';
        $out = $this->sanitizer->clean($html);

        $this->assertStringContainsString('drive.google.com', $out);
    }

    #[Test]
    public function it_strips_untrusted_iframe_hosts(): void
    {
        $html = '<p>x</p><iframe src="https://evil.example.com/embed"></iframe><p>y</p>';
        $out = $this->sanitizer->clean($html);

        $this->assertStringNotContainsString('<iframe', $out);
        $this->assertStringNotContainsString('evil.example.com', $out);
        $this->assertStringContainsString('<p>x</p>', $out);
        $this->assertStringContainsString('<p>y</p>', $out);
    }

    #[Test]
    public function it_rejects_http_embed_src(): void
    {
        $this->assertFalse($this->sanitizer->isAllowedEmbedSrc('http://www.youtube.com/embed/abc'));
        $this->assertTrue($this->sanitizer->isAllowedEmbedSrc('https://www.youtube.com/embed/abc'));
    }
}
