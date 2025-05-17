<?php

namespace App\Imports;

use App\Models\Task;
use App\Models\Project;
use App\Models\Category;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class TasksImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Find project by name or ID
        $project = null;
        if (isset($row['project_id'])) {
            $project = Project::find($row['project_id']);
        } elseif (isset($row['project'])) {
            $project = Project::where('name', $row['project'])->first();
        }

        if (!$project) {
            return null;
        }

        // Find category by name or ID
        $category = null;
        if (isset($row['category_id'])) {
            $category = Category::find($row['category_id']);
        } elseif (isset($row['category'])) {
            $category = Category::where('name', $row['category'])->first();
        }

        // Find assignee by name, email or ID
        $assignee = null;
        if (isset($row['assigned_to_id'])) {
            $assignee = User::find($row['assigned_to_id']);
        } elseif (isset($row['assigned_to'])) {
            $assignee = User::where('name', $row['assigned_to'])
                        ->orWhere('email', $row['assigned_to'])
                        ->first();
        }

        return new Task([
            'title' => $row['title'],
            'description' => $row['description'] ?? null,
            'project_id' => $project->id,
            'category_id' => $category ? $category->id : null,
            'user_id' => Auth::id(),
            'assigned_to' => $assignee ? $assignee->id : null,
            'priority' => strtolower($row['priority'] ?? 'medium'),
            'status' => strtolower($row['status'] ?? 'todo'),
            'due_date' => isset($row['due_date']) ? \Carbon\Carbon::parse($row['due_date']) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'project_id' => 'required_without:project',
            'project' => 'required_without:project_id',
            'priority' => 'nullable|in:low,medium,high,urgent,Low,Medium,High,Urgent',
            'status' => 'nullable|in:todo,in_progress,review,completed,Todo,In Progress,Review,Completed',
            'due_date' => 'nullable|date',
        ];
    }
}