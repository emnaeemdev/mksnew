@extends('admin.layouts.app')

@section('title', 'عرض الاستفسار')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i> الاستفسار رقم #{{ $inquiry->id }}</h5>
        <div>
            @if($inquiry->status === \App\Models\Inquiry::STATUS_NEW)
                <form id="mark-read-form" action="{{ route('admin.inquiries.mark-read', $inquiry) }}" method="post" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-success">تعليم كمقروء</button>
                </form>
            @else
                <form id="mark-unread-form" action="{{ route('admin.inquiries.mark-unread', $inquiry) }}" method="post" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-warning">تعليم كغير مقروء</button>
                </form>
            @endif
            <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="post" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-danger">حذف</button>
            </form>
        </div>
                            <a href="{{ route('admin.inquiries.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>
                        العودة للقائمة
                    </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>الاسم:</strong>
                <div>{{ $inquiry->name }}</div>
            </div>
            <div class="col-md-6">
                <strong>البريد الإلكتروني:</strong>
                <div><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>الهاتف:</strong>
                <div>{{ $inquiry->phone ?: '-' }}</div>
            </div>
            <div class="col-md-6">
                <strong>الموضوع:</strong>
                <div>{{ $inquiry->subject ?: '-' }}</div>
            </div>
        </div>
        <div class="mb-3">
            <strong>الرسالة:</strong>
            <div class="border rounded p-3 bg-light">{!! nl2br(e($inquiry->message)) !!}</div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>الحالة:</strong>
                <div>
                    @if($inquiry->status === \App\Models\Inquiry::STATUS_NEW)
                        <span class="badge bg-warning">جديد</span>
                    @else
                        <span class="badge bg-success">مقروء</span>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <strong>اللغة:</strong>
                <div>{{ strtoupper($inquiry->locale ?: '-') }}</div>
            </div>
            <div class="col-md-4">
                <strong>التاريخ:</strong>
                <div>انشئ: {{ $inquiry->created_at->format('Y-m-d H:i') }}<br>آخر تحديث: {{ $inquiry->updated_at->format('Y-m-d H:i') }}</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('JavaScript loaded successfully');
    
    // Handle mark as read form
    const markReadForm = document.getElementById('mark-read-form');
    console.log('Mark read form:', markReadForm);
    if (markReadForm) {
        markReadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Mark read form submitted');
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                 console.log('Response received:', response);
                 return response.json();
             })
             .then(data => {
                 console.log('Data received:', data);
                 if (data.success) {
                     location.reload();
                 } else {
                     alert('حدث خطأ أثناء تحديث الحالة');
                 }
             })
            .catch(error => {
                console.error('Error:', error);
                location.reload();
            });
        });
    }
    
    // Handle mark as unread form
    const markUnreadForm = document.getElementById('mark-unread-form');
    console.log('Mark unread form:', markUnreadForm);
    if (markUnreadForm) {
        markUnreadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Mark unread form submitted');
            
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({})
            })
             .then(response => {
                 console.log('Response received (unread):', response);
                 return response.json();
             })
             .then(data => {
                 console.log('Data received (unread):', data);
                 if (data.success) {
                     // أعد التحميل مع إضافة skip_mark=1 لمنع التعليم التلقائي كمقروء في show()
                     const url = new URL(window.location.href);
                     url.searchParams.set('skip_mark', '1');
                     window.location.href = url.toString();
                 } else {
                     alert('حدث خطأ أثناء تحديث الحالة');
                 }
             })
             .catch(error => {
                 console.error('Error:', error);
                 location.reload();
             });
        });
    }
});
</script>

@endsection
