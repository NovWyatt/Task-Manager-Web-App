<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueSoon;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyTasksDueSoon extends Command
{
    protected $signature = 'tasks:notify-due-soon';
    protected $description = 'Notify users about tasks due soon';

    public function handle()
    {
        // Find tasks due tomorrow
        $tomorrow = Carbon::tomorrow();
        $tasks = Task::where('status', '!=', 'completed')
            ->whereDate('due_date', $tomorrow)
            ->whereNotNull('assigned_to')
            ->with('assignee')
            ->get();

        foreach ($tasks as $task) {
            if ($task->assignee) {
                $task->assignee->notify(new TaskDueSoon($task));
                $this->info("Notification sent for task: {$task->title} to {$task->assignee->name}");
            }
        }

        $this->info("Total notifications sent: {$tasks->count()}");
        return Command::SUCCESS;
    }
}