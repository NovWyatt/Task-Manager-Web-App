<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view tasks');
    }

    public function view(User $user, Task $task)
    {
        // User can view tasks they created, are assigned to them, or in projects they're part of
        if ($user->hasPermissionTo('view tasks')) {
            return $user->id === $task->user_id || 
                   $user->id === $task->assigned_to || 
                   $user->id === $task->project->user_id ||
                   $task->project->members->contains($user);
        }

        return false;
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create tasks');
    }

    public function update(User $user, Task $task)
    {
        if ($user->hasPermissionTo('edit tasks')) {
            return $user->id === $task->user_id || 
                   $user->id === $task->assigned_to || 
                   $user->id === $task->project->user_id;
        }

        return false;
    }

    public function delete(User $user, Task $task)
    {
        if ($user->hasPermissionTo('delete tasks')) {
            return $user->id === $task->user_id || 
                   $user->id === $task->project->user_id;
        }

        return false;
    }

    public function assign(User $user, Task $task)
    {
        return $user->hasPermissionTo('assign tasks');
    }

    public function changeStatus(User $user, Task $task)
    {
        if ($user->hasPermissionTo('change task status')) {
            return $user->id === $task->user_id || 
                   $user->id === $task->assigned_to || 
                   $user->id === $task->project->user_id;
        }

        return false;
    }
}