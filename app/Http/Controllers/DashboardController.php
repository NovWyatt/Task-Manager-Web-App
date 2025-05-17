<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Lấy các project mà user là thành viên hoặc chủ sở hữu
        $projects = Project::where('user_id', $user->id)
            ->orWhereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->withCount(['tasks', 'completedTasks'])
            ->latest()
            ->take(5)
            ->get();

        // Lấy các task được gán cho user
        $assignedTasks = Task::where('assigned_to', $user->id)
            ->with(['project', 'category'])
            ->where('status', '!=', 'completed')
            ->orderBy('due_date')
            ->take(10)
            ->get();

        // Lấy các task đã quá hạn
        $overdueTasks = Task::where('assigned_to', $user->id)
            ->with(['project', 'category'])
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->get();

        // Tasks hoàn thành gần đây
        $recentlyCompletedTasks = Task::where('assigned_to', $user->id)
            ->with(['project', 'category'])
            ->where('status', 'completed')
            ->latest('completed_at')
            ->take(5)
            ->get();

        return view('dashboard', compact('projects', 'assignedTasks', 'overdueTasks', 'recentlyCompletedTasks'));
    }
}
