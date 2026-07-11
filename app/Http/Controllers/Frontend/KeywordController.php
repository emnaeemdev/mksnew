<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Keyword;
use App\Models\Post;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function postsIndex(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $keywords = Keyword::query()
            ->where('scope', 'post')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%' . $q . '%'))
            ->where('usage_count', '>', 0)
            ->orderByDesc('usage_count')
            ->orderBy('name')
            ->paginate(48)
            ->withQueryString();

        return view('frontend.keywords.posts-index', compact('keywords', 'q'));
    }

    public function postsShow(string $locale, string $keyword)
    {
        $keywordModel = Keyword::query()
            ->where('scope', 'post')
            ->where('slug', $keyword)
            ->firstOrFail();

        $postsQuery = $keywordModel->posts()
            ->published()
            ->with('category');

        if (app()->getLocale() === 'en') {
            $postsQuery->whereNotNull('title_en')->where('title_en', '!=', '')
                ->whereNotNull('content_en')->where('content_en', '!=', '');
        }

        $posts = $postsQuery
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('frontend.keywords.posts-show', [
            'keyword' => $keywordModel,
            'posts' => $posts,
        ]);
    }

    public function documentsIndex(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $keywords = Keyword::query()
            ->where('scope', 'document')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', '%' . $q . '%'))
            ->where('usage_count', '>', 0)
            ->orderByDesc('usage_count')
            ->orderBy('name')
            ->paginate(48)
            ->withQueryString();

        return view('frontend.keywords.documents-index', compact('keywords', 'q'));
    }

    public function documentsShow(string $locale, string $keyword)
    {
        $keywordModel = Keyword::query()
            ->where('scope', 'document')
            ->where('slug', $keyword)
            ->firstOrFail();

        $documents = $keywordModel->documents()
            ->published()
            ->with('section')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('frontend.keywords.documents-show', [
            'keyword' => $keywordModel,
            'documents' => $documents,
        ]);
    }
}
