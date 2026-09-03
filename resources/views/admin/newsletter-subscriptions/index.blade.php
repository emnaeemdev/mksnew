@extends('admin.layouts.app')

@section('title', 'اشتراكات النشرة البريدية')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">اشتراكات النشرة البريدية</h1>
    <div class="d-flex gap-2 align-items-center">
        <form method="get" action="{{ route('admin.newsletter-subscriptions.index') }}" class="d-flex" role="search">
            <input type="text" name="q" value="{{ $search }}" class="form-control me-2" placeholder="بحث بالاسم أو البريد">
            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
        </form>
        <a class="btn btn-success" href="{{ route('admin.newsletter-subscriptions.export', request()->only('q')) }}">
            <i class="fa fa-download me-1"></i> تصدير CSV
        </a>
        <button type="button" id="copyAllEmailsBtn" class="btn btn-secondary">
            <i class="fa fa-copy me-1"></i> نسخ كل الإيميلات (الصفحة)
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width:36px"><input type="checkbox" id="selectAll"></th>
                        <th>#</th>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>تاريخ الاشتراك</th>
                        <th class="text-end">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $subscription->id }}" class="row-check"></td>
                            <td>{{ $subscription->id }}</td>
                            <td>{{ $subscription->name }}</td>
                            <td class="email-cell">{{ $subscription->email }}</td>
                            <td>{{ $subscription->created_at->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary copy-email-btn" data-email="{{ $subscription->email }}" title="نسخ البريد">
                                    <i class="fa fa-copy"></i>
                                </button>
                                <form action="{{ route('admin.newsletter-subscriptions.destroy', $subscription) }}" method="POST" onsubmit="return confirm('حذف هذا الاشتراك؟');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد اشتراكات حتى الآن</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div>
            <form id="bulkDeleteForm" method="POST" action="{{ route('admin.newsletter-subscriptions.bulk-delete') }}" class="d-none">
                @csrf
            </form>
            <button type="button" id="bulkDeleteBtn" class="btn btn-danger" disabled>
                <i class="fa fa-trash me-1"></i> مسح المحدد
            </button>
        </div>
        @if($subscriptions->hasPages())
            <div>
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function copyText(text, onSuccess) {
        if (!text) {
            return;
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(onSuccess).catch(function () {
                fallbackCopy(text, onSuccess);
            });
            return;
        }

        fallbackCopy(text, onSuccess);
    }

    function fallbackCopy(text, onSuccess) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.setAttribute('readonly', '');
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();

        try {
            document.execCommand('copy');
            if (onSuccess) {
                onSuccess();
            }
        } catch (error) {
            alert('تعذر نسخ النص');
        }

        document.body.removeChild(textarea);
    }

    function flashButton(button) {
        button.classList.add('btn-success');
        setTimeout(function () {
            button.classList.remove('btn-success');
        }, 800);
    }

    const selectAll = document.getElementById('selectAll');
    const checks = document.querySelectorAll('.row-check');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    function updateBulkDeleteState() {
        if (!bulkDeleteBtn) {
            return;
        }

        const selectedCount = document.querySelectorAll('.row-check:checked').length;
        bulkDeleteBtn.disabled = selectedCount === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checks.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
            updateBulkDeleteState();
        });
    }

    checks.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateBulkDeleteState);
    });

    document.querySelectorAll('.copy-email-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const email = this.dataset.email || '';
            copyText(email, function () {
                flashButton(button);
            });
        });
    });

    const copyAllBtn = document.getElementById('copyAllEmailsBtn');
    if (copyAllBtn) {
        copyAllBtn.addEventListener('click', function () {
            const emails = Array.from(document.querySelectorAll('td.email-cell'))
                .map(function (cell) { return cell.textContent.trim(); })
                .filter(Boolean)
                .join('\n');

            if (!emails) {
                alert('لا توجد إيميلات في هذه الصفحة');
                return;
            }

            copyText(emails, function () {
                flashButton(copyAllBtn);
            });
        });
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const selected = Array.from(document.querySelectorAll('.row-check:checked')).map(function (checkbox) {
                return checkbox.value;
            });

            if (selected.length === 0) {
                alert('يرجى اختيار عناصر للحذف');
                return;
            }

            if (!confirm('هل تريد حذف العناصر المحددة؟')) {
                return;
            }

            const form = document.getElementById('bulkDeleteForm');
            form.querySelectorAll('input[name="ids[]"]').forEach(function (input) {
                input.remove();
            });

            selected.forEach(function (id) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                form.appendChild(input);
            });

            form.submit();
        });
    }
});
</script>
@endpush
