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
        {--role=admin : admin or editor}';

    protected $description = 'Create (or reset the password of) an admin-panel user. No password is ever stored in the source code.';

    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('E-mail address');
        $name = $this->option('name') ?: ($this->input->isInteractive() ? $this->ask('Name', 'Administrator') : 'Administrator');
        $role = $this->option('role');

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

        $user = User::firstOrNew(['email' => $email]);
        $existed = $user->exists;
        $user->name = $name;
        $user->password = $password;           // hashed by the model cast
        $user->role = $role;
        $user->is_active = true;
        $user->save();

        $this->info(($existed ? 'Updated' : 'Created') . " {$role} account: {$email}");
        if ($generated) {
            $this->warn("Generated password (shown once, change it after signing in): {$password}");
        }

        return self::SUCCESS;
    }
}
