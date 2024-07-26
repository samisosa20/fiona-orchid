<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

use App\Notifications\ReminderVerifyEmail;

use App\Models\User;

class EmailReminderVerify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:reminder-verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email all users dont have verify their emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::whereNull('email_verified_at')
        ->get();

        foreach ($users as $user) {

            Notification::route('mail', $user->email)->notify(new ReminderVerifyEmail($user));
        }
        $this->info('Correos electrónicos enviados con éxito.');
    }
}
