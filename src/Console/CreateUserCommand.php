<?php
// src/Console/CreateUserCommand.php

namespace OpenAdminCore\Admin\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a admin user';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $userModel = config('admin.database.users_model');
        $roleModel = config('admin.database.roles_model');

        $username = $this->ask('Please enter a username to login');

        // Проверяем уникальность username
        if ($userModel::where('username', $username)->exists()) {
            $this->error('Username already exists. Please choose another.');
            return;
        }

        // Запрашиваем email (опционально)
        $email = $this->ask('Please enter an email address (optional, required for password reset)');

        // Если email указан, проверяем его валидность и уникальность
        if (!empty($email)) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Invalid email format. User not created.');
                return;
            }

            if ($userModel::where('email', $email)->exists()) {
                $this->error('Email already exists. Please choose another.');
                return;
            }
        } else {
            $this->warn('Warning: User created without email. Password reset will not be possible for this user.');
        }

        $password = $this->secret('Please enter a password to login');

        if (empty($password)) {
            $this->error('Password cannot be empty.');
            return;
        }

        $passwordConfirmation = $this->secret('Please confirm the password');

        if ($password !== $passwordConfirmation) {
            $this->error('Passwords do not match.');
            return;
        }

        $name = $this->ask('Please enter a name to display');

        if (empty($name)) {
            $name = $username;
        }

        $roles = $roleModel::all();

        $selectedRoles = [];
        if ($roles->isNotEmpty()) {
            $roleNames = $roles->pluck('name')->toArray();
            $selectedRoleNames = $this->choice('Please choose a role for the user (comma separated for multiple)', $roleNames, null, null, true);

            if (!empty($selectedRoleNames)) {
                $selectedRoles = $roles->filter(function ($role) use ($selectedRoleNames) {
                    return in_array($role->name, $selectedRoleNames);
                });
            }
        }

        $userData = compact('username', 'name');
        $userData['password'] = Hash::make($password);

        if (!empty($email)) {
            $userData['email'] = $email;
        }

        $user = new $userModel($userData);
        $user->save();

        if (!empty($selectedRoles)) {
            $user->roles()->attach($selectedRoles);
        }

        $this->info("User [$name] created successfully.");

        if (empty($email)) {
            $this->warn('Note: This user has no email and cannot use password reset functionality.');
        }
    }
}
