<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendEndTimeNotification;


class CheckEndTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:notification-to-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        dispatch(new SendEndTimeNotification()); 
       

       
    }
}
