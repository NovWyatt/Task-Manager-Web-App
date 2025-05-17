<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('tasks')->get();
        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        if (Gate::denies('create', Category::class)) {
            abort(403);
        }
        
        return view('categories.create');
    }

    public function store(Request $request)
    {
        if (Gate::denies('create', Category::class)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category)
    {
        if (Gate::denies('view', $category)) {
            abort(403);
        }
        
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        if (Gate::denies('update', $category)) {
            abort(403);
        }
        
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        if (Gate::denies('update', $category)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        if (Gate::denies('delete', $category)) {
            abort(403);
        }
        
        // Kiểm tra nếu danh mục đã có tasks
        if ($category->tasks()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category because it has associated tasks.');
        }
        
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}