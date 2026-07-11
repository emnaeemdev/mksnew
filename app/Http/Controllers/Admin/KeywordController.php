<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keyword;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function suggest(Request $request)
    {
        $scope = (string) $request->query('scope', '');
        $q = trim((string) $request->query('q', ''));
        $limit = min(20, max(5, (int) $request->query('limit', 12)));

        if (!in_array($scope, Keyword::scopes(), true)) {
            return response()->json(['data' => []]);
        }

        $query = Keyword::query()
            ->where('scope', $scope)
            ->orderByDesc('usage_count')
            ->orderBy('name');

        if ($q !== '') {
            $query->where('name', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%');
        }

        $items = $query->limit($limit)->get(['id', 'name', 'slug', 'usage_count']);

        return response()->json([
            'data' => $items->map(fn (Keyword $k) => [
                'id' => $k->id,
                'name' => $k->name,
                'slug' => $k->slug,
                'usage_count' => $k->usage_count,
            ])->values(),
        ]);
    }
}
