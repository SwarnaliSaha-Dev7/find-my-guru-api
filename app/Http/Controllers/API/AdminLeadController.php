<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Throwable;
use DB;
use Session;


class AdminLeadController extends BaseController
{
    //== Search enquiry
    public function searchEnquiryListing(Request $request): JsonResponse
    {
        try {

            $reviews = DB::table('search_enquiry')
                ->orderBy('created_at', 'desc')
                ->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Search enquiries fetched successfully.', $reviews);
        } catch (\Throwable $th) {
            Log::error('Search enquiries fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteSearchEnquiry(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('search_enquiry')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('search_enquiry')->delete($id);

            return $this->sendSuccessResponse('Search enquiry details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Search enquiry details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //=========== Webinar Lead Section ===================
    public function webinarListing(Request $request): JsonResponse
    {
        try {

            $reviews = DB::table('user_webinar_student_lead as uwsl')
                ->select(
                'uwsl.id', 'users.f_name', 'users.l_name', 'categories.name as category_name',
                'webinars.title as webinar_name',

                'uwsl.student_name',
                'uwsl.student_email',
                'uwsl.student_phone',
                'uwsl.student_message',
                'uwsl.tutor_action',
                'uwsl.tutor_notes',

                'uwsl.created_at',
                DB::raw('GROUP_CONCAT(user_skills.skill) as skills')

                )
                ->leftJoin('users', 'uwsl.user_id', '=', 'users.id')
                ->leftJoin('webinars', 'uwsl.webinar_id', '=', 'webinars.id')
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
                ->orderBy('uwsl.created_at', 'desc')
                ->groupBy(
                    'uwsl.id',
                    'users.f_name',
                    'users.l_name',
                    'categories.name',
                    'webinars.title',
                    'uwsl.student_name',
                    'uwsl.student_email',
                    'uwsl.student_phone',
                    'uwsl.student_message',
                    'uwsl.tutor_action',
                    'uwsl.tutor_notes',
                    'uwsl.created_at',
                )
                ->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Webinar leads data fetched successfully.', $reviews);
        } catch (\Throwable $th) {
            Log::error('Webinar leads data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //========== Course Lead section =========
    public function courseListing(Request $request): JsonResponse
    {
        try {

            $reviews = DB::table('user_course_student_lead as ucsl')
            ->select(
                'ucsl.id',
                'users.f_name',
                'users.l_name',
                'categories.name as category_name',
                'courses.course_name',
                'skills.name as skill_name',
                'ucsl.student_name',
                'ucsl.student_email',
                'ucsl.student_phone',
                'ucsl.student_message',
                'ucsl.tutor_action',
                'ucsl.tutor_notes',
                'ucsl.created_at',
                //DB::raw('GROUP_CONCAT(user_skills.skill) as skills')
            )
                ->leftJoin('users', 'ucsl.user_id', '=', 'users.id')
                ->leftJoin('courses', 'ucsl.course_id', '=', 'courses.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('skills', 'skills.id', '=', 'courses.skill_id')
                ->orderBy('ucsl.created_at', 'desc')
                // ->groupBy(
                //     'users.f_name',
                //     'users.l_name',
                //     'categories.name',
                //     'courses.course_name',
                //     'ucsl.created_at',
                // )
                ->get();

            //$studentLead = $query->first();

            return $this->sendSuccessResponse('Course leads data fetched successfully.', $reviews);
        } catch (\Throwable $th) {
            Log::error('Course leads data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function contactWithTrainer(Request $request): JsonResponse
    {
        //return $this->sendSuccessResponse('Trainer will contact you soon.', '');
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'course_id' => 'required',
                'category_id' => 'required',
                'student_name' => 'required',
                'student_email' => 'required|email',
                'student_phone' => 'required',

                'email' => 'required',
                'f_name' => 'required',
                'course_name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $insertedData = [
                    'user_id' => $request->user_id,
                    'course_id' => $request->course_id,
                    'category_id' => $request->category_id,
                    'student_name' => $request->student_name,
                    'student_email' => $request->student_email,
                    'student_phone' => $request->student_phone,
                    'student_message' => $request->student_message,
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now(),
                ];
            $storeInfo = DB::table('user_course_student_lead')->insert($insertedData);
            $email = $request->email;
            $data = array(
                "name" => $request->f_name,
                "email" => $email,
                "course_name" => $request->course_name,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone,
                "date" => \Carbon\Carbon::now()
            );
            // Send email
            Mail::send('email.leadGeneration', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                ->subject('New Student Lead for Your Course on FindMyGuru!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            //=== admin mail
            // $adminEmail = env('ADMIN_MAIL');
            // // Send email
            // Mail::send('email.admin.studentLeadGenerationNotification', $data, function ($message) use ($adminEmail) {
            //     $message->to($adminEmail) // Use the recipient's email
            //     ->subject('Student Lead Generation Alert on FindMyGuru');
            //     $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            // });

            //== Student mail

            $user_details = DB::table('users')->where('id', $request->user_id)->first();
            if ($user_details) {
                $studentData =
                    array(
                        "name" => $user_details->f_name . ($user_details->l_name ? ' ' . $user_details->l_name : ''),
                        "phone" => $user_details->phone,
                        "email" => $user_details->email,
                        "course_name" => $request->course_name,
                        'student_name' => $request->student_name,
                        "date" => \Carbon\Carbon::now()
                    );
                $studentEmail = $request->student_email;
                Mail::send('email.tutorContactDetails', $studentData, function ($message) use ($studentEmail) {
                    $message->to($studentEmail) // Use the recipient's email
                    ->subject('Your Tutor/Institute Contact Information from FindMyGuru');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
            }

            return $this->sendSuccessResponse('Trainer will contact you soon.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Contact with trainer error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
