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
        </div>
    </div>
</div>
@endsection