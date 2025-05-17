<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUserRoles extends Command
{
    protected $signature = 'users:list-roles';
    protected $description = 'List all users with their roles';

    public function handle()
    {
        $users = User::with('roles')->get();
        
        $rows = [];
        foreach ($users as $user) {
            $rows[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->implode(', '),
            ];
        }
        
        $this->table(['ID', 'Name', 'Email', 'Roles'], $rows);
        
        return Command::SUCCESS;
    }
}