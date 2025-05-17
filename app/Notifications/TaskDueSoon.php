<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskDueSoon extends Notification implements ShouldQueue
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
                    ->subject('Reminder: Task Due Tomorrow - ' . $this->task->title)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('This is a reminder that your task is due tomorrow.')
                    ->line('Task: ' . $this->task->title)
                    ->line('Project: ' . $this->task->project->name)
                    ->line('Priority: ' . ucfirst($this->task->priority))
                    ->line('Current Status: ' . ucfirst($this->task->status))
                    ->action('View Task', $url)
                    ->line('Thank you for using our Task Manager!');
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'message' => 'Task due tomorrow',
            'project_id' => $this->task->project_id,
            'project_name' => $this->task->project->name,
            'due_date' => $this->task->due_date ? $this->task->due_date->format('Y-m-d') : null,
        ];
    }
}