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
                                <tr>
                                    <th>حالة البريد الإلكتروني</th>
                                    <td><span class="badge bg-warning">غير مؤكد</span></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">إحصائيات المستخدم</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-primary">{{ $user->posts()->count() ?? 0 }}</h4>
                                                <p class="text-muted mb-0">المقالات</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $user->created_at->diffInDays(now()) }}</h4>
                                            <p class="text-muted mb-0">يوم منذ التسجيل</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">الإجراءات</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> تعديل المستخدم
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> حذف المستخدم
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection