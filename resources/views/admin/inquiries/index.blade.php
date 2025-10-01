@extends('admin.layouts.app')

@section('title', 'التواصل - الاستفسارات')

@section('styles')
<style>
.table-fixed { table-layout: fixed; }
.table td, .table th { vertical-align: middle; }
.badge-status { font-size: 0.85rem; }
.message-preview { max-width: 320px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0"><i class="fas fa-envelope-open-text me-2"></i> الاستفسارات (التواصل)</h4>
    <form class="d-flex" method="get" action="{{ route('admin.inquiries.index') }}">
        <input type="text" name="q" value="{{ $search }}" class="form-control ms-2" placeholder="بحث بالاسم/البريد/الموضوع/الرسالة">
        <select name="status" class="form-select ms-2" style="width:auto">
            <option value="">الكل</option>
            <option value="{{ \App\Models\Inquiry::STATUS_NEW }}" {{ $status === \App\Models\Inquiry::STATUS_NEW ? 'selected' : '' }}>جديد</option>
            <option value="{{ \App\Models\Inquiry::STATUS_READ }}" {{ $status === \App\Models\Inquiry::STATUS_READ ? 'selected' : '' }}>مقروء</option>
        </select>
        <button class="btn btn-primary ms-2">بحث</button>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 table-fixed">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <th style="width:180px">الاسم</th>
                    <th style="width:220px">البريد</th>
                    <th style="width:140px">الهاتف</th>
                    <th style="width:180px">الموضوع</th>
                    <th>الرسالة</th>
                    <th style="width:120px">الحالة</th>
                    <th style="width:160px">التاريخ</th>
                    <th style="width:140px">التحكم</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inquiries as $inquiry)
                    <tr>
                        <td>{{ $inquiry->id }}</td>
                        <td>{{ $inquiry->name }}</td>
                        <td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td>
                        <td>{{ $inquiry->phone }}</td>
                        <td>{{ $inquiry->subject }}</td>
                        <td class="message-preview" title="{{ $inquiry->message }}">{{ $inquiry->message }}</td>
                        <td>
                            @if($inquiry->status === \App\Models\Inquiry::STATUS_NEW)
                                <span class="badge bg-warning badge-status">جديد</span>
                            @else
                                <span class="badge bg-success badge-status">مقروء</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $inquiry->created_at->format('Y-m-d H:i') }}</div>
                            @if($inquiry->read_at)
                                <small class="text-muted">قرئ: {{ $inquiry->read_at->format('Y-m-d H:i') }}</small>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="btn btn-sm btn-outline-primary">عرض</a>
                            <!-- @if($inquiry->status === \App\Models\Inquiry::STATUS_NEW)
                                <form action="{{ route('admin.inquiries.mark-read', $inquiry) }}" method="post" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-success">تعليم كمقروء</button>
                                </form>
                            @else
                                <form action="{{ route('admin.inquiries.mark-unread', $inquiry) }}" method="post" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">تعليم كغير مقروء</button>
                                </form>
                            @endif -->
                            <form action="{{ route('admin.inquiries.destroy', $inquiry) }}" method="post" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center p-4 text-muted">لا توجد استفسارات بعد.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $inquiries->links() }}
    </div>
</div>
@endsection