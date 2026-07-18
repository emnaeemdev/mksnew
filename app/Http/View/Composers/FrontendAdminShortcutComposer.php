<?php

namespace App\Http\View\Composers;

use App\Models\Category;
use App\Models\Document;
use App\Models\Nashra;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FrontendAdminShortcutComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with('frontendAdminShortcut', null);
            return;
        }

        $view->with('frontendAdminShortcut', $this->resolve());
    }

    /**
     * @return array{url: string, label: string, icon: string, mode: string}|null
     */
    private function resolve(): array
    {
        $locale = app()->getLocale();
        $route = request()->route();

        if (!$route || !$route->getName()) {
            return $this->dashboardLink($locale);
        }

        $editLink = match ($route->getName()) {
            'content.show' => $this->resolveContentShow($route, $locale),
            'frontend.documents.show' => $this->editLink($locale, 'admin.documents.edit', $this->resolveModel($route, 'document', Document::class)),
            'frontend.nashras.show' => $this->editLink($locale, 'admin.nashras.edit', $this->resolveModel($route, 'nashra', Nashra::class)),
            'frontend.podcasts.show' => $this->editLink($locale, 'admin.podcasts.edit', $this->resolveModel($route, 'podcast', Podcast::class)),
            default => null,
        };

        return $editLink ?? $this->dashboardLink($locale);
    }

    /**
     * @return array{url: string, label: string, icon: string, mode: string}|null
     */
    private function resolveContentShow($route, string $locale): ?array
    {
        $id = (int) $route->parameter('id');
        $sectionSlug = (string) $route->parameter('sectionSlug');

        if ($id < 1 || $sectionSlug === '') {
            return null;
        }

        $category = Category::query()
            ->where(function ($q) use ($sectionSlug) {
                $q->where('name_en', $sectionSlug)
                    ->orWhere('slug', $sectionSlug);
            })
            ->first();

        if ($category) {
            $post = Post::query()->find($id);
            if ($post) {
                return $this->editLink($locale, 'admin.posts.edit', $post);
            }
        }

        $document = Document::query()->find($id);
        if ($document) {
            return $this->editLink($locale, 'admin.documents.edit', $document);
        }

        return null;
    }

    /**
     * @template T of Model
     * @param class-string<T> $class
     * @return T|null
     */
    private function resolveModel($route, string $param, string $class): ?Model
    {
        $value = $route->parameter($param);

        if ($value instanceof $class) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return $class::query()->find((int) $value);
        }

        $instance = new $class();
        $key = method_exists($instance, 'getRouteKeyName')
            ? $instance->getRouteKeyName()
            : 'id';

        return $class::query()->where($key, $value)->first();
    }

    /**
     * @return array{url: string, label: string, icon: string, mode: string}|null
     */
    private function editLink(string $locale, string $routeName, ?Model $model): ?array
    {
        if (!$model) {
            return null;
        }

        // روابط الأدمن تستخدم المعرّف دائماً (الوثائق تستخدم slug في الواجهة الأمامية فقط)
        return [
            'url' => route($routeName, $model->getKey()),
            'label' => $locale === 'ar' ? 'تعديل' : 'Edit',
            'icon' => 'fa-edit',
            'mode' => 'edit',
        ];
    }

    /**
     * @return array{url: string, label: string, icon: string, mode: string}
     */
    private function dashboardLink(string $locale): array
    {
        return [
            'url' => route('admin.dashboard'),
            'label' => $locale === 'ar' ? 'لوحة التحكم' : 'Admin',
            'icon' => 'fa-cog',
            'mode' => 'dashboard',
        ];
    }
}
