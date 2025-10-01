@extends('admin.layouts.app')

@section('title', 'إضافة بيانات جديدة - ' . $section->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">إضافة بيانات جديدة - {{ $section->name }}</h3>
                    <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right"></i> العودة للقائمة
                    </a>
                </div>
                
                <form action="{{ route('admin.section-data.store', $section) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- العنوان -->
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">العنوان <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- الحالة -->
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">الحالة</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                <option value="archived" {{ old('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- تاريخ النشر -->
                        <div class="form-group mb-3">
                            <label for="published_at" class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" name="published_at" value="{{ old('published_at') }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- الحقول المخصصة -->
                        @if($section->customFields->count() > 0)
                            <hr>
                            <h5 class="mb-3">الحقول المخصصة</h5>
                            
                            @foreach($section->customFields as $field)
                                <div class="form-group mb-3">
                                    <label for="field_{{ $field->id }}" class="form-label">
                                        {{ $field->label }}
                                        @if($field->is_required)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    
                                    @if($field->help_text)
                                        <small class="form-text text-muted d-block">{{ $field->help_text }}</small>
                                    @endif

                                    @switch($field->type)
                                        @case('text')
                                            <input type="text" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('textarea')
                                            <textarea class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                      id="field_{{ $field->id }}" 
                                                      name="fields[{{ $field->id }}]" 
                                                      rows="4"
                                                      placeholder="{{ $field->placeholder }}"
                                                      {{ $field->is_required ? 'required' : '' }}>{{ old('fields.'.$field->id) }}</textarea>
                                            @break

                                        @case('number')
                                            <input type="number" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('email')
                                            <input type="email" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('url')
                                            <input type="url" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('date')
                                            <input type="date" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id) }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('datetime')
                                            <input type="datetime-local" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id) }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('select')
                                            <select class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                    id="field_{{ $field->id }}" 
                                                    name="fields[{{ $field->id }}]"
                                                    {{ $field->is_required ? 'required' : '' }}>
                                                <option value="">اختر...</option>
                                                @if($field->options)
                                                    @foreach($field->options as $option)
                                                        <option value="{{ $option }}" 
                                                                {{ old('fields.'.$field->id) == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @break

                                        @case('radio')
                                            @if($field->options)
                                                @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input @error('fields.'.$field->id) is-invalid @enderror" 
                                                               type="radio" 
                                                               name="fields[{{ $field->id }}]" 
                                                               id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                               value="{{ $option }}"
                                                               {{ old('fields.'.$field->id) == $option ? 'checked' : '' }}
                                                               {{ $field->is_required ? 'required' : '' }}>
                                                        <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                            @break

                                        @case('checkbox')
                                            @if($field->options)
                                                @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="fields[{{ $field->id }}][]" 
                                                               id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                               value="{{ $option }}"
                                                               {{ in_array($option, old('fields.'.$field->id, [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                            @break

                                        @case('file')
                                            <input type="file" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('image')
                                            <input type="file" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]"
                                                   accept="image/*"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('boolean')
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="field_{{ $field->id }}" 
                                                       name="fields[{{ $field->id }}]" 
                                                       value="1"
                                                       {{ old('fields.'.$field->id) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="field_{{ $field->id }}">
                                                    {{ $field->placeholder ?: 'نعم' }}
                                                </label>
                                            </div>
                                            @break
                                    @endswitch

                                    @error('fields.'.$field->id)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                لا توجد حقول مخصصة لهذا القسم. يمكنك إضافة حقول مخصصة من صفحة إدارة القسم.
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // تحديث تاريخ النشر تلقائياً عند تغيير الحالة إلى منشور
    $('#status').change(function() {
        if ($(this).val() === 'published' && !$('#published_at').val()) {
            var now = new Date();
            var year = now.getFullYear();
            var month = String(now.getMonth() + 1).padStart(2, '0');
            var day = String(now.getDate()).padStart(2, '0');
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            
            $('#published_at').val(year + '-' + month + '-' + day + 'T' + hours + ':' + minutes);
        }
    });
});
</script>
@endsection