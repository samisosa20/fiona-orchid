<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Notifications\ReminderLoginEmail;

use App\Models\User;

class EmailReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email all users that dont have move for more 10 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::addSelect([
            'last_movement' => DB::table('movements')->selectRaw('max(created_at)')->whereColumn('users.id', 'movements.user_id')
        ])
        ->get();

        foreach ($users as $user) {
            if($user->last_movement) {
                $fecha = Carbon::parse($user->last_movement);
                $diasDiferencia = $fecha->diffInDays(Carbon::now());
                if ($diasDiferencia >= 10) {
                    Notification::route('mail', $user->email)->notify(new ReminderLoginEmail([]));
                }
            } else {
                Notification::route('mail', $user->email)->notify(new ReminderLoginEmail([]));
            }
        }
        $this->info('Correos electrónicos enviados con éxito.');
    }
}
