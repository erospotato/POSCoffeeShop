<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\CategoryRequest;
use App\Models\Category;
use App\Services\AuditService;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->paginate(15);
        return view('menu.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('menu.categories.create');
    }

    public function store(CategoryRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);

        $category = Category::create($data);
        AuditService::log('category_created', $category, null, ['name' => $category->name]);

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' created.");
    }

    public function edit(Category $category)
    {
        return view('menu.categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $data['slug'] = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', false);

        $category->update($data);
        AuditService::log('category_updated', $category);

        return redirect()->route('categories.index')->with('success', "Category '{$category->name}' updated.");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category_error' => 'Cannot delete a category that contains products. Reassign or delete products first.']);
        }

        $name = $category->name;
        $category->delete();
        AuditService::log('category_deleted', null, ['name' => $name]);

        return redirect()->route('categories.index')->with('success', "Category '{$name}' deleted.");
    }
}
