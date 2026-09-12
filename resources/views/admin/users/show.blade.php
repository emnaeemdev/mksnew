@extends('admin.layouts.app')

@section('title', 'عرض المستخدم')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">تفاصيل المستخدم</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> العودة للقائمة
                        </a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> تعديل
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">رقم المستخدم</th>
                                    <td>{{ $user->id }}</td>
                                </tr>
                                <tr>
                                    <th>الاسم</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>اسم المستخدم</th>
                                    <td>{{ $user->username }}</td>
                                </tr>
                                <tr>
                                    <th>البريد الإلكتروني</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ التسجيل</th>
                                    <td>{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>آخر تحديث</th>
                                    <td>{{ $user->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @if($user->email_verified_at)
                                <tr>
                                    <th>تاريخ تأكيد البريد</th>
                                    <td>{{ $user->email_verified_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @else

                                @endif
                            </table>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h3 class="card-title mb-0">سجل النشاط</h3>
                        <small class="text-muted">يبدأ من تفعيل السجل ولا يشمل الأحداث السابقة. يُسجَّل الدخول والخروج والإضافة والتعديل والحذف والنشر، وليس تصفّح الصفحات.</small>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                    @if(auth()->user()?->isAdmin())
                        <form method="POST" action="{{ route('admin.users.activity-logs.clear', $user) }}" onsubmit="return confirm('مسح كل سجل نشاط هذا المستخدم؟ لا يمكن التراجع عن ذلك.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm" @disabled($logs->total() === 0)>
                                <i class="fas fa-trash"></i> مسح السجل
                            </button>
                        </form>
                    @endif
                    <form method="GET" class="d-flex gap-2">
                        <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">كل الأحداث</option>
                            @foreach([
                                'login' => 'دخول',
                                'login_failed' => 'دخول فاشل',
                                'logout' => 'خروج',
                                'created' => 'إضافة',
                                'updated' => 'تعديل',
                                'deleted' => 'حذف',
                                'published' => 'نشر',
                                'unpublished' => 'إلغاء نشر',
                                'exported' => 'تصدير',
                                'downloaded' => 'تحميل',
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected($action === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>الوقت</th>
                                    <th>النوع</th>
                                    <th>الحدث</th>
                                    <th>العنصر</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    @php
                                        $label = $log->properties['subject_label'] ?? null;
                                        $subjectUrl = $log->action === 'deleted'
                                            ? null
                                            : $activityLogger->subjectUrl($log->subject_type, $log->subject_id);
                                        $summary = is_array($log->properties['summary'] ?? null) ? $log->properties['summary'] : [];
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                        <td><span class="badge {{ $log->actionBadgeClass() }}">{{ $log->actionLabel() }}</span></td>
                                        <td>
                                            {{ $log->description }}
                                            @if($summary !== [])
                                                <div class="small text-muted mt-1">
                                                    {{ collect($summary)->map(fn ($value, $key) => $key . ': ' . (is_scalar($value) ? $value : ''))->take(6)->implode(' · ') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($label)
                                                @if($subjectUrl)
                                                    <a href="{{ $subjectUrl }}">{{ $label }}</a>
                                                @else
                                                    {{ $label }}
                                                @endif
                                            @elseif(!empty($log->properties['count']))
                                                {{ $log->properties['count'] }} عنصر
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-nowrap small">{{ $log->ip_address ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">لا توجد أحداث مسجّلة لهذا المستخدم بعد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection