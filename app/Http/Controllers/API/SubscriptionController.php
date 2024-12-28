<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Throwable;
use DB;
use Session;


class SubscriptionController extends BaseController
{

    public function subscriptionPurchase(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'subcription_id' => 'required',
                'transuction_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $subscription_details = DB::table('subscription_plans')->where('id', $request->subcription_id)->first();

            if (!$subscription_details) {
                return $this->sendErrorResponse('Subscription plan not found', '', 404);
            }

            $purchase_history = DB::table('user_subscription_purchase_history as usph')
            ->where('user_id', $request->user_id)
            ->orderBy('usph.created_at', 'desc')->first();

            // Calculate start and end dates
            $start_date = \Carbon\Carbon::now();
            $duration_in_months = $subscription_details->duration_in_months;

            if ($purchase_history) {
                $existing_end_date = \Carbon\Carbon::parse($purchase_history->end_date);

                if ($existing_end_date->isFuture()) {
                    $start_date = \Carbon\Carbon::parse($purchase_history->start_date);
                    $end_date = $existing_end_date->addMonths($duration_in_months);
                } else {
                    $end_date = $start_date->copy()->addMonths($duration_in_months);
                }
            } else {
                $end_date = $start_date->copy()->addMonths($duration_in_months);
            }

            $paymentData = [
                'user_id' => $request->user_id,
                'payment_id' => '1234',
                'payment_method' => 'Online',
                'amount' => $request->amount_paid,
                'status' => 'Success',
                'transaction_id' => $request->transuction_id,
                'payment_type' => 'subscription',
            ];

            $paymentId = DB::table('payment_history')->insertGetId($paymentData);

            $insertedData = [
                'user_id' => $request->user_id,
                'subcription_id' => $request->subcription_id,
                'payment_history_id' => $paymentId,
                'subcription_date' => \Carbon\Carbon::now(),
                'package_name' => $subscription_details->title,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'amount_paid' => $request->amount_paid,
                'gst_amount' => $request->gst_amount,
                'payment_status' => "Success",
                'transuction_id' => $request->transuction_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            //return $this->sendSuccessResponse('Subscription added successfully.', $insertedData);

            $storeInfo = DB::table('user_subscription_purchase_history')->insert($insertedData);

            $user_details = DB::table('users')->where('id', $request->user_id)->first();

            if ($user_details) {
                $email = $user_details->email;
                $name =  $user_details->f_name;
                $data = array("email" => $email, "name" => $name, "package_name" => $request->package_name, "amount_paid" => $request->amount_paid, "start_date" => $request->start_date, "end_date" => $request->end_date);
                // Send email
                Mail::send('email.subscriptionPurchase', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Your Subscription to FindMyGuru Has Been Confirmed!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

                //==========
                $adminData = array(
                    "user_name" => $name,
                    "email" => $email,
                    "package_name" => $request->package_name,
                    "start_date" => $request->start_date,
                    "end_date" => $request->end_date,
                    "amount_paid" => $request->amount_paid,
                    "purchase_date" => \Carbon\Carbon::now()
                );
                $adminEmail = env('ADMIN_MAIL');
                // Send email
                Mail::send('email.admin.subscriptionPurchaseNotification', $adminData, function ($message) use ($adminEmail) {
                    $message->to($adminEmail) // Use the recipient's email
                        ->subject('Subscription Purchase Alert on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Subscription added successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Subscription added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
     public function subscriptionHistory(Request $request, $id): JsonResponse
    {
        try {
            $user_id = $id;

            $authuser_id = Auth::user()->id;
            if ($user_id != $authuser_id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $data = DB::table('user_subscription_purchase_history as usph')
            ->select('usph.id', 'sp.title as package_name', 'usph.subcription_date as purchase_date', 'usph.end_date', 'usph.amount_paid')
            ->leftJoin('users', 'usph.user_id', '=', 'users.id')
            ->leftJoin('subscription_plans as sp', 'usph.subcription_id', '=', 'sp.id')
                ->orderBy('usph.created_at', 'desc')
                ->where('usph.user_id', $user_id)
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            $currentPackage = DB::table('user_subscription_purchase_history as usph')
                ->select('sp.title as package_name', 'usph.subcription_date as purchase_date', 'usph.end_date', 'usph.amount_paid')
                ->leftJoin('subscription_plans as sp', 'usph.subcription_id', '=', 'sp.id')
                ->where('usph.user_id', $user_id)
                ->where('usph.end_date', '>=', \Carbon\Carbon::now())
                ->first();

            $array = ['current_package' => null, 'end_date' => null];
            if ($currentPackage) {
                $array = ['current_package' => $currentPackage->package_name, 'end_date' => $currentPackage->end_date];
            }

            $object = (object)[
                'currentPackageDetails' => $array,
                'packageHistory' => $data,
            ];
            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Subscription history fetching successfully.', $object);
        } catch (\Throwable $th) {
            Log::error('Subscription history fetching error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
   

    public function deleteSubscription(Request $request, $id): JsonResponse
    {
        try {
            $user_id = Auth::user()->id;
            $subscription_id = $id;
            if (!$user_id) {
                return $this->sendErrorResponse('Authentication error!', '', 403);
            }

            $data = DB::table('user_subscription_purchase_history')
                ->where('user_id', $user_id)
                ->where('id', $subscription_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Record not found', '', 404);
            }

            DB::table('user_subscription_purchase_history')
                ->where('id', $subscription_id)
                ->delete();

            return $this->sendSuccessResponse('Subscription history deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Subscription history deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

   public function coinPurchase(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'coin_package_id' => 'required',
                'amount_paid' => 'required',
                'transuction_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors());
            }

            $package = DB::table('coin_packages_plans')->where('id', $request->coin_package_id)->first();


            if (!$package) {
                return $this->sendErrorResponse('Coin package not found.', '');
            }

            $paymentData = [
                'user_id' => $request->user_id,
                'payment_id' => '1234',
                'payment_method' => 'Online',
                'amount' => $request->amount_paid,
                'status' => 'Success',
                'transaction_id' => $request->transuction_id,
                'payment_type' => 'coin',
            ];

            $paymentId = DB::table('payment_history')->insertGetId($paymentData);

            $insertedData = [
                'user_id' => $request->user_id,
                'coin_package_id' => $request->coin_package_id,
                'payment_history_id' => $paymentId,
                'amount_paid' => $request->amount_paid,
                'purchase_date' => \Carbon\Carbon::now(),
                'coins_received' => $request->amount_paid * $package->coin_to_rupee_ratio,
                'payment_status' => "Success",
                'transuction_id' => $request->transuction_id,

                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('user_coin_purchase_history')->insert($insertedData);

            $user_details = DB::table('users')->where('id', $request->user_id)->first();

            $totalCoins = DB::table('user_coin_purchase_history')
                ->where('user_id', '=', $request->user_id)
                ->SUM('coins_received');

            $totalCoinsConsumed = DB::table('user_coin_consumption_history')
                ->where('user_id', '=', $request->user_id)
                ->SUM('coins_consumed');

            $remainingCoins = $totalCoins - $totalCoinsConsumed;

            if ($user_details) {
                $email = $user_details->email;
                $name =  $user_details->f_name;
                $data = array("email" => $email, "name" => $name, "purchase_date" => \Carbon\Carbon::now(), "coins_received" => $request->amount_paid * $package->coin_to_rupee_ratio, "amount_paid" => $request->amount_paid, "remainingCoins" => $remainingCoins);
                // Send email
                Mail::send('email.coinPurchase', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Confirmation: Your Coin Purchase on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

                //==========
                $adminData = array(
                    "user_name" => $name,
                    "email" => $email,
                    "coins_received" => $request->amount_paid * $package->coin_to_rupee_ratio,
                    "amount_paid" => $request->amount_paid,
                    "purchase_date" => \Carbon\Carbon::now()
                );
                $adminEmail = env('ADMIN_MAIL');
                // Send email
                Mail::send('email.admin.coinPurchaseNotification', $adminData, function ($message) use ($adminEmail) {
                    $message->to($adminEmail) // Use the recipient's email
                        ->subject('Coin Purchase Alert on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Coins added successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Coins added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    public function coinPurchaseHistory(Request $request, $id): JsonResponse
    {
        try {
            $user_id = $id;
            
            $authuser_id = Auth::user()->id;
            if ($user_id != $authuser_id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }
            
            $data = DB::table('user_coin_purchase_history as ucph')
                ->select('ucph.id', 'cpp.title', 'ucph.amount_paid', 'ucph.coins_received', 'ucph.purchase_date')
                ->leftJoin('coin_packages_plans as cpp', 'cpp.id', '=', 'ucph.coin_package_id')
                ->where('user_id', $user_id)->get();

            return $this->sendSuccessResponse('Coin purchase history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('About us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    public function deleteCoinHistory(Request $request, $id): JsonResponse
    {
        try {
            $user_id = Auth::user()->id;
            $coin_id = $id;
            if (!$user_id) {
                return $this->sendErrorResponse('Authentication error!', '');
            }

            $data = DB::table('user_coin_purchase_history')
                ->where('user_id', $user_id)
                ->where('id', $coin_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Record not found', '');
            }

            DB::table('user_coin_purchase_history')
                ->where('id', $coin_id)
                ->delete();

            return $this->sendSuccessResponse('Coin history deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Coin delete  error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    public function coinConsumed(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'student_name' => 'required',
                'skill_name' => 'required',
                'enquiry_date' => 'required',
                'coins_consumed' => 'required',
                'user_course_student_lead_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors());
            }


            $totalCoins = DB::table('user_coin_purchase_history')
                ->where('user_id', '=', $request->user_id)
                ->SUM('coins_received');

            $totalCoinsConsumed = DB::table('user_coin_consumption_history')
                ->where('user_id', '=', $request->user_id)
                ->SUM('coins_consumed');

            $remainingCoins = $totalCoins - $totalCoinsConsumed;

            if ($totalCoins < 1 && $remainingCoins < $request->coins_consumed) {
                return $this->sendErrorResponse('You do not have sufficient coin to complete this operation.', '', 402);
            };

            $insertedData = [
                'user_id' => $request->user_id,
                'student_name' => $request->student_name,
                'skill_name' => $request->skill_name,
                'enquiry_date' => $request->enquiry_date,
                'coins_consumed' => $request->coins_consumed,
                'coin_consumed_date' => \Carbon\Carbon::now(),

                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('user_coin_consumption_history')->insert($insertedData);


            $insertedData = [
                'user_id' => $request->user_id,
                'user_course_student_lead_id' => $request->user_course_student_lead_id,
                'used_coins' => $request->coins_consumed,
                'unlock_status' => '1',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('potential_student_unlock_log')->insert($insertedData);

            $user_details = DB::table('users')->where('id', $request->user_id)->first();
            $student_details = DB::table('user_course_student_lead')->where('id', $request->user_course_student_lead_id)->first();


            if ($user_details && $student_details) {
                $email = $user_details->email;
                $name =  $user_details->f_name;
                $data = array("email" => $email, "name" => $name, "student_name" => $student_details->student_name, "student_phone" => $student_details->student_phone, "student_email" => $student_details->student_email, "used_coins" => $request->coins_consumed, "remaining_coin_balance" => $remainingCoins, "date" => \Carbon\Carbon::now());
                // Send email
                Mail::send('email.coinConsumed', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Notification: Coins Spent on Viewing a Lead');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });

                //==========
                $adminEmail = env('ADMIN_MAIL');
                // Send email
                Mail::send('email.admin.coinConsumptionNotification', $data, function ($message) use ($adminEmail) {
                    $message->to($adminEmail) // Use the recipient's email
                        ->subject('Coin Consumption Alert on FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Coins consumption record added successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Coins consumption record added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }

    public function coinConsumedHistory(Request $request, $id): JsonResponse
    {
        try {
            $user_id = Auth::user()->id;
            if ($user_id != $id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }

            $data = DB::table('user_coin_consumption_history as ucch')
                ->select('users.f_name', 'ucch.id',  'ucch.enquiry_date', 'ucch.coin_consumed_date', 'ucch.coins_consumed',
                'ucsl.student_name',
                'ucsl.student_email',
                'ucsl.student_phone',
                'ucsl.student_message',
                    DB::raw('GROUP_CONCAT(DISTINCT skills.name) as skills'))
                ->leftJoin('users', 'ucch.user_id', '=', 'users.id')
                ->leftJoin('user_course_student_lead as ucsl', 'ucch.user_course_student_lead_id', '=', 'ucsl.id')
                ->leftJoin('courses_skills', 'ucsl.course_id', '=', 'courses_skills.course_id')
                ->leftJoin('skills', 'skills.id', '=', 'courses_skills.skill_name')
                ->groupBy(
                    'users.f_name',
                    'ucch.id',
                'ucch.enquiry_date',
                'ucch.coin_consumed_date',
                'ucch.coins_consumed',
                'ucsl.student_name',
                'ucsl.student_email',
                'ucsl.student_phone',
                'ucsl.student_message'
                )
                ->orderBy('ucch.created_at', 'desc')
                ->where('ucch.user_id', $user_id)->get();

            return $this->sendSuccessResponse('Coin consumed history fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('About us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage());
        }
    }
}
