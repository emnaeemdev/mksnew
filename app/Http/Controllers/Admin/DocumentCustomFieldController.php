<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentCustomFieldController extends Controller
{
    public function index()
    {
        $sections = DocumentSection::with(['customFields' => function($query) {
            $query->orderBy('sort_order');
        }])->orderBy('sort_order')->get();
        
        $fields = DocumentCustomField::with('section')
            ->orderBy('section_id')
            ->orderBy('sort_order')
            ->paginate(15);
            
        return view('admin.document-custom-fields.index', compact('sections', 'fields'));
    }

    public function indexBySection(DocumentSection $section)
    {
        $fields = $section->customFields()
            ->orderBy('sort_order')
            ->paginate(15);
            
        return view('admin.document-custom-fields.section', compact('section', 'fields'));
    }

    public function create()
    {
        $sections = DocumentSection::where('is_active', true)->orderBy('sort_order')->get();
        
        $fieldTypes = [
            'text' => 'نص قصير',
            'textarea' => 'نص طويل',
            'number' => 'رقم',
            'date' => 'تاريخ',
            'datetime' => 'تاريخ ووقت',
            'select' => 'قائمة منسدلة',
            'multiselect' => 'اختيارات متعددة',
            'checkbox' => 'مربع اختيار',
            'radio' => 'اختيار واحد',
            'file' => 'ملف',
            'url' => 'رابط',
            'email' => 'بريد إلكتروني'
        ];
        
        return view('admin.document-custom-fields.create', compact('sections', 'fieldTypes'));
    }

    public function store(Request $request)
    {
        // تصفية الخيارات الفارغة قبل التحقق
        if ($request->has('options') && is_array($request->options)) {
            $request->merge([
                'options' => array_filter($request->options, function($option) {
                    return !empty(trim($option));
                })
            ]);
        }

        $request->validate([
            'section_id' => 'required|exists:document_sections,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('document_custom_fields')->where('section_id', $request->section_id)],
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,number,date,datetime,select,multiselect,checkbox,radio,file,url,email',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'validation_rules' => 'nullable|string|max:500',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500'
        ]);

        $section = DocumentSection::findOrFail($request->section_id);

        // تحويل الخيارات إلى JSON إذا كانت موجودة
        $options = null;
        if (in_array($request->type, ['select', 'multiselect', 'radio']) && $request->options) {
            $options = array_filter($request->options); // إزالة القيم الفارغة
        }

        $field = DocumentCustomField::create([
            'section_id' => $section->id,
            'name' => $request->name,
            'label' => $request->label,
            'type' => $request->type,
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? DocumentCustomField::where('section_id', $section->id)->max('sort_order') + 1,
            'validation_rules' => $request->validation_rules,
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text
        ]);

        return redirect()->route('admin.document-custom-fields.index')
            ->with('success', 'تم إنشاء الحقل بنجاح');
    }

    public function show(DocumentCustomField $documentCustomField)
    {
        $documentCustomField->load('section');
        
        // Calculate statistics for the field
        $totalValues = $documentCustomField->fieldValues()->count();
        $filledValues = $documentCustomField->fieldValues()
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->count();
        
        // Calculate value statistics for select/multiselect fields
        $valueStats = [];
        if (in_array($documentCustomField->type, ['select', 'multiselect'])) {
            $valueStats = $documentCustomField->fieldValues()
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->selectRaw('value, COUNT(*) as count')
                ->groupBy('value')
                ->pluck('count', 'value')
                ->toArray();
        }
        
        // Get documents that use this field
        $documents = \App\Models\Document::whereHas('fieldValues', function($query) use ($documentCustomField) {
            $query->where('field_id', $documentCustomField->id);
        })->with(['fieldValues' => function($query) use ($documentCustomField) {
            $query->where('field_id', $documentCustomField->id);
        }])->paginate(15);
        
        return view('admin.document-custom-fields.show', compact('documentCustomField', 'totalValues', 'filledValues', 'documents', 'valueStats'));
    }

    public function edit(DocumentCustomField $documentCustomField)
    {
        $sections = DocumentSection::where('is_active', true)->orderBy('sort_order')->get();
        
        $fieldTypes = [
            'text' => 'نص قصير',
            'textarea' => 'نص طويل',
            'number' => 'رقم',
            'date' => 'تاريخ',
            'datetime' => 'تاريخ ووقت',
            'select' => 'قائمة منسدلة',
            'multiselect' => 'اختيارات متعددة',
            'checkbox' => 'مربع اختيار',
            'radio' => 'اختيار واحد',
            'file' => 'ملف',
            'url' => 'رابط',
            'email' => 'بريد إلكتروني'
        ];
        
        return view('admin.document-custom-fields.edit', compact('sections', 'documentCustomField', 'fieldTypes'));
    }

    public function update(Request $request, DocumentCustomField $documentCustomField)
    {
        // تصفية الخيارات الفارغة قبل التحقق
        if ($request->has('options') && is_array($request->options)) {
            $request->merge([
                'options' => array_filter($request->options, function($option) {
                    return !empty(trim($option));
                })
            ]);
        }

        $request->validate([
            'section_id' => 'required|exists:document_sections,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('document_custom_fields')->where('section_id', $request->section_id)->ignore($documentCustomField->id)],
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,textarea,number,date,datetime,select,multiselect,checkbox,radio,file,url,email',
            'options' => 'nullable|array',
            'options.*' => 'string|max:255',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'validation_rules' => 'nullable|string|max:500',
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:500'
        ]);

        // تحويل الخيارات إلى JSON إذا كانت موجودة
        $options = null;
        if (in_array($request->type, ['select', 'multiselect', 'radio']) && $request->options) {
            $options = array_filter($request->options); // إزالة القيم الفارغة
        }

        $documentCustomField->update([
            'section_id' => $request->section_id,
            'name' => $request->name,
            'label' => $request->label,
            'type' => $request->type,
            'options' => $options,
            'is_required' => $request->boolean('is_required'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? $documentCustomField->sort_order,
            'validation_rules' => $request->validation_rules,
            'placeholder' => $request->placeholder,
            'help_text' => $request->help_text
        ]);

        return redirect()->route('admin.document-custom-fields.index')
            ->with('success', 'تم تحديث الحقل بنجاح');
    }

    public function destroy(DocumentCustomField $documentCustomField)
    {
        // التحقق من وجود قيم للحقل
        if ($documentCustomField->fieldValues()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف الحقل لأنه يحتوي على بيانات');
        }

        $documentCustomField->delete();

        return redirect()->route('admin.document-custom-fields.index')
            ->with('success', 'تم حذف الحقل بنجاح');
    }

    public function toggleStatus(DocumentCustomField $documentCustomField)
    {
        $documentCustomField->update([
            'is_active' => !$documentCustomField->is_active
        ]);

        $status = $documentCustomField->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        
        return redirect()->back()
            ->with('success', $status . ' الحقل بنجاح');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:document_custom_fields,id',
            'order.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->order as $fieldData) {
            DocumentCustomField::where('id', $fieldData['id'])
                ->update(['sort_order' => $fieldData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'تم إعادة ترتيب الحقول بنجاح']);
    }
}