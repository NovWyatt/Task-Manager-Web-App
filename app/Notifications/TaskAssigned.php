<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = url('/tasks/' . $this->task->id);

        return (new MailMessage)
                    ->subject('New Task Assigned: ' . $this->task->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('You have been assigned a new task in project "' . $this->task->project->name . '".')
                    ->line('Task: ' . $this->task->title)
                    ->line('Priority: ' . ucfirst($this->task->priority))
                    ->line('Due date: ' . ($this->task->due_date ? $this->task->due_date->format('M d, Y') : 'No due date'))
                    ->action('View Task', $url)
                    ->line('Thank you for using our Task Manager!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'priority' => $this->task->priority,
            'due_date' => $this->task->due_date ? $this->task->due_date->format('Y-m-d') : null,
        ];
    }
}