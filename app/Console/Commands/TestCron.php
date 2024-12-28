<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Mail;
use DateTime;

use function Laravel\Prompts\select;

class TestCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $currentPackage = DB::table('user_subscription_purchase_history as usph')
            ->select('users.f_name', 'users.email', 'users.phone', 'usph.package_name', 'usph.end_date',)
            ->leftJoin('users', 'usph.user_id', '=', 'users.id')
            ->where('end_date', '>=', \Carbon\Carbon::now())
            ->get();

        foreach ($currentPackage as $key => $value ){
            $end_date = $value->end_date;
            $current_date = \Carbon\Carbon::now();

            $datetime1 = new DateTime($end_date);
            $datetime2 = \Carbon\Carbon::now();
            $interval = $datetime1->diff($datetime2);

            $email = $value->email;
            $name =  $value->f_name;

            if ($interval->days == 14) {

                $data = array("email" => $email, "name" => $name, "package_name" => $value->package_name, "end_date" => $value->end_date);
                // Send email
                Mail::send('email.subscriptionAlert15', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Reminder: Your FindMyGuru Subscription is Expiring Soon!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
                
            } elseif ($interval->days == 6) {

                $data = array("email" => $email, "name" => $name, "package_name" => $value->package_name, "end_date" => $value->end_date);
                // Send email
                Mail::send('email.subscriptionAlert7', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Your FindMyGuru Subscription Expires in 7 Days!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

            } elseif ($interval->days == 0) {

                $data = array("email" => $email, "name" => $name, "package_name" => $value->package_name, "end_date" => $value->end_date);
                // Send email
                Mail::send('email.subscriptionAlert1', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Final Reminder: Your FindMyGuru Subscription Expires Tomorrow!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

            }
        };

        info("Cron job running at " . now());
    }
}
