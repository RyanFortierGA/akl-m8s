<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ClubBootstrap;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

use function Laravel\Prompts\password as promptPassword;

class SetupClubCommand extends Command
{
    protected $signature = 'm8s:setup
        {--email= : Admin email}
        {--password= : Admin password. Generated if omitted for a new account}
        {--name=Ryan : Admin name}
        {--no-demo : Skip demo mates and RSVP counts}';

    protected $description = 'Create the Auckland M8s admin account and sample nights';

    public function handle(ClubBootstrap $club): int
    {
        $email = (string) ($this->option('email') ?: env('ADMIN_EMAIL', 'ryan@aklm8s.nz'));
        $name = (string) ($this->option('name') ?: env('ADMIN_NAME', 'Ryan'));
        $password = $this->option('password') ?: env('ADMIN_PASSWORD');
        $password = filled($password) ? (string) $password : null;

        $exists = User::query()->where('email', $email)->exists();

        if (! $password && $this->input->isInteractive()) {
            $password = promptPassword($exists
                ? 'New password (leave empty to keep the current one)'
                : 'Admin password (leave empty to generate one)');
            $password = filled($password) ? $password : null;
        }

        $generated = null;

        if (! $password && ! $exists) {
            $generated = Str::password(16);
            $password = $generated;
        }

        $result = $club->run(
            name: $name,
            email: $email,
            password: $password,
            withDemoMates: ! $this->option('no-demo'),
        );

        $this->info($result['created'] ? 'Admin account created.' : 'Admin account updated.');
        $this->line("Email: {$email}");

        if ($result['created'] && $generated) {
            $this->warn("Password: {$generated}");
            $this->line('Save that. It will not be shown again.');
        } elseif ($result['password_set'] && ! $generated) {
            $this->line('Password has been set to the one you passed in.');
        } else {
            $this->line('Existing password was left as-is.');
        }

        $this->newLine();
        $this->info('Sample nights (edit any of these in /admin):');
        $result['events']->each(function ($event): void {
            $this->line('  · '.$event->title.'  '.$event->starts_at->timezone('Pacific/Auckland')->format('D j M g:ia'));
        });

        $this->newLine();
        $this->line('Log in at /login, then you should land on /admin.');

        return self::SUCCESS;
    }
}
