<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Category;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Exports\TasksExport;
use App\Imports\TasksImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project', 'category', 'assignee']);

        // Filter by project
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // Filter by overdue
        if ($request->has('overdue') && $request->overdue) {
            $query->where('due_date', '<', now())
                ->where('status', '!=', 'completed');
        }

        // By default, show tasks that user can access
        $user = Auth::user();
        if (!$user->hasRole('admin')) {
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('assigned_to', $user->id)
                    ->orWhereHas('project', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->orWhereHas('members', function ($q) use ($user) {
                                $q->where('users.id', $user->id);
                            });
                    });
            });
        }

        $tasks = $query->latest()->paginate(15);
        $projects = Project::all();
        $categories = Category::all();
        $users = User::all();

        return view('tasks.index', compact('tasks', 'projects', 'categories', 'users'));
    }

    public function create(Request $request)
    {
        if (Gate::denies('create', Task::class)) {
            abort(403);
        }

        $projects = Project::all();
        $categories = Category::all();
        $users = User::all();
        $selectedProject = null;

        if ($request->has('project_id')) {
            $selectedProject = Project::findOrFail($request->project_id);
        }

        return view('tasks.create', compact('projects', 'categories', 'users', 'selectedProject'));
    }

    public function store(Request $request)
    {
        if (Gate::denies('create', Task::class)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'nullable|exists:categories,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed',
            'due_date' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        $task = Task::create($validated);

        // Handle attachments if any
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $task->addMedia($file)->toMediaCollection('attachments');
            }
        }

        // Send notification to assigned user
        if ($task->assigned_to) {
            $assignee = User::find($task->assigned_to);
            $assignee->notify(new TaskAssigned($task));
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        if (Gate::denies('view', $task)) {
            abort(403);
        }

        $task->load(['project', 'category', 'assignee', 'creator', 'comments.user']);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }

        $projects = Project::all();
        $categories = Category::all();
        $users = User::all();

        return view('tasks.edit', compact('task', 'projects', 'categories', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'nullable|exists:categories,id',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'required|in:todo,in_progress,review,completed',
            'due_date' => 'nullable|date',
        ]);

        $oldAssignedTo = $task->assigned_to;

        $task->update($validated);

        // Update task completion time if status changed to completed
        if ($task->status === 'completed' && $validated['status'] !== 'completed') {
            $task->completed_at = null;
            $task->save();
        } elseif ($task->status !== 'completed' && $validated['status'] === 'completed') {
            $task->completed_at = now();
            $task->save();
        }

        // Handle attachments if any
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $task->addMedia($file)->toMediaCollection('attachments');
            }
        }

        // Send notification if assigned user changed
        if ($validated['assigned_to'] && $oldAssignedTo !== $validated['assigned_to']) {
            $assignee = User::find($validated['assigned_to']);
            $assignee->notify(new TaskAssigned($task));
        }

        return redirect()->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        if (Gate::denies('delete', $task)) {
            abort(403);
        }

        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function addComment(Request $request, Task $task)
    {
        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $comment = $task->comments()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Comment added successfully.');
    }

    public function export(Request $request)
    {
        $projectId = $request->input('project_id');
        return Excel::download(new TasksExport($projectId), 'tasks.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls',
        ]);

        try {
            Excel::import(new TasksImport, $request->file('file'));
            return redirect()->back()->with('success', 'Tasks imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing tasks: ' . $e->getMessage());
        }
    }

    public function removeAttachment(Task $task, $mediaId)
    {
        if (Gate::denies('update', $task)) {
            abort(403);
        }

        $media = $task->getMedia('attachments')->where('id', $mediaId)->first();

        if ($media) {
            $media->delete();
            return redirect()->back()->with('success', 'Attachment removed successfully.');
        }

        return redirect()->back()->with('error', 'Attachment not found.');
    }
}
