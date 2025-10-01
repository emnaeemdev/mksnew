<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('sort_order')->paginate(15);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = new Category();
        $category->name_ar = $request->name_ar;
        $category->name_en = $request->name_en;
        $category->slug = Str::slug($request->name_en);
        $category->description_ar = $request->description_ar;
        $category->description_en = $request->description_en;
        $category->is_active = $request->has('is_active');
        $category->sort_order = $request->sort_order ?? 0;
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم إنشاء القسم بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'menu_order' => 'nullable|integer|min:0',
            'menu_order_ar' => 'nullable|integer|min:0',
            'menu_order_en' => 'nullable|integer|min:0',
        ]);

        $category->name_ar = $request->name_ar;
        $category->name_en = $request->name_en;
        $category->slug = Str::slug($request->name_en);
        $category->description_ar = $request->description_ar;
        $category->description_en = $request->description_en;
        $category->is_active = $request->has('is_active');
        $category->show_in_menu = $request->has('show_in_menu');
        $category->show_in_menu_ar = $request->has('show_in_menu_ar');
        $category->show_in_menu_en = $request->has('show_in_menu_en');
        $category->sort_order = $request->sort_order ?? 0;
        $category->menu_order = $request->menu_order ?? 0;
        $category->menu_order_ar = $request->menu_order_ar;
        $category->menu_order_en = $request->menu_order_en;
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم تحديث القسم بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->posts()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'لا يمكن حذف القسم لأنه يحتوي على مواضيع');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'تم حذف القسم بنجاح');
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:categories,id',
            'categories.*.sort_order' => 'required|integer|min:1'
        ]);

        foreach ($request->categories as $categoryData) {
            Category::where('id', $categoryData['id'])
                   ->update(['sort_order' => $categoryData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'تم تحديث ترتيب الأقسام بنجاح']);
    }
}
