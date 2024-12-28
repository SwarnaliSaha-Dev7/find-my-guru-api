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


class AdminContentPageController extends BaseController
{

    //====== Blog Management start ================

    //====== Listing
    public function blogListing(Request $request): JsonResponse
    {
        try {

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $data = DB::table('blogs')
                ->select('blogs.id', 'blogs.title', 'blogs.picture', 'categories.name as category_name',)
                ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
                ->orderBy('blogs.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Blog`s details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('User reviews fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Add
    public function blogDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'title' => 'required',
                'picture' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            // Save new file
            $path = public_path('uploads/blog');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $fileName = null;
            $filePath = null;
            if ($request->hasFile('picture')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('picture')->getClientOriginalName();
                $request->picture->move($path, $fileName);
                $filePath = "uploads/blog/" . $fileName;
            }

            $insertedData = [
                'category_id' => $request->category_id,
                'title' => $request->title,
                'picture' => $filePath,
                'short_content' => $request->short_content,
                'full_content' => $request->full_content,
                'mete_title' => $request->mete_title,
                'meta_tag' => $request->meta_tag,
                'meta_description' => $request->meta_description,
                'seo1' => $request->seo1,
                'seo2' => $request->seo2,
                'seo3' => $request->seo3,
                'is_trending' => $request->is_trending ?? 0,
                'status' => $request->status,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            $storeInfo = DB::table('blogs')->insert($insertedData);

            return $this->sendSuccessResponse('Blog details added successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Blog details added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Fetch details
    public function blogUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('blogs')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $categories = DB::table('categories')
                ->select('id', 'name')
                ->get();

            $data->categories = $categories;

            return $this->sendSuccessResponse('Blog`s data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Blog`s data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //===== Delete file
    public function delete_file($path)
    {
        //$file_path = public_path('path/to/your/file.txt');
        $file_path = public_path($path);
        if (File::exists($file_path)) {
            File::delete($file_path);
            echo 'File deleted successfully.';
        } else {
            echo 'File does not exist.';
        }
    }

    //===== Blog Details Update
    public function blogDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required|integer',
                'title' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('blogs')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            // Save new file
            $path = public_path('uploads/blog');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('picture')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('picture')->getClientOriginalName();
                $request->picture->move($path, $fileName);
                $filePath = "uploads/blog/" . $fileName;

                //$updateddData = ['picture' => $filePath];

                if ($data->picture) {
                    $this->delete_file($data->picture);
                }
            } else {
                $filePath = $data->picture;
            }

            $updateddData = [
                'category_id' => $request->category_id,
                'title' => $request->title,
                'picture' => $filePath,
                'short_content' => $request->short_content,
                'full_content' => $request->full_content,
                'mete_title' => $request->mete_title,
                'meta_tag' => $request->meta_tag,
                'meta_description' => $request->meta_description,
                'seo1' => $request->seo1,
                'seo2' => $request->seo2,
                'seo3' => $request->seo3,
                'is_trending' => $request->is_trending ?? 0,
                'status' => $request->status,

                'updated_at' => \Carbon\Carbon::now(),
            ];
            $storeInfo = DB::table('blogs')->where('id', $id)->update($updateddData);

            return $this->sendSuccessResponse('Blog details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Blog details updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //===== Delete
    public function deleteBlog(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('blogs')
                ->select('id', 'picture')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            if ($data->picture) {
                $this->delete_file($data->picture);
            }

            DB::table('blogs')->delete($id);

            return $this->sendSuccessResponse('Blog`s details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Blog`s details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Blog Management end ================

    //====== Webinar management start ==============

    //====== Listing
    public function webinarListing(Request $request): JsonResponse
    {
        try {

            $pageNumber = request()->input('page', 1); // Get 'page' parameter from the request, default to 1
            $perPage = 15;
            $data = DB::table('webinars')
                ->select('users.f_name', 'users.l_name', 'webinars.id', 'webinars.title', 'webinars.logo', 'categories.name as category_name',)
                ->leftJoin('users', 'webinars.user_id', '=', 'users.id')
                ->leftJoin('categories', 'webinars.category_id', '=', 'categories.id')
                ->orderBy('webinars.created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $pageNumber);

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Webinar`s details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Webinar`s fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Add
    public function webinarDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [

                'user_id' => 'required|integer',
                'category_id' => 'required|integer',
                'title' => 'required',
                'language' => 'required',
                'start_date' => 'required',
                'start_time' => 'required',
                'fee' => 'required',
                'no_of_seats' => 'required',
                'address' => 'required',
                'agenda' => 'required',
                'content' => 'required',
                'currency_id' => 'required',
                'logo' => 'required',

            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            // Save new file
            $path = public_path('uploads/webinar');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            $fileName = null;
            $filePath = null;
            if ($request->hasFile('logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('logo')->getClientOriginalName();
                $request->logo->move($path, $fileName);
                $filePath = "uploads/webinar/" . $fileName;
            }

            $insertedData = [
                'user_id' => $request->user_id,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'language' => $request->language,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'fee' => $request->fee,
                'currency_id' => $request->currency_id,
                'no_of_seats' => $request->no_of_seats,
                'delivery_mode' => $request->delivery_mode,
                'address' => $request->address,
                'agenda' => $request->agenda,
                'content' => $request->content,
                'logo' => $filePath,

                'demo_video_url' => $request->demo_video_url,
                'mete_title' => $request->mete_title,
                'meta_description' => $request->meta_description,
                'seo1' => $request->seo1,
                'seo2' => $request->seo2,
                'seo3' => $request->seo3,
                'status' => $request->status,

                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('webinars')->insert($insertedData);

            return $this->sendSuccessResponse('Blog details added successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Blog details added error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Fetch details
    public function webinarUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('webinars')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $categories = DB::table('categories')
                ->select('id', 'name')
                ->get();

            $currency = DB::table('currency')
                ->select('id', 'code', 'symbol')
                ->get();

            $data->categories = $categories;
            $data->currency = $currency;

            return $this->sendSuccessResponse('Webinar`s data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Webinar`s data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Update details
    public function webinarDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {
            //dd($webinar_id);

            $validator = Validator::make($request->all(), [
                //'webinar_id' => 'required|integer',
                'category_id' => 'required|integer',
                'title' => 'required',
                'language' => 'required',
                'start_date' => 'required',
                'start_time' => 'required',
                'fee' => 'required',
                'no_of_seats' => 'required',
                'address' => 'required',
                'agenda' => 'required',
                'content' => 'required',
                'currency_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('webinars')
                ->select(
                    'id',
                    'category_id',
                    'title',
                    'language',
                    'start_date',
                    'end_date',
                    'start_time',
                    'end_time',
                    'fee',
                    'currency_id',
                    'no_of_seats',
                    'delivery_mode',
                    'address',
                    'agenda',
                    'content',
                    'logo'
                )
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }
            // Save new file
            $path = public_path('uploads/webinar');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('logo')->getClientOriginalName();
                $request->logo->move($path, $fileName);
                $filePath = "uploads/webinar/" . $fileName;

                if ($data->logo) {
                    $this->delete_file($data->logo);
                }
            } else {
                $filePath = $data->logo;
            }

            $updatedData = [
                'category_id' => $request->category_id,
                'title' => $request->title,
                'language' => $request->language,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'fee' => $request->fee,
                'currency_id' => $request->currency_id,
                'no_of_seats' => $request->no_of_seats,
                'delivery_mode' => $request->delivery_mode,
                'address' => $request->address,
                'agenda' => $request->agenda,
                'content' => $request->content,
                'logo' => $filePath,

                'demo_video_url' => $request->demo_video_url,
                'mete_title' => $request->mete_title,
                'meta_description' => $request->meta_description,
                'seo1' => $request->seo1,
                'seo2' => $request->seo2,
                'seo3' => $request->seo3,
                'status' => $request->status,

                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('webinars')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse(
                'Webinar`s details updated successfully.',
                $storeInfo
            );
        } catch (\Throwable $th) {
            Log::error('Webinar`s details updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //====== Delete details
    public function deleteWebinar(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('webinars')
                ->select('id', 'logo')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            if ($data->logo) {
                $this->delete_file($data->logo);
            }

            DB::table('webinars')->delete($id);
            DB::table('user_webinar_student_lead')->where('webinar_id', $id)->delete();

            return $this->sendSuccessResponse('Webinar`s details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Webinar`s details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Webinar management end =================

    //====== Contact us page Management start ================
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
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }


            $updatedData = [
                'title' => $request->title,
                'subtitle_1' => $request->subtitle_1,
                'subtitle_2' => $request->subtitle_2,
                'subtitle_3' => $request->subtitle_3,
                'phone' => $request->phone,
                'email' => $request->email,
                'phoneSupportTime' => $request->phoneSupportTime,
                'emailSupportTime' => $request->emailSupportTime,
                'facebook_url' => $request->facebook_url,
                'insta_url' => $request->insta_url,
                'twitter_url' => $request->twitter_url,
                'linkedin_url' => $request->linkedin_url,
                'youtube_url' => $request->youtube_url,

                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('contact_us_page')->where('id', $data->id)->update($updatedData);

            return $this->sendSuccessResponse('Contact details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Contact details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Contact us page Management end ================

    //====== Investor Connect page Management start ================
    public function investorConnectPageDetails(Request $request): JsonResponse
    {
        try {

            $data = DB::table('page_investor_connect')
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Investor connect page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Investor connect page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateInvestorConnectPage(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('page_investor_connect')
                ->select(
                    'id',
                    'banner_image'
                )
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            $path = public_path('uploads/CMS');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = $data->banner_image;
            if ($request->hasFile('banner_image')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('banner_image')->getClientOriginalName();
                $request->banner_image->move($path, $fileName);
                $filePath = "uploads/CMS/" . $fileName;

                if ($data->banner_image) {
                    $this->delete_file($data->banner_image);
                }
            }

            $updatedData = [
                'title' => $request->title,
                'subtitle_1' => $request->subtitle_1,
                'content' => $request->content,
                'button_link' => $request->button_link,
                'button_text' => $request->button_text,
                'banner_image' => $filePath,

                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('page_investor_connect')->where('id', $data->id)->update($updatedData);

            return $this->sendSuccessResponse('Contact details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Contact details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Investor Connect page Management end ================

    //====== Investor Connect page Management start ================
    public function privacyPolicyPageDetails(Request $request): JsonResponse
    {
        try {

            $data = DB::table('page_privacy_policy')
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Privacy policy page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Privacy policy page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateprivacyPolicyPage(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('page_privacy_policy')
                ->select(
                    'id',
                    'title'
                )
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }


            $updatedData = [
                'title' => $request->title,
                'subtitle_1' => $request->subtitle_1,
                'subtitle_2' => $request->subtitle_2,
                'subtitle_3' => $request->subtitle_3,
                'content_1' => $request->content_1,
                'content_2' => $request->content_2,
                'content_3' => $request->content_3,

                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('page_privacy_policy')->where('id', $data->id)->update($updatedData);

            return $this->sendSuccessResponse('Privacy policy details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Privacy policy details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Investor Connect page Management end ================

    //====== Global Variable Management start ================
    public function globalVariableDetails(Request $request): JsonResponse
    {
        try {

            $data = DB::table('settings')
                ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Global variables details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Global variables details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateGlobalVariable(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'header_logo' => 'required',
                'footer_logo' => 'required',
                'copy_right' => 'required',
                'quick_links' => 'required',
                'support' => 'required',
                'disclaimer' => 'required',
                'short_description' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $inputArray = ['header_logo', 'footer_logo', 'support', 'copy_right', 'quick_links', 'disclaimer', 'short_description'];

            $data = DB::table('settings')->whereIn('key', $inputArray)->get();

            $filePath_footer_logo = null;
            $filePath_header_logo = null;
            foreach ($data as $val) {

                if (trim($val->key) == 'header_logo') {
                    $filePath_header_logo = $val->value;
                }

                if (trim($val->key) == 'footer_logo') {
                    $filePath_footer_logo = $val->value;
                }
            }

            $path = public_path('uploads/CMS');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            if ($request->hasFile('header_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('header_logo')->getClientOriginalName();
                $request->header_logo->move($path, $fileName);

                if ($filePath_header_logo) {
                    $this->delete_file($filePath_header_logo);
                }

                $filePath_header_logo = "uploads/CMS/" . $fileName;
            }

            if ($request->hasFile('footer_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('footer_logo')->getClientOriginalName();
                $request->footer_logo->move($path, $fileName);

                if ($filePath_footer_logo) {
                    $this->delete_file($filePath_footer_logo);
                }

                $filePath_footer_logo = "uploads/CMS/" . $fileName;
            }

            $updates = [];
            foreach ($inputArray as $key) {
                if ($request->has($key)) {
                    $updates[] = [
                        'key' => $key,
                        'value' => $request->{$key}
                    ];
                }
            }

            foreach ($updates as $update) {

                if ($update['key'] == 'header_logo') {
                    DB::table('settings')
                        ->where('key', $update['key'])
                        ->update(['value' => $filePath_header_logo]);
                    continue;
                }

                if ($update['key'] == 'footer_logo') {
                    DB::table('settings')
                        ->where('key', $update['key'])
                        ->update(['value' => $filePath_footer_logo]);
                    continue;
                }

                DB::table('settings')
                    ->where('key', $update['key'])
                    ->update(['value' => $update['value']]);
            }

            return $this->sendSuccessResponse('Global variables details updated successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Global variables details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Global Variable Management end ================

    //====== Home Page Management start ================
    public function hpmePageDetails(Request $request): JsonResponse
    {
        try {

            $data = DB::table('page_home')
                ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Home page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Home page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateHomePage(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('page_home')
                ->select(
                    'id',
                    'banner_image',
                'banner_right_image',
                'banner_left_upper_image',
                'banner_left_lower_image'
                )
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            $path = public_path('uploads/CMS');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath_banner_image = $data->banner_image;
            if ($request->hasFile('banner_image')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('banner_image')->getClientOriginalName();
                $request->banner_image->move($path, $fileName);
                $filePath_banner_image = "uploads/CMS/" . $fileName;

                if ($data->banner_image) {
                    $this->delete_file($data->banner_image);
                }
            }
            $filePath_banner_right_image = $data->banner_right_image;
            if ($request->hasFile('banner_right_image')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('banner_right_image')->getClientOriginalName();
                $request->banner_right_image->move($path, $fileName);
                $filePath_banner_right_image = "uploads/CMS/" . $fileName;

                if ($data->banner_right_image) {
                    $this->delete_file($data->banner_right_image);
                }
            }
            $filePath_banner_left_upper_image = $data->banner_left_upper_image;
            if ($request->hasFile('banner_left_upper_image')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('banner_left_upper_image')->getClientOriginalName();
                $request->banner_left_upper_image->move($path, $fileName);
                $filePath_banner_left_upper_image = "uploads/CMS/" . $fileName;

                if ($data->banner_left_upper_image) {
                    $this->delete_file($data->banner_left_upper_image);
                }
            }
            $filePath_banner_left_lower_image = $data->banner_left_lower_image;
            if ($request->hasFile('banner_left_lower_image')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('banner_left_lower_image')->getClientOriginalName();
                $request->banner_left_lower_image->move($path, $fileName);
                $filePath_banner_left_lower_image = "uploads/CMS/" . $fileName;

                if ($data->banner_left_lower_image) {
                    $this->delete_file($data->banner_left_lower_image);
                }
            }

            $updatedData = [

                'title' => $request->title,
                'search_title' => $request->search_title,

                'banner_right_text' => $request->banner_right_text,
                'banner_left_upper_text' => $request->banner_left_upper_text,
                'banner_left_lower_text' => $request->banner_left_lower_text,

                'total_user' => $request->total_user,
                'total_course' => $request->total_course,
                'total_webinar' => $request->total_webinar,
                'happy_student' => $request->happy_student,


                'total_user_text' => $request->total_user_text,
                'total_course_text' => $request->total_course_text,
                'total_webinar_text' => $request->total_webinar_text,
                'happy_student_text' => $request->happy_student_text,

                'banner_image' => $filePath_banner_image,
                'banner_right_image' => $filePath_banner_right_image,
                'banner_left_upper_image' => $filePath_banner_left_upper_image,
                'banner_left_lower_image' => $filePath_banner_left_lower_image,

                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('page_home')->where('id', $data->id)->update($updatedData);

            return $this->sendSuccessResponse('Home page updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Home page details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Home Page Management end ============

    //====== FAQ Management start ================
    public function faqListing(Request $request): JsonResponse
    {
        try {

            $type = $request->input('type');
            $query = DB::table('faqs');
            if (!is_null($type)) {
                $query->where('type', '=', $type);
            }
            $data = $query->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('FAQ listing fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('FAQ listing fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function faqInsert(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'question' => 'required',
                'answer' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $insertData = [
                "type" => $request->type,
                "question" => $request->question,
                "answer" => $request->answer
            ];

            $storeInfo = DB::table('faqs')->insert($insertData);

            return $this->sendSuccessResponse('FAQ inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('FAQ inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function faqDetails(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('faqs')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('FAQ details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('FAQ details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function faqDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'question' => 'required',
                'answer' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('faqs')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $updatedData = [
                'type' => $request->type,
                'question' => $request->question,
                'answer' => $request->answer,
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('faqs')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('FAQ details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('FAQ details updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteFaq(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('faqs')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('faqs')->delete($id);

            return $this->sendSuccessResponse(
                'FAQ deleted successfully.',
                ''
            );
        } catch (\Throwable $th) {
            Log::error('FAQ deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== FAQ Management end ================
    
    
    //====== Terms And Conditions Page start ================
    public function termsAndConditionsPageDetails(Request $request): JsonResponse
    {
        try {
            $data = DB::table('page_terms_and_conditions')->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Terms And Conditions page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Terms And Conditions page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateTermsAndConditionsPage(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'content' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('page_terms_and_conditions')
                ->select(
                    'id',
                    // 'title'
                )
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }


            $updatedData = [
                'title' => $request->title,
                'content' => $request->content,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('page_terms_and_conditions')->where('id', $data->id)->update($updatedData);

            return $this->sendSuccessResponse('Terms And Conditions details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Terms And Conditions details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== Terms And Conditions Page end ================
    
    //====== List Your Courses Page start ================
    public function listYourCoursesPageDetails(Request $request): JsonResponse
    {
        try {
            $data = DB::table('page_list_your_courses')->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('List Your Courses page details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('List Your Courses page details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function listYourCoursesPage(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'content' => 'required',
                'url' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('page_list_your_courses')
                ->select(
                    'id',
                    // 'title'
                )
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }


            $updatedData = [
                'title' => $request->title,
                'content' => $request->content,
                'url' => $request->url,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('page_list_your_courses')->where('id', $data->id)->update($updatedData);

            return $this->sendSuccessResponse('List Your Courses details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('List Your Courses details update error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //====== List Your Courses Page end ================
}
