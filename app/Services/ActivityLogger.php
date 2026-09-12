<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Document;
use App\Models\DocumentCustomField;
use App\Models\DocumentSection;
use App\Models\Inquiry;
use App\Models\Media;
use App\Models\Nashra;
use App\Models\NewsletterSubscription;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ActivityLogger
{
    /**
     * @param array<string, mixed> $context
     */
    public function record(?User $user, string $action, string $description, array $context = []): void
    {
        try {
            ActivityLog::query()->create([
                'user_id' => $user?->id ?? ($context['user_id'] ?? null),
                'action' => Str::limit($action, 40, ''),
                'description' => Str::limit($description, 500, ''),
                'subject_type' => $context['subject_type'] ?? null,
                'subject_id' => $context['subject_id'] ?? null,
                'route_name' => isset($context['route_name']) ? Str::limit((string) $context['route_name'], 255, '') : null,
                'method' => isset($context['method']) ? Str::limit((string) $context['method'], 10, '') : null,
                'url' => isset($context['url']) ? Str::limit((string) $context['url'], 500, '') : null,
                'ip_address' => isset($context['ip_address']) ? Str::limit((string) $context['ip_address'], 45, '') : null,
                'user_agent' => isset($context['user_agent']) ? Str::limit((string) $context['user_agent'], 255, '') : null,
                'properties' => $context['properties'] ?? null,
                'status_code' => $context['status_code'] ?? null,
            ]);
        } catch (Throwable $e) {
            Log::warning('activity_log_failed', ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    public function recordRequest(Request $request, int $statusCode, array $snapshot = []): void
    {
        $user = $request->user();
        if (!$user instanceof User) {
            return;
        }

        $routeName = (string) optional($request->route())->getName();
        $described = $this->describe($routeName, $request->method(), $request->all(), $snapshot['subject_label'] ?? null);

        $properties = array_filter([
            'subject_label' => $snapshot['subject_label'] ?? null,
            'actor_name' => $user->name,
            'summary' => $this->summarizeInput($request),
            'ids' => $described['ids'] ?? null,
            'count' => $described['count'] ?? null,
        ], fn ($value) => $value !== null && $value !== [] && $value !== '');

        $this->record($user, $described['action'], $described['description'], [
            'subject_type' => $snapshot['subject_type'] ?? null,
            'subject_id' => $snapshot['subject_id'] ?? null,
            'route_name' => $routeName !== '' ? $routeName : null,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'status_code' => $statusCode,
            'properties' => $properties === [] ? null : $properties,
        ]);
    }

    /**
     * @return array{subject_type?:string,subject_id?:int,subject_label?:string}
     */
    public function snapshotSubject(Request $request): array
    {
        $route = $request->route();
        if ($route) {
            foreach ($route->parameters() as $value) {
                if (!$value instanceof Model) {
                    continue;
                }

                return array_filter([
                    'subject_type' => $value::class,
                    'subject_id' => (int) $value->getKey(),
                    'subject_label' => $this->labelFor($value),
                ], fn ($item) => $item !== null && $item !== '');
            }
        }

        $label = $this->firstString($request->only(['title', 'title_ar', 'title_en', 'name', 'name_ar', 'subject', 'email']));
        if ($label === null) {
            return [];
        }

        return ['subject_label' => Str::limit($label, 180, '')];
    }

    public function subjectUrl(?string $type, ?int $id): ?string
    {
        if ($type === null || $id === null || $id < 1) {
            return null;
        }

        $route = match ($type) {
            Document::class => 'admin.documents.edit',
            Post::class => 'admin.posts.edit',
            Category::class => 'admin.categories.edit',
            User::class => 'admin.users.show',
            DocumentSection::class => 'admin.document-sections.edit',
            DocumentCustomField::class => 'admin.document-custom-fields.edit',
            Nashra::class => 'admin.nashras.edit',
            Podcast::class => 'admin.podcasts.edit',
            Comment::class => 'admin.comments.edit',
            Media::class => 'admin.media.edit',
            Inquiry::class => 'admin.inquiries.show',
            NewsletterSubscription::class => 'admin.newsletter-subscriptions.index',
            default => null,
        };

        if ($route === null || !\Illuminate\Support\Facades\Route::has($route)) {
            return null;
        }

        try {
            return route($route, $id);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $input
     * @return array{action:string,description:string,ids?:array<int,int>,count?:int}
     */
    public function describe(string $routeName, string $method, array $input = [], ?string $subjectLabel = null): array
    {
        $routeName = str_starts_with($routeName, 'admin.') ? substr($routeName, 6) : $routeName;
        $tail = $routeName === '' ? '' : (string) Str::afterLast($routeName, '.');
        $resource = $this->resourceLabel($routeName);
        $ids = $this->extractIds($input);
        $count = $ids === [] ? null : count($ids);
        $label = $subjectLabel ? '«' . Str::limit($subjectLabel, 120, '') . '»' : null;

        [$action, $verb] = match (true) {
            $tail === 'bulk-publish' => ['published', 'نشر مجموعة'],
            $tail === 'bulk-unpublish' => ['unpublished', 'ألغى نشر مجموعة'],
            $tail === 'bulk-feature' => ['updated', 'ميّز مجموعة'],
            $tail === 'bulk-delete', $tail === 'bulkDelete' => ['deleted', 'حذف مجموعة'],
            $tail === 'toggle-status' => ['updated', 'غيّر حالة'],
            $tail === 'toggle-featured' => ['updated', 'غيّر التمييز'],
            $tail === 'toggleStatus' => ['updated', 'غيّر الحالة'],
            in_array($tail, ['mark-read', 'mark-unread'], true) => ['updated', $tail === 'mark-read' ? 'علّم كمقروء' : 'علّم كغير مقروء'],
            $tail === 'reorder' => ['updated', 'أعاد ترتيب'],
            str_contains($tail, 'download') => ['downloaded', 'حمّل'],
            str_contains($tail, 'export') => ['exported', 'صدّر'],
            in_array($tail, ['store'], true) || $method === 'POST' && $tail === '' => ['created', 'أضاف'],
            in_array($tail, ['update', 'updateProfile'], true) || $routeName === 'profile.update' => ['updated', 'عدّل'],
            $routeName === 'settings.update' => ['updated', 'حدّث إعدادات الموقع'],
            in_array($tail, ['destroy', 'delete'], true) || str_ends_with($routeName, '.files.delete') => ['deleted', 'حذف'],
            $method === 'DELETE' => ['deleted', 'حذف'],
            in_array($method, ['PUT', 'PATCH'], true) => ['updated', 'عدّل'],
            $method === 'POST' => ['created', 'نفّذ إجراء على'],
            default => ['updated', 'نفّذ إجراء على'],
        };

        if ($routeName === 'users.activity-logs.clear') {
            $action = 'deleted';
            $description = 'مسح سجل النشاط' . ($label ? ' للمستخدم ' . $label : '');
        } elseif ($routeName === 'profile.update') {
            $description = 'حدّث الملف الشخصي';
        } elseif ($routeName === 'settings.update') {
            $description = 'حدّث إعدادات الموقع';
        } elseif ($routeName === 'documents.pinned-keywords.update') {
            $action = 'updated';
            $description = 'حدّث اختصارات قسم الوثائق';
        } else {
            $description = trim($verb . ' ' . $resource);
            if ($count !== null && $count > 1) {
                $description .= ' (' . $count . ')';
            } elseif ($label) {
                $description .= ' ' . $label;
            }
        }

        $result = [
            'action' => $action,
            'description' => $description,
        ];
        if ($ids !== []) {
            $result['ids'] = array_slice($ids, 0, 30);
            $result['count'] = $count;
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<int, int>
     */
    protected function extractIds(array $input): array
    {
        foreach (['document_ids', 'ids', 'selected', 'post_ids'] as $key) {
            if (!isset($input[$key]) || !is_array($input[$key])) {
                continue;
            }

            return array_values(array_filter(array_map('intval', $input[$key])));
        }

        return [];
    }

    protected function resourceLabel(string $routeName): string
    {
        $head = str_contains($routeName, '.') ? Str::before($routeName, '.') : $routeName;

        return match ($head) {
            'documents' => 'وثيقة',
            'posts' => 'موضوع',
            'categories' => 'تصنيف',
            'users' => 'مستخدم',
            'document-sections' => 'قسم وثائق',
            'document-custom-fields' => 'حقل مخصص',
            'nashras' => 'نشرة',
            'podcasts' => 'بودكاست',
            'comments' => 'تعليق',
            'media' => 'ملف وسائط',
            'newsletter-subscriptions' => 'اشتراك النشرة',
            'inquiries' => 'رسالة تواصل',
            'keywords' => 'كلمة مفتاحية',
            'profile' => 'الملف الشخصي',
            'settings' => 'الإعدادات',
            default => $head !== '' ? $head : 'عنصر',
        };
    }

    protected function labelFor(Model $model): ?string
    {
        foreach (['title', 'title_ar', 'title_en', 'name', 'name_ar', 'subject', 'email', 'slug'] as $field) {
            $value = $model->getAttribute($field);
            if (is_string($value) && trim($value) !== '') {
                return Str::limit(trim($value), 180, '');
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function firstString(array $input): ?string
    {
        foreach ($input as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    /**
     * ملخص قصير للحقول المرسلة بدون كلمات المرور أو المحتوى الطويل.
     *
     * @return array<string, mixed>
     */
    protected function summarizeInput(Request $request): array
    {
        $skip = [
            '_token', '_method', 'password', 'password_confirmation', 'current_password',
            'content', 'content_ar', 'content_en', 'search_text', 'search_words',
            'footer_html', 'about_html_ar', 'about_html_en',
        ];

        $summary = [];
        foreach ($request->except($skip) as $key => $value) {
            if (!is_string($key) || str_contains(strtolower($key), 'password') || str_contains(strtolower($key), 'token')) {
                continue;
            }

            if (is_string($value)) {
                $value = trim($value);
                if ($value === '' || mb_strlen($value) > 160) {
                    if ($value !== '' && mb_strlen($value) > 160) {
                        $summary[$key] = Str::limit($value, 80, '…');
                    }
                    continue;
                }
                $summary[$key] = $value;
            } elseif (is_numeric($value) || is_bool($value)) {
                $summary[$key] = $value;
            } elseif (is_array($value)) {
                $summary[$key] = 'مصفوفة (' . count($value) . ')';
            }

            if (count($summary) >= 12) {
                break;
            }
        }

        return $summary;
    }
}
