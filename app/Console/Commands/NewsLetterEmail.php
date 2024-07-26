<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

use App\Notifications\SendNewsLetterEmail;

use App\Models\Newsletter;
use App\Models\User;

class NewsLetterEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:newsletter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send newsletter programmer in the CMS';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newsletter = Newsletter::where('sended', false)
        ->whereDate('date_delivery', Carbon::today())
        ->get();
        
        $users = User::get();
        
        foreach ($newsletter as $letter) {
            foreach ($users as $user) {
    
                Notification::route('mail', $user->email)->notify(new SendNewsLetterEmail($letter));
                $letter->sended = true;
                $letter->save();
            }
        }

        $this->info('Correos electrónicos enviados con éxito.');
    }
}
