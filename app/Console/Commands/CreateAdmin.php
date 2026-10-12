<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {email? : Login e-mail}
        {--name= : Display name}
        {--password= : Password (prompted if omitted; use --generate to get a random one)}
        {--generate : Generate a strong random password and print it once}
        {--role= : admin or editor (a new account defaults to admin; an existing account keeps its role unless given)}';

    protected $description = 'Create (or reset the password of) an admin-panel user. No password is ever stored in the source code.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('E-mail address');
        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;

        // an existing account keeps its name and role unless they are given explicitly (a password reset must not demote/rename anyone)
        $name = $this->option('name')
            ?: ($existed ? $user->name : ($this->input->isInteractive() ? $this->ask('Name', 'Administrator') : 'Administrator'));
        $role = $this->option('role') ?: ($existed ? $user->role : User::ROLE_ADMIN);

        if ($this->option('generate')) {
            $password = Str::password(16, symbols: false);
            $generated = true;
        } elseif ($this->option('password')) {
            $password = $this->option('password');
            $generated = false;
        } else {
            $password = $this->secret('Password (min 10 characters)');
            if ($password !== $this->secret('Repeat password')) {
                $this->error('The passwords do not match.');
                return self::FAILURE;
            }
            $generated = false;
        }

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password, 'role' => $role],
            [
                'email' => ['required', 'email', 'max:190'],
                'name' => ['required', 'string', 'max:120'],
                'password' => ['required', 'string', 'min:10', 'max:200'],
                'role' => ['required', 'in:' . implode(',', array_keys(User::ROLES))],
            ]
        );
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }
            return self::FAILURE;
        }

        $wasDisabled = $existed && !$user->is_active;
        $user->name = $name;
        $user->password = $password;           // hashed by the model cast
        $user->role = $role;
        $user->is_active = true;               // this command is also the recovery path for a locked-out administrator
        $user->save();

        $this->info(($existed ? 'Updated' : 'Created') . " {$role} account: {$email}");
        if ($wasDisabled) {
            $this->warn('The account was disabled and has been re-activated.');
        }
        if ($generated) {
            $this->warn("Generated password (shown once, change it after signing in): {$password}");
        }

        return self::SUCCESS;
    }
}
