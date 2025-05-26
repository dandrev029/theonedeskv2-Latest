<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class RestoreAdminAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'restore:admin {--email=admin@admin.com} {--password=12345678}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore admin access by creating or updating admin user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        // Get admin role
        $adminRole = UserRole::where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error('Admin role not found. Please run database seeders first.');
            return 1;
        }

        // Check if admin user exists
        $admin = User::where('email', $email)->first();

        if ($admin) {
            // Update existing admin
            $admin->password = Hash::make($password);
            $admin->role_id = $adminRole->id;
            $admin->status = 1;
            $admin->email_verified_at = now();
            $admin->save();

            $this->info('Admin user updated successfully!');
        } else {
            // Create new admin
            $admin = User::create([
                'name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $adminRole->id,
                'status' => 1,
                'email_verified_at' => now(),
            ]);

            $this->info('Admin user created successfully!');
        }

        $this->info('Login credentials:');
        $this->info('Email: ' . $email);
        $this->info('Password: ' . $password);

        return 0;
    }
}
