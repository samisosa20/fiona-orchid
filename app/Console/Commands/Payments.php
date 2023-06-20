<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\PlannedPayment;
use App\Models\Movement;

class Payments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create auto payments for user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $listPayments = PlannedPayment::where('start_date', '<=', now())
        ->where(function($query) {
            $query->where('end_date', '>=', now())
                  ->orWhereNull('end_date');
        })
        ->get();

        foreach ($listPayments as $value) {
            if($value->specific_day === (int)now()->format('d')){
                Movement::create([
                    'account_id' => $value->account_id,
                    'category_id' => $value->category_id,
                    'description' => $value->description,
                    'amount' => $value->amount,
                    'trm' => 1,
                    'date_purchase' => now(),
                    'user_id' => $value->user_id,
                ]);
            }
        }

        

        return Command::SUCCESS;
    }
}
