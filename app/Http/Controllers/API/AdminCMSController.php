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


class AdminCMSController extends BaseController
{

    //====== Review Management start ================
    public function contactPageDetails(Request $request): JsonResponse
    {
        try {

            $data = DB::table('contact_us_page')
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Contact page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Contact page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateContactUs(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'title' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('contact_us_page')
            ->select('id', 'image')
            ->where('id', $request->id)
            ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            $path = public_path('uploads/CMS');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = $data->image;
            if ($request->hasFile('image')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('image')->getClientOriginalName();
                $request->image->move($path, $fileName);
                $filePath = "uploads/CMS/" . $fileName;

                if ($data->profile_pic) {
                    $this->delete_file($data->profile_pic);
                }
            }

            $updatedData = [
                'title' => $request->title,
                'subtitle_1' => $request->subtitle_1,
                'subtitle_2' => $request->subtitle_2,
                'subtitle_3' => $request->subtitle_3,
                'phone' => $request->phone,
                'email' => $request->email,
                'address_1' => $request->address_1,
                'address_2' => $request->address_2,
                'facebook_url' => $request->facebook_url,
                'insta_url' => $request->insta_url,
                'twitter_url' => $request->twitter_url,
                'social_url_1' => $request->social_url_1,
                'social_url_2' => $request->social_url_2,
                'image' => $filePath,

                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('contact_us_page')->where('id', $request->id)->update($updatedData);

            return $this->sendSuccessResponse('Contact details updated successfully.', $storeInfo);

        } catch (\Throwable $th) {
            Log::error('Contact details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteReview(Request $request, $review_id): JsonResponse
    {
        try {

            $review_id = $review_id; // Optional filter by rating

            if (!$review_id) {
                return $this->sendErrorResponse('Validation Error.', "Review id is required", 403);
            }

            $data = DB::table('student_course_reviews')
                ->select('id')
                ->where('id', $review_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            DB::table('student_course_reviews')->delete($review_id);

            return $this->sendSuccessResponse('User review deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User review deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    //====== Review Management end ================
    public function updateSettings(Request $request): JsonResponse
    {
        try {
            //code...
        } catch (\Throwable $th) {
            Log::error('User review deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }


}
