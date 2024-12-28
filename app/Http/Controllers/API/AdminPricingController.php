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


class AdminPricingController extends BaseController
{

    //=============== Subscription start =========================
    public function subscriptionDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'duration_in_months' => 'required',
                'actual_price' => 'required',
                'offer_price' => 'required',
                'free_coins' => 'required',
                'is_course_listing' => 'required',
                'featured_listing' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('subscription_plans')
                ->where(function ($query) use ($request) {
                    $query->where('title', $request->title);
                })
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The feature is already in use.', '', 409);
            }

            $insertedData = [
                'title' => $request->title,
                'duration_in_months' => $request->duration_in_months,
                'actual_price' => $request->actual_price,
                'offer_price' => $request->offer_price,
                'free_coins' => $request->free_coins,
                'is_course_listing' => $request->is_course_listing,
                'featured_listing' => $request->featured_listing,

                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('subscription_plans')->insert($insertedData);

            return $this->sendSuccessResponse('Subscription plans details inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Subscription plans details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function subscriptionListing(Request $request): JsonResponse
    {
        try {

            $checkData = DB::table('subscription_plans')
                ->get();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Subscription plans fetched successfully.', $checkData);
        } catch (\Throwable $th) {
            Log::error('Subscription plans fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function subscriptionUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('subscription_plans')
                ->where('id', $id)
                ->first();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Subscription data fetched successfully.', $checkData);
        } catch (\Throwable $th) {
            Log::error('Subscription data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function subscriptionDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'duration_in_months' => 'required',
                'actual_price' => 'required',
                'offer_price' => 'required',
                'free_coins' => 'required',
                'is_course_listing' => 'required',
                'featured_listing' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('subscription_plans')
                ->where('id', $id)->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('subscription_plans')
                ->where(function ($query) use ($request) {
                    $query->where('title', $request->title);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The Subscription title is already in use.', '', 409);
            }

            $updatedData = [
                'title' => $request->title,
                'duration_in_months' => $request->duration_in_months,
                'actual_price' => $request->actual_price,
                'offer_price' => $request->offer_price,
                'free_coins' => $request->free_coins,
                'is_course_listing' => $request->is_course_listing,
                'featured_listing' => $request->featured_listing,
            ];

            $storeInfo = DB::table('subscription_plans')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Subscription details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Subscription details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteSubscription(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('subscription_plans')
                ->where('id', $id)->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('subscription_plans')->delete($id);

            return $this->sendSuccessResponse('Subscription plan details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Subscription plan details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=============== Subscription end =========================


    //=============== Coin start =========================
    public function coinDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(),
                [
                    'title' => 'required',
                    'min_amount' => 'required',
                    'max_amount' => 'required',
                    'coin_to_rupee_ratio' => 'required',
                    'expiry_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('coin_packages_plans')
                ->where(function ($query) use ($request) {
                    $query->where('title', $request->title);
                })
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The feature is already in use.', '', 409);
            }

            $insertedData = [
                'title' => $request->title,
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'coin_to_rupee_ratio' => $request->coin_to_rupee_ratio,
                'description' => $request->description,
                'expiry_date' => $request->expiry_date,
                'bonus_coins' => $request->bonus_coins ?? 0,

                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('coin_packages_plans')->insert($insertedData);

            return $this->sendSuccessResponse('Coin plans details inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Coin plans details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function coinListing(Request $request): JsonResponse
    {
        try {

            $checkData = DB::table('coin_packages_plans')
                ->get();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Coins plan fetched successfully.', $checkData);
        } catch (\Throwable $th) {
            Log::error('Coins plan fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function coinUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('coin_packages_plans')
                ->where('id', $id)
                ->first();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Coin`s plan data fetched successfully.', $checkData);
        } catch (\Throwable $th) {
            Log::error('Coin`n data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function coinDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(),
                [
                    'title' => 'required',
                    'min_amount' => 'required',
                    'max_amount' => 'required',
                    'coin_to_rupee_ratio' => 'required',
                    'expiry_date' => 'required',
                ]
            );

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('coin_packages_plans')
                ->where('id', $id)->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('coin_packages_plans')
                ->where(function ($query) use ($request) {
                    $query->where('title', $request->title);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The Subscription title is already in use.', '', 409);
            }

            $updatedData = [
                'title' => $request->title,
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'coin_to_rupee_ratio' => $request->coin_to_rupee_ratio,
                'expiry_date' => $request->expiry_date,
                'bonus_coins' => $request->bonus_coins ?? 0,
                'description' => $request->description,
                'updated_at' => \Carbon\Carbon::now(),

            ];

            $storeInfo = DB::table('coin_packages_plans')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Coin`s details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Coin`s details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function coinStatusUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make(
                $request->all(),
                [
                    'status' => 'required',
                ]
            );

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('coin_packages_plans')
                ->where('id', $id)->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $updatedData = [
                'status' => $request->status,
                'updated_at' => \Carbon\Carbon::now(),

            ];

            $storeInfo = DB::table('coin_packages_plans')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Coin`s status updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Coin`s status updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteCoin(Request $request, $id): JsonResponse {
        try {

            $checkData = DB::table('coin_packages_plans')
                ->where('id', $id)->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('coin_packages_plans')->delete($id);

            return $this->sendSuccessResponse('Coin`s plan details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Coin`s plan details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=============== Coin end =========================
}
