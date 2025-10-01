<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use App\Models\DocumentFieldValue;
use App\Models\DocumentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['section', 'user', 'fieldValues.field'])
            ->latest();

        // تصفية حسب القسم
        $sectionId = $request->filled('section_id') ? $request->section_id : $request->section;
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        // تصفية حسب الحالة
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->published();
            } elseif ($request->status === 'draft') {
                $query->draft();
            }
        }

        // البحث في العنوان والمحتوى
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // فلترة حسب الحقول المخصصة
        if ($request->filled('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                if (!empty($value)) {
                    $query->whereHas('fieldValues', function($q) use ($fieldId, $value) {
                        $q->where('field_id', $fieldId)
                          ->where('value', 'like', "%{$value}%");
                    });
                }
            }
        }

        $documents = $query->paginate(15);
        $sections = DocumentSection::active()->withCount('documents')->orderBy('sort_order')->get();
        
        // جلب الحقول المخصصة للقسم المحدد
        $customFields = collect();
        $sectionId = $request->filled('section_id') ? $request->section_id : $request->section;
        if ($sectionId) {
            $selectedSection = DocumentSection::find($sectionId);
            if ($selectedSection) {
                $customFields = $selectedSection->activeCustomFields()->get();
                
                // إضافة عدد الوثائق لكل خيار في الحقول المخصصة
                foreach ($customFields as $field) {
                    if (in_array($field->type, ['select', 'multiselect']) && $field->options) {
                        $optionsWithCounts = [];
                        foreach ($field->options as $option) {
                            $count = \App\Models\DocumentFieldValue::where('field_id', $field->id)
                                ->where('value', 'like', "%{$option}%")
                                ->whereHas('document', function($q) use ($sectionId) {
                                    $q->where('section_id', $sectionId);
                                })
                                ->count();
                            $optionsWithCounts[] = [
                                'value' => $option,
                                'label' => $option,
                                'count' => $count
                            ];
                        }
                        $field->options_with_counts = $optionsWithCounts;
                    }
                }
            }
        }
        
        // إحصائيات الوثائق
        $stats = [
            'total' => Document::count(),
            'published' => Document::where('is_published', true)->count(),
            'draft' => Document::where('is_published', false)->count(),
            'featured' => Document::where('is_featured', true)->count()
        ];

        return view('admin.documents.index', compact('documents', 'sections', 'stats', 'customFields'));
    }

    public function create(Request $request)
    {
        $sections = DocumentSection::active()->orderBy('sort_order')->get();
        $selectedSection = null;
        $customFields = collect();

        // التحقق من معامل section أو section_id
        $sectionId = $request->filled('section') ? $request->section : $request->section_id;
        
        if ($sectionId) {
            $selectedSection = DocumentSection::findOrFail($sectionId);
            $customFields = $selectedSection->customFields()->active()->orderBy('sort_order')->get();
        }

        return view('admin.documents.create', compact('sections', 'selectedSection', 'customFields'));
    }

    public function store(Request $request)
    {
        $section = DocumentSection::findOrFail($request->section_id);
        
        $rules = [
            'section_id' => 'required|exists:document_sections,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|file|max:10240',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'document_files.*' => 'nullable|file|max:51200', // 50MB max per file
            'file_display_names.*' => 'nullable|string|max:255'
        ];

        // إضافة قواعد التحقق للحقول المخصصة
        $customFields = $section->customFields()->active()->get();
        foreach ($customFields as $field) {
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field->type) {
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    break;
                case 'select':
                case 'radio':
                    if ($field->options) {
                        $fieldRules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
                case 'multiselect':
                    $fieldRules[] = 'array';
                    if ($field->options) {
                        $fieldRules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
            }

            if ($field->validation_rules) {
                $fieldRules[] = $field->validation_rules;
            }

            $rules["custom_fields.{$field->id}"] = implode('|', $fieldRules);
        }

        $request->validate($rules);

        // رفع الصورة المميزة
        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $featuredImagePath = $request->file('featured_image')->store('documents', 'public');
        }

        // إنشاء الوثيقة
        $document = Document::create([
            'section_id' => $request->section_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'featured_image' => $featuredImagePath,
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $request->boolean('is_published') ? ($request->published_at ?? now()) : null,
            'user_id' => Auth::id()
        ]);

        // حفظ قيم الحقول المخصصة
        if ($request->has('custom_fields')) {
            foreach ($request->custom_fields as $fieldId => $value) {
                $field = $customFields->find($fieldId);
                if ($field && $value !== null) {
                    // معالجة خاصة للملفات
                    if ($field->type === 'file' && $request->hasFile("custom_fields.{$fieldId}")) {
                        $value = $request->file("custom_fields.{$fieldId}")->store('documents/custom-fields', 'public');
                    }
                    // معالجة خاصة للاختيارات المتعددة
                    elseif ($field->type === 'multiselect' && is_array($value)) {
                        $value = json_encode($value);
                    }

                    DocumentFieldValue::create([
                        'document_id' => $document->id,
                        'field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }
        }

        // معالجة رفع الملفات المتعددة
        if ($request->hasFile('document_files')) {
            $files = $request->file('document_files');
            $displayNames = $request->input('file_display_names', []);
            
            foreach ($files as $index => $file) {
                if ($file && $file->isValid()) {
                    $originalName = $file->getClientOriginalName();
                    $displayName = $displayNames[$index] ?? pathinfo($originalName, PATHINFO_FILENAME);
                    $filePath = $file->store('documents/files', 'public');
                    
                    \App\Models\DocumentFile::create([
                        'document_id' => $document->id,
                        'file_path' => $filePath,
                        'original_name' => $originalName,
                        'display_name' => $displayName,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'sort_order' => $index + 1
                    ]);
                }
            }
        }

        return redirect()->route('admin.documents.index')
            ->with('success', 'تم إنشاء الوثيقة بنجاح');
    }

    public function show(Document $document)
    {
        $document->load(['section', 'user', 'fieldValues.field']);
        
        // الوثائق ذات الصلة من نفس القسم
        $relatedDocuments = $document->section->documents()
            ->where('id', '!=', $document->id)
            ->latest('published_at')
            ->take(6)
            ->get();
        
        return view('admin.documents.show', compact('document', 'relatedDocuments'));
    }

    public function edit(Document $document)
    {
        $sections = DocumentSection::active()->orderBy('sort_order')->get();
        $customFields = $document->section->customFields()->active()->orderBy('sort_order')->get();
        $fieldValues = $document->fieldValues()->with('field')->get()->keyBy('field_id');

        return view('admin.documents.edit', compact('document', 'sections', 'customFields', 'fieldValues'));
    }

    public function update(Request $request, Document $document)
    {
        $section = DocumentSection::findOrFail($request->section_id);
        
        $rules = [
            'section_id' => 'required|exists:document_sections,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|file|max:10240',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'document_files.*' => 'nullable|file|max:51200', // 50MB per file
            'file_display_names.*' => 'nullable|string|max:255'
        ];

        // إضافة قواعد التحقق للحقول المخصصة
        $customFields = $section->customFields()->active()->get();
        foreach ($customFields as $field) {
            $fieldRules = [];
            
            if ($field->is_required) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            switch ($field->type) {
                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                case 'datetime':
                    $fieldRules[] = 'date';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    break;
                case 'select':
                case 'radio':
                    if ($field->options) {
                        $fieldRules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
                case 'multiselect':
                    $fieldRules[] = 'array';
                    if ($field->options) {
                        $fieldRules[] = 'in:' . implode(',', $field->options);
                    }
                    break;
            }

            if ($field->validation_rules) {
                $fieldRules[] = $field->validation_rules;
            }

            $rules["custom_fields.{$field->id}"] = implode('|', $fieldRules);
        }

        $request->validate($rules);

        // رفع الصورة المميزة الجديدة
        $featuredImagePath = $document->featured_image;
        if ($request->hasFile('featured_image')) {
            // حذف الصورة القديمة
            if ($document->featured_image) {
                Storage::disk('public')->delete($document->featured_image);
            }
            $featuredImagePath = $request->file('featured_image')->store('documents', 'public');
        }

        // تحديث الوثيقة
        $document->update([
            'section_id' => $request->section_id,
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'featured_image' => $featuredImagePath,
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'published_at' => $request->boolean('is_published') ? ($request->published_at ?? $document->published_at ?? now()) : null
        ]);

        // معالجة الملفات الجديدة المرفوعة
        if ($request->hasFile('document_files')) {
            $files = $request->file('document_files');
            $displayNames = $request->input('file_display_names', []);
            
            foreach ($files as $index => $file) {
                $originalName = $file->getClientOriginalName();
                $displayName = $displayNames[$index] ?? pathinfo($originalName, PATHINFO_FILENAME);
                $filePath = $file->store('documents/files', 'public');
                
                DocumentFile::create([
                    'document_id' => $document->id,
                    'file_path' => $filePath,
                    'original_name' => $originalName,
                    'display_name' => $displayName,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'sort_order' => $document->files()->count() + $index + 1
                ]);
            }
        }
        
        // تحديث قيم الحقول المخصصة
        if ($request->has('custom_fields')) {
            // حذف القيم القديمة للحقول المخصصة
            $document->fieldValues()->delete();
            
            foreach ($request->custom_fields as $fieldId => $value) {
                $field = $customFields->find($fieldId);
                if ($field && $value !== null) {
                    // معالجة خاصة للملفات
                    if ($field->type === 'file' && $request->hasFile("custom_fields.{$fieldId}")) {
                        $value = $request->file("custom_fields.{$fieldId}")->store('documents/custom-fields', 'public');
                    }
                    // معالجة خاصة للاختيارات المتعددة
                    elseif ($field->type === 'multiselect' && is_array($value)) {
                        $value = json_encode($value);
                    }

                    DocumentFieldValue::create([
                        'document_id' => $document->id,
                        'field_id' => $fieldId,
                        'value' => $value
                    ]);
                }
            }
        }

        // return redirect()->route('admin.documents.index')
        //     ->with('success', 'تم تحديث الوثيقة بنجاح');
    return back()->with('success', 'تم تحديث الوثيقة بنجاح');
    }

    public function destroy(Document $document)
    {
        // حذف الصورة المميزة
        if ($document->featured_image) {
            Storage::disk('public')->delete($document->featured_image);
        }

        // حذف ملفات الحقول المخصصة
        foreach ($document->fieldValues as $fieldValue) {
            if ($fieldValue->field->type === 'file' && $fieldValue->value) {
                Storage::disk('public')->delete($fieldValue->value);
            }
        }
        
        // حذف الملفات المرفقة
        foreach ($document->files as $file) {
            if ($file->file_path) {
                Storage::disk('public')->delete($file->file_path);
            }
        }

        $document->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'تم حذف الوثيقة بنجاح');
    }

    public function toggleStatus(Document $document)
    {
        $document->update([
            'is_published' => !$document->is_published,
            'published_at' => !$document->is_published ? now() : null
        ]);

        $status = $document->is_published ? 'تم نشر' : 'تم إلغاء نشر';
        
        return redirect()->back()
            ->with('success', $status . ' الوثيقة بنجاح');
    }

    public function toggleFeatured(Document $document)
    {
        $document->update([
            'is_featured' => !$document->is_featured
        ]);

        $status = $document->is_featured ? 'تم تمييز' : 'تم إلغاء تمييز';
        
        return redirect()->back()
            ->with('success', $status . ' الوثيقة بنجاح');
    }

    public function bulkPublish(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id'
        ]);

        $documents = Document::whereIn('id', $request->document_ids)->get();
        
        foreach ($documents as $document) {
            $document->update([
                'is_published' => true,
                'published_at' => $document->published_at ?? now()
            ]);
        }

        return redirect()->route('admin.documents.index')
            ->with('success', 'تم نشر ' . count($documents) . ' وثيقة بنجاح');
    }

    public function bulkUnpublish(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id'
        ]);

        $documents = Document::whereIn('id', $request->document_ids)->get();
        
        foreach ($documents as $document) {
            $document->update([
                'is_published' => false
            ]);
        }

        return redirect()->route('admin.documents.index')
            ->with('success', 'تم إلغاء نشر ' . count($documents) . ' وثيقة بنجاح');
    }

    public function bulkFeature(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id'
        ]);

        $documents = Document::whereIn('id', $request->document_ids)->get();
        
        foreach ($documents as $document) {
            $document->update([
                'is_featured' => true
            ]);
        }

        return redirect()->route('admin.documents.index')
            ->with('success', 'تم تمييز ' . count($documents) . ' وثيقة بنجاح');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id'
        ]);

        $documents = Document::whereIn('id', $request->document_ids)->get();
        
        foreach ($documents as $document) {
            // حذف الصورة المميزة
            if ($document->featured_image) {
                Storage::disk('public')->delete($document->featured_image);
            }

            // حذف ملفات الحقول المخصصة
            foreach ($document->fieldValues as $fieldValue) {
                if ($fieldValue->customField->type === 'file' && $fieldValue->value) {
                    Storage::disk('public')->delete($fieldValue->value);
                }
            }

            $document->delete();
        }

        return redirect()->route('admin.documents.index')
            ->with('success', 'تم حذف ' . count($documents) . ' وثيقة بنجاح');
    }
    
    public function deleteFile(DocumentFile $file)
    {
        // حذف الملف من التخزين
        if ($file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        // حذف السجل من قاعدة البيانات
        $file->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'تم حذف الملف بنجاح'
        ]);
    }
    
    public function downloadFile(DocumentFile $file)
    {
        $filePath = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404, 'الملف غير موجود');
        }
        
        return response()->download($filePath, $file->original_name);
    }

    public function getCustomFields(Request $request)
    {
        $section = DocumentSection::findOrFail($request->section_id);
        $customFields = $section->customFields()->active()->orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'fields' => $customFields->map(function($field) {
                return [
                    'id' => $field->id,
                    'name' => $field->name,
                    'label' => $field->label,
                    'type' => $field->type,
                    'options' => $field->options,
                    'is_required' => $field->is_required,
                    'placeholder' => $field->placeholder,
                    'help_text' => $field->help_text
                ];
            })
        ]);
    }
}