<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('books')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true])]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return redirect()->route('admin.categories.index')->with('success', "Category “{$category->name}” was added.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('success', "Category “{$category->name}” was updated.");
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return back()->with('success', "Category “{$category->name}” is now " . ($category->is_active ? 'active' : 'hidden') . '.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $count = $category->books()->count();

        if ($count > 0) {
            return back()->with('error', "“{$category->name}” still has {$count} " . str('book')->plural($count)
                . '. Move them to another category first, or deactivate the category instead.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "Category “{$category->name}” was deleted.");
    }
}
