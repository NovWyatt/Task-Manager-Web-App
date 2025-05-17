<?php

namespace App\Http\Livewire;

use App\Models\Task;
use App\Models\Project;
use Livewire\Component;

class TaskList extends Component
{
    public $project;
    public $tasks;
    public $filter = 'all';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $listeners = ['taskAdded' => 'refreshTasks', 'taskDeleted' => 'refreshTasks'];

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->refreshTasks();
    }

    public function refreshTasks()
    {
        $query = $this->project->tasks()->with(['category', 'assignee']);
        
        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }
        
        $this->tasks = $query->orderBy($this->sortField, $this->sortDirection)->get();
    }

    public function updateTaskStatus($taskId, $status)
    {
        $task = Task::findOrFail($taskId);
        $task->update(['status' => $status]);
        
        if ($status === 'completed') {
            $task->completed_at = now();
            $task->save();
        }
        
        $this->refreshTasks();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        
        $this->refreshTasks();
    }

    public function render()
    {
        return view('livewire.task-list');
    }
}