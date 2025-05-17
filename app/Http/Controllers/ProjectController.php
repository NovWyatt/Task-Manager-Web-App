<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->latest()->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:not_started,in_progress,on_hold,completed',
        ]);

        $validated['user_id'] = Auth::id();

        $project = Project::create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function addMember(Request $request, Project $project)
    {
        // Kiểm tra quyền
        if (Gate::denies('manageMembers', $project)) {
            abort(403);
        }

        // Validate request
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Kiểm tra user không phải là owner và chưa là member
        $user = \App\Models\User::findOrFail($validated['user_id']);

        if ($user->id == $project->user_id) {
            return redirect()->back()->with('error', 'User is already the project owner.');
        }

        if ($project->members->contains($user)) {
            return redirect()->back()->with('error', 'User is already a member of this project.');
        }

        // Thêm thành viên
        $project->members()->attach($user->id);

        return redirect()->back()->with('success', 'Member added successfully.');
    }

    public function removeMember(Project $project, User $user)
    {
        // Kiểm tra quyền
        if (Gate::denies('manageMembers', $project)) {
            abort(403);
        }

        // Kiểm tra user có phải là member không
        if (!$project->members->contains($user)) {
            return redirect()->back()->with('error', 'User is not a member of this project.');
        }

        // Xóa thành viên
        $project->members()->detach($user->id);

        return redirect()->back()->with('success', 'Member removed successfully.');
    }

    public function show(Project $project)
    {
        if (Gate::denies('view', $project)) {
            abort(403);
        }

        $tasks = $project->tasks()->with(['category', 'assignee'])->get();
        $categories = \App\Models\Category::all();

        return view('projects.show', compact('project', 'tasks', 'categories'));
    }

    public function edit(Project $project)
    {
        if (Gate::denies('update', $project)) {
            abort(403);
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        if (Gate::denies('update', $project)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:not_started,in_progress,on_hold,completed',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if (Gate::denies('delete', $project)) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
