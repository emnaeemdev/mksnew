<?php

namespace Tests\Unit;

use App\Services\DocumentSearchService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DocumentSearchServiceTest extends TestCase
{
    protected DocumentSearchService $search;

    protected function setUp(): void
    {
        parent::setUp();
        $this->search = new DocumentSearchService();
    }

    #[Test]
    public function it_splits_attached_waw_before_al(): void
    {
        $this->assertSame(
            ['و', 'التعبير'],
            $this->search->splitAttachedConjunctionWaw(['والتعبير'])
        );
    }

    #[Test]
    public function it_does_not_split_words_that_naturally_start_with_waw(): void
    {
        $this->assertSame(
            ['وثيقه', 'وجود'],
            $this->search->splitAttachedConjunctionWaw(['وثيقه', 'وجود'])
        );
    }

    #[Test]
    public function attached_waw_query_matches_spaced_waw_query(): void
    {
        $attached = $this->search->parseSearchQuery('حرية والتعبير');
        $spaced = $this->search->parseSearchQuery('حرية و التعبير');

        $this->assertSame(['حريه', 'التعبير'], $attached['tokensPerWord']);
        $this->assertSame(['حريه', 'التعبير'], $spaced['tokensPerWord']);
        $this->assertSame($spaced['tokensForAll'], $attached['tokensForAll']);
        $this->assertNotContains('و', $attached['tokensForAll']);
    }

    #[Test]
    public function tokens_for_all_exclude_stop_words(): void
    {
        $parsed = $this->search->parseSearchQuery('حرية و التعبير');

        $this->assertSame(['حريه', 'التعبير'], $parsed['tokensForAll']);
    }

    #[Test]
    public function finalize_scattered_plan_groups_two_word_cooccurrence_first(): void
    {
        $method = new \ReflectionMethod(DocumentSearchService::class, 'finalizeScatteredPlan');
        $method->setAccessible(true);

        $tokens = ['حريه', 'التعبير'];
        $setBuckets = [
            '0' => [10, 11, 12],
            '1' => [20],
            '0-1' => [1, 2],
        ];
        $wordDocIds = [
            0 => [10, 11, 12, 1, 2],
            1 => [20, 1, 2],
        ];

        $plan = $method->invoke($this->search, $tokens, $setBuckets, $wordDocIds);

        $this->assertSame('grouped', $plan['mode']);
        $this->assertSame('group-0-1', $plan['tabs'][0]['key']);
        $this->assertSame([1, 2], $plan['tabs'][0]['doc_ids']);
        $this->assertGreaterThanOrEqual(2, count($plan['tabs']));
    }

    #[Test]
    public function set_buckets_from_word_doc_ids_detects_intersection(): void
    {
        $method = new \ReflectionMethod(DocumentSearchService::class, 'setBucketsFromWordDocIds');
        $method->setAccessible(true);

        $buckets = $method->invoke($this->search, [
            0 => [10, 1, 2],
            1 => [20, 1, 2],
        ]);

        $this->assertSame([1, 2], $buckets['0-1']);
        $this->assertSame([10], $buckets['0']);
        $this->assertSame([20], $buckets['1']);
    }

    #[Test]
    public function preview_does_not_split_hurriya_out_of_tahriya(): void
    {
        $text = 'جد فى تحريه ولا ينال من ذلك';
        $out = $this->search->plainTextForPreview($text, 500, ['حرية', 'حريه']);

        $this->assertStringContainsString('تحريه', $out);
        $this->assertStringNotContainsString('ت حريه', $out);
    }

    #[Test]
    public function preview_splits_legitimate_prefixes_ka_and_al(): void
    {
        $ka = $this->search->plainTextForPreview('كصحفيين في الموضوع', 500, ['صحفيين']);
        $this->assertMatchesRegularExpression('/ك\s+صحفيين/u', $ka);

        $highlighted = $this->search->highlightSearchTokensInText(
            $this->search->plainTextForPreview('كصحفيين حضروا', 500, ['صحفيين']),
            ['صحفيين'],
            true
        );
        $this->assertStringContainsString('<mark>', $highlighted);

        $al = $this->search->highlightSearchTokensInText('حماية الحرية الشخصية', ['حرية'], true);
        $this->assertStringContainsString('<mark>', $al);
    }

    #[Test]
    public function highlight_matches_al_hurriya_but_not_inside_tahriya(): void
    {
        $ok = $this->search->highlightSearchTokensInText('حماية الحرية الشخصية', ['حرية'], true);
        $this->assertStringContainsString('<mark>', $ok);

        $bad = $this->search->highlightSearchTokensInText('جد فى تحريه ولا ينال', ['حرية'], true);
        $this->assertStringNotContainsString('<mark>', $bad);
    }

    #[Test]
    public function preview_window_includes_match_for_highlighting(): void
    {
        $long = str_repeat('نص تمهيدي للجلسة والوقائع. ', 40)
            . 'تدخلت نقابة الصحفيين خصما في الدعوى '
            . str_repeat('وتستمر الأسباب بعد ذلك. ', 20);

        $preview = $this->search->plainTextForPreview($long, 180, ['صحفيين']);
        $this->assertStringContainsString('صحفيين', $preview);

        $marked = $this->search->highlightSearchTokensInText($preview, ['صحفيين'], true);
        $this->assertStringContainsString('<mark>', $marked);
    }
}
