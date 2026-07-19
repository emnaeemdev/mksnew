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

    #[Test]
    public function multi_word_snippet_prefers_phrase_over_single_token(): void
    {
        $document = (object) [
            'content' => 'الحكم صراحة واستقلالا عن كل ركن من أركان حرية التقليد غير لازم. '
                . 'الإحالة و المحكمة المذكورة قضت قانون حرية الصحافة رقم 96 لسنة 1996.',
            'excerpt' => '',
            'title' => 'الطعن رقم 11843',
            'search_text' => '',
        ];

        $snippets = $this->search->findDocumentSearchSnippets(
            $document,
            'حرية الصحافة',
            'exact',
            ['حريه', 'صحافه']
        );

        $this->assertCount(1, $snippets);
        $plain = $this->search->snippetToPlainText($snippets[0]);
        $this->assertStringContainsString('حرية', $plain);
        $this->assertStringContainsString('الصحافة', $plain);
        $this->assertStringNotContainsString('التقليد', $plain);
    }

    #[Test]
    public function non_adjacent_query_words_each_get_a_snippet(): void
    {
        $document = (object) [
            'content' => 'وردت حرية الرأي في مقدمة النص ثم بعد ذلك ذكرت الصحافة في موضع لاحق قريب.',
            'excerpt' => '',
            'title' => 'مثال',
            'search_text' => '',
        ];

        $snippets = $this->search->findDocumentSearchSnippets(
            $document,
            'حرية الصحافة',
            'exact',
            ['حريه', 'صحافه']
        );

        $this->assertGreaterThanOrEqual(2, count($snippets));
        $combined = implode(' || ', array_map(fn ($sn) => $this->search->snippetToPlainText($sn), $snippets));
        $this->assertStringContainsString('حرية', $combined);
        $this->assertStringContainsString('الصحافة', $combined);
    }

    #[Test]
    public function multiple_query_phrases_each_appear_as_snippet(): void
    {
        $document = (object) [
            'content' => '. تقادم . الاعتداء على الحرية الشخصية أو حرمة الحياة الخاصة وغيرها من الحقوق و الحريات العامة. '
                . 'حو ما نصت عليه المادة ٢٦٥ من قانون الإجراءات الجنائية ، وإن كان يتأدى منه بالضرورة.',
            'excerpt' => '',
            'title' => 'الطعن رقم 2257 لسنة 56',
            'search_text' => '',
        ];

        $query = 'حرمة الحياة الخاصة قانون الإجراءات الجنائية';
        $snippets = $this->search->findDocumentSearchSnippets($document, $query, 'exact');

        $this->assertGreaterThanOrEqual(2, count($snippets));
        $combined = implode(' || ', array_map(fn ($sn) => $this->search->snippetToPlainText($sn), $snippets));
        $this->assertStringContainsString('حرمة', $combined);
        $this->assertStringContainsString('الخاصة', $combined);
        $this->assertStringContainsString('قانون', $combined);
        $this->assertStringContainsString('الجنائية', $combined);

        $highlight = $this->search->snippetFocusTokens($query);
        $htmlLines = array_map(
            fn ($sn) => $this->search->renderSnippetHtml($sn, $highlight),
            $snippets
        );
        $html = implode("\n", $htmlLines);
        $this->assertStringContainsString('<mark>', $html);
        $this->assertTrue(
            (bool) preg_match('/حرمة|الحياة|الخاصة/u', $html)
            && (bool) preg_match('/قانون|الإجراءات|الجنائية/u', $html),
            'Expected both query phrases visible: ' . $html
        );
    }

    #[Test]
    public function repeated_word_does_not_create_duplicate_snippets(): void
    {
        $document = (object) [
            'content' => 'حرية التعبير مهمة. وأيضًا حرية الرأي مكفولة. وكذلك حرية الصحافة.',
            'excerpt' => '',
            'title' => 'مثال تكرار',
            'search_text' => '',
        ];

        $snippets = $this->search->findDocumentSearchSnippets(
            $document,
            'حرية',
            'exact',
            ['حريه']
        );

        $this->assertCount(1, $snippets);
    }

    #[Test]
    public function render_snippet_highlights_all_query_tokens(): void
    {
        $html = $this->search->renderSnippetHtml(
            [
                'before' => 'قانون',
                'match' => 'حرية الصحافة',
                'after' => 'رقم 96',
            ],
            ['حريه', 'صحافه']
        );

        $this->assertStringContainsString('<mark>', $html);
        // إما تظليل الجملة كاملة أو كل كلمة على حدة
        $this->assertTrue(
            (bool) preg_match('/<mark>[^<]*حري[هة][^<]*الصحاف[هة][^<]*<\/mark>/u', $html)
            || (
                preg_match('/<mark>[^<]*حري[هة][^<]*<\/mark>/u', $html)
                && preg_match('/<mark>[^<]*الصحاف[هة][^<]*<\/mark>/u', $html)
            ),
            'Expected both query words highlighted: ' . $html
        );
    }

    #[Test]
    public function snippets_do_not_use_search_text_metadata(): void
    {
        $document = (object) [
            'content' => 'نص الحكم يتحدث عن التقادم فقط دون ذكر الكلمة المطلوبة.',
            'excerpt' => '',
            'title' => 'الطعن رقم 62 لسنة 58',
            'search_text' => 'لما تقدم 2146 1990-11-13 58 حقوق اقتصاديه الصحافه',
            'search_words' => ' لما تقدم الصحافه ',
        ];

        $snippets = $this->search->findDocumentSearchSnippets(
            $document,
            'صحافة',
            'any',
            ['صحافه']
        );

        $this->assertSame([], $snippets);
    }

    #[Test]
    public function classification_select_fields_are_not_indexed_in_content_search(): void
    {
        $select = (object) ['field' => (object) ['type' => 'select'], 'value' => 'الصحافة'];
        $multi = (object) ['field' => (object) ['type' => 'multiselect'], 'value' => 'الصحافة'];
        $text = (object) ['field' => (object) ['type' => 'text'], 'value' => '2146'];
        $number = (object) ['field' => (object) ['type' => 'number'], 'value' => '58'];
        $unknown = (object) ['field' => null, 'value' => 'الصحافة'];

        $this->assertFalse($this->search->shouldIndexFieldValueInContentSearch($select));
        $this->assertFalse($this->search->shouldIndexFieldValueInContentSearch($multi));
        $this->assertFalse($this->search->shouldIndexFieldValueInContentSearch($unknown));
        $this->assertTrue($this->search->shouldIndexFieldValueInContentSearch($text));
        $this->assertTrue($this->search->shouldIndexFieldValueInContentSearch($number));
    }
}
