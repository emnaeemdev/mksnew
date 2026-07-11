<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Keyword extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'scope',
        'usage_count',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    public static function scopes(): array
    {
        return ['document', 'post', 'nashra', 'podcast'];
    }

    public static function normalizeName(string $name): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        return $name;
    }

    public static function makeSlug(string $name): string
    {
        $slug = Str::slug($name, '-', null);
        if ($slug === '') {
            $slug = 'kw-' . substr(md5($name), 0, 10);
        }

        return $slug;
    }

    public static function findOrCreateForScope(string $scope, string $name): ?self
    {
        $name = self::normalizeName($name);
        if ($name === '' || !in_array($scope, self::scopes(), true)) {
            return null;
        }

        $slug = self::makeSlug($name);

        return self::firstOrCreate(
            ['scope' => $scope, 'slug' => $slug],
            ['name' => $name, 'usage_count' => 0]
        );
    }

    public function documents(): MorphToMany
    {
        return $this->morphedByMany(Document::class, 'keywordable');
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'keywordable');
    }

    public function nashras(): MorphToMany
    {
        return $this->morphedByMany(Nashra::class, 'keywordable');
    }

    public function podcasts(): MorphToMany
    {
        return $this->morphedByMany(Podcast::class, 'keywordable');
    }
}
