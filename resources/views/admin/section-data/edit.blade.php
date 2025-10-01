@extends('admin.layouts.app')

@section('title', 'تحرير البيانات - ' . $sectionData->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">تحرير البيانات - {{ $sectionData->title }}</h3>
                    <div>
                        <a href="{{ route('admin.section-data.show', [$section, $sectionData]) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                        <a href="{{ route('admin.section-data.index', $section) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-right"></i> العودة للقائمة
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('admin.section-data.update', [$section, $sectionData]) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
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
                                   id="title" name="title" value="{{ old('title', $sectionData->title) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- الحالة -->
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">الحالة</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status', $sectionData->status) == 'draft' ? 'selected' : '' }}>مسودة</option>
                                <option value="published" {{ old('status', $sectionData->status) == 'published' ? 'selected' : '' }}>منشور</option>
                                <option value="archived" {{ old('status', $sectionData->status) == 'archived' ? 'selected' : '' }}>مؤرشف</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- تاريخ النشر -->
                        <div class="form-group mb-3">
                            <label for="published_at" class="form-label">تاريخ النشر</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" 
                                   id="published_at" name="published_at" 
                                   value="{{ old('published_at', $sectionData->published_at ? $sectionData->published_at->format('Y-m-d\TH:i') : '') }}">
                            @error('published_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- الحقول المخصصة -->
                        @if($section->customFields->count() > 0)
                            <hr>
                            <h5 class="mb-3">الحقول المخصصة</h5>
                            
                            @foreach($section->customFields as $field)
                                @php
                                    $fieldValue = $sectionData->getFieldValue($field->id);
                                @endphp
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
                                                   value="{{ old('fields.'.$field->id, $fieldValue) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('textarea')
                                            <textarea class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                      id="field_{{ $field->id }}" 
                                                      name="fields[{{ $field->id }}]" 
                                                      rows="4"
                                                      placeholder="{{ $field->placeholder }}"
                                                      {{ $field->is_required ? 'required' : '' }}>{{ old('fields.'.$field->id, $fieldValue) }}</textarea>
                                            @break

                                        @case('number')
                                            <input type="number" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id, $fieldValue) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('email')
                                            <input type="email" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id, $fieldValue) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('url')
                                            <input type="url" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id, $fieldValue) }}"
                                                   placeholder="{{ $field->placeholder }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('date')
                                            <input type="date" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id, $fieldValue) }}"
                                                   {{ $field->is_required ? 'required' : '' }}>
                                            @break

                                        @case('datetime')
                                            <input type="datetime-local" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]" 
                                                   value="{{ old('fields.'.$field->id, $fieldValue ? \Carbon\Carbon::parse($fieldValue)->format('Y-m-d\TH:i') : '') }}"
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
                                                                {{ old('fields.'.$field->id, $fieldValue) == $option ? 'selected' : '' }}>
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
                                                               {{ old('fields.'.$field->id, $fieldValue) == $option ? 'checked' : '' }}
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
                                                @php
                                                    $selectedValues = is_array($fieldValue) ? $fieldValue : ($fieldValue ? explode(',', $fieldValue) : []);
                                                @endphp
                                                @foreach($field->options as $option)
                                                    <div class="form-check">
                                                        <input class="form-check-input" 
                                                               type="checkbox" 
                                                               name="fields[{{ $field->id }}][]" 
                                                               id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                               value="{{ $option }}"
                                                               {{ in_array($option, old('fields.'.$field->id, $selectedValues)) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                            {{ $option }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @endif
                                            @break

                                        @case('file')
                                        @case('image')
                                            @if($fieldValue)
                                                <div class="mb-2">
                                                    <small class="text-muted">الملف الحالي:</small>
                                                    @if($field->type == 'image' && in_array(pathinfo($fieldValue, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                        <div class="mt-1">
                                                            <img src="{{ asset('storage/' . $fieldValue) }}" alt="Current Image" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                                                        </div>
                                                    @else
                                                        <div class="mt-1">
                                                            <a href="{{ asset('storage/' . $fieldValue) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-download"></i> تحميل الملف
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            <input type="file" 
                                                   class="form-control @error('fields.'.$field->id) is-invalid @enderror" 
                                                   id="field_{{ $field->id }}" 
                                                   name="fields[{{ $field->id }}]"
                                                   {{ $field->type == 'image' ? 'accept="image/*"' : '' }}>
                                            <small class="form-text text-muted">اتركه فارغاً للاحتفاظ بالملف الحالي</small>
                                            @break

                                        @case('boolean')
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="field_{{ $field->id }}" 
                                                       name="fields[{{ $field->id }}]" 
                                                       value="1"
                                                       {{ old('fields.'.$field->id, $fieldValue) ? 'checked' : '' }}>
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
                            <i class="fas fa-save"></i> تحديث
                        </button>
                        <a href="{{ route('admin.section-data.show', [$section, $sectionData]) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> عرض
                        </a>
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