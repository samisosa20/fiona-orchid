<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Notifications\ReportProgresMonthlyEmail;

use App\Models\Category;
use App\Models\User;

class EmailProgressMonthly extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:progress-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email all users to now how is their progress every 25th';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::get();

        foreach ($users as $user) {
            $init_date = Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = Carbon::now()->lastOfMonth()->format("Y-m-d");


            $category = Category::where([
                ['user_id', $user->id],
                ['group_id', '=', env('GROUP_TRANSFER_ID')],
            ])
            ->first();
            // get balance without transferns
            $balance = DB::select('select open_balance , incomes as income, expensives as expensive, end_balance as utility from (SELECT @user_id := ' . $user->id . ' u, @init_date := "' . $init_date . ' 00:00:00" i, @end_date := "' . $end_date . ' 23:59:59" e, @currency := ' . $user->badge_id . ' c, @category_id := ' . $category->id . ' g) alias, report_open_close_balance')[0];

            Notification::route('mail', $user->email)->notify(new ReportProgresMonthlyEmail($user, $balance));
        }
        $this->info('Correos electrónicos enviados con éxito.');
    }
}
