<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentSection;
use App\Models\DocumentCustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentSectionController extends Controller
{
    public function index()
    {
        $sections = DocumentSection::withCount(['documents', 'customFields'])
            ->orderBy('sort_order')
            ->paginate(15);
            
        return view('admin.document-sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.document-sections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:document_sections,name',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $section = DocumentSection::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? DocumentSection::max('sort_order') + 1
        ]);

        return redirect()->route('admin.document-sections.index')
            ->with('success', 'تم إنشاء القسم بنجاح');
    }

    public function show(DocumentSection $documentSection)
    {
        $documentSection->load(['customFields' => function($query) {
            $query->orderBy('sort_order');
        }, 'documents' => function($query) {
            $query->latest()->take(10);
        }]);
        
        $section = $documentSection;
        return view('admin.document-sections.show', compact('section'));
    }

    public function edit(DocumentSection $documentSection)
    {
        $section = $documentSection;
        return view('admin.document-sections.edit', compact('section'));
    }

    public function update(Request $request, DocumentSection $documentSection)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_sections')->ignore($documentSection->id)],
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'show_in_menu' => 'boolean',
            'show_in_menu_ar' => 'boolean',
            'show_in_menu_en' => 'boolean',
            'is_dropdown' => 'boolean',
            'dropdown_title' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'menu_order' => 'nullable|integer|min:0',
            'menu_order_ar' => 'nullable|integer|min:0',
            'menu_order_en' => 'nullable|integer|min:0'
        ]);

        $documentSection->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
            'show_in_menu' => $request->boolean('show_in_menu'),
            'show_in_menu_ar' => $request->boolean('show_in_menu_ar'),
            'show_in_menu_en' => $request->boolean('show_in_menu_en'),
            'is_dropdown' => $request->boolean('is_dropdown'),
            'dropdown_title' => $request->dropdown_title,
            'sort_order' => $request->sort_order ?? $documentSection->sort_order,
            'menu_order' => $request->menu_order ?? $documentSection->menu_order,
            'menu_order_ar' => $request->menu_order_ar,
            'menu_order_en' => $request->menu_order_en
        ]);

        return redirect()->route('admin.document-sections.index')
            ->with('success', 'تم تحديث القسم بنجاح');
    }

    public function destroy(DocumentSection $documentSection)
    {
        if ($documentSection->documents()->count() > 0) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف القسم لأنه يحتوي على وثائق');
        }

        $documentSection->delete();

        return redirect()->route('admin.document-sections.index')
            ->with('success', 'تم حذف القسم بنجاح');
    }

    public function toggleStatus(DocumentSection $documentSection)
    {
        $documentSection->update([
            'is_active' => !$documentSection->is_active
        ]);

        $status = $documentSection->is_active ? 'تم تفعيل' : 'تم إلغاء تفعيل';
        
        return redirect()->back()
            ->with('success', $status . ' القسم بنجاح');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:document_sections,id',
            'sections.*.sort_order' => 'required|integer|min:0'
        ]);

        foreach ($request->sections as $sectionData) {
            DocumentSection::where('id', $sectionData['id'])
                ->update(['sort_order' => $sectionData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'تم إعادة ترتيب الأقسام بنجاح']);
    }
}