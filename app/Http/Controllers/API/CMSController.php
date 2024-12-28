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


class CMSController extends BaseController
{
    public function contuctUsSendMail(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
                'message' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = array("name" => $request->name, "email" => $request->email, "phone" => "$request->phone", "msg" => $request->message);
            // return $this->sendSuccessResponse('Thank you for contacting us.', $data);
            // Send email
            Mail::send('email.emailTemplate', $data, function ($message) {
                $message->to('pranab@scwebtech.com') // Use the recipient's email
                    ->subject('Contact Us Form Submission');
                $message->from('pranab@scwebtech.com');
            });

            return $this->sendSuccessResponse('Thank you for contacting us.', '');
        } catch (\Throwable $th) {
            Log::error('Contuct us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function aboutUs(Request $request): JsonResponse
    {
        try {

            $aboutUs = DB::table('cms_content')->where('content_type', 'aboutUs')->first();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('About us content fetching successfully.', $aboutUs);
        } catch (\Throwable $th) {
            Log::error('About us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function investorConnect(Request $request): JsonResponse
    {
        try {

            $investorConnect = DB::table('cms_content')->where('content_type', 'investorConnect')->first();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Investor Connect content fetching successfully.', $investorConnect);
        } catch (\Throwable $th) {
            Log::error('Investor Connect error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    public function contactUs(Request $request): JsonResponse
    {
        try {

            $contactUs = DB::table('contact_us_page')->first();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Contact us content fetching successfully.', $contactUs);
        } catch (\Throwable $th) {
            Log::error('Contact us error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function privacyPolicy(Request $request): JsonResponse
    {
        try {

            $investorConnect = DB::table('cms_content')->where('content_type', 'privacyPolicy')->first();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Privacy Policy content fetching successfully.', $investorConnect);
        } catch (\Throwable $th) {
            Log::error('Privacy Policy error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function allFaqs(Request $request): JsonResponse
    {
        try {

            $type = $request->input('type');
            $query = DB::table('faqs');
            if (!is_null($type)) {
                $query->where('type', $type);
            }
            $data =  $query->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('FAQ content fetching successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('FAQ error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function subscriptions(Request $request): JsonResponse
    {
        try {

            $subscriptions = DB::table('subscription_plans')->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Subscription plans fetching successfully.', $subscriptions);
        } catch (\Throwable $th) {
            Log::error('Subscription plans fetching error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function coins(Request $request): JsonResponse
    {
        try {

            $coins = DB::table('coin_packages_plans')->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Coin plans fetching successfully.', $coins);
        } catch (\Throwable $th) {
            Log::error('Coin plans fetching error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function termsAndConditions(Request $request): JsonResponse
    {
        try {

            $termsAndConditions = DB::table('page_terms_and_conditions')->first();

            return $this->sendSuccessResponse('Terms And Conditions content fetching successfully.', $termsAndConditions);
        } catch (\Throwable $th) {
            Log::error('Terms And Conditions error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function listYourCourses(Request $request): JsonResponse
    {
        try {

            $listYourCourses = DB::table('page_list_your_courses')->first();

            return $this->sendSuccessResponse('List Your Courses content fetching successfully.', $listYourCourses);
        } catch (\Throwable $th) {
            Log::error('List Your Courses error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
