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


class AdminMasterDataController extends BaseController
{
    

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

    //==================== Country Start ==========================
    public function countryListing(Request $request): JsonResponse
    {
        try {

            $countries = DB::table('country')
                ->select('id', 'name', 'calling_code')
                ->orderBy('id', 'desc')
                ->get();

            if (!$countries) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All countries fetched successfully.', $countries);
        } catch (\Throwable $th) {
            Log::error('All countries fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function countryDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'calling_code' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('country')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name)
                        ->orWhere('calling_code', $request->calling_code);
                })
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The name or code is already in use.', '', 409);
            }

            $updatedData = [
                'name' => $request->name,
                'calling_code' => $request->calling_code,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('country')->insert($updatedData);

            return $this->sendSuccessResponse('Country details inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Country details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function countryUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $country = DB::table('country')
                ->select('id', 'name', 'calling_code')
                ->where('id', $id)
                ->first();

            if (!$country) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Country updated data fetched successfully.', $country);
        } catch (\Throwable $th) {
            Log::error('Country updated data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function countryDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'calling_code' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $country = DB::table('country')
                ->select('id', 'name', 'calling_code')
                ->where('id', $id)
                ->first();

            if (!$country) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('country')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name)
                        ->orWhere('calling_code', $request->calling_code);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The name or code is already in use.', '', 409);
            }

            $updatedData = [
                'name' => $request->name,
                'calling_code' => $request->calling_code,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('country')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Country details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Country details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteCountry(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('country')
                ->select('id')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('country')->delete($id);

            return $this->sendSuccessResponse('Country details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Country detailsdeleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //==================== Country end ==========================

    //==================== State start ==========================

    public function stateDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'state' => 'required',
                'country_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('states')
                ->where('state', $request->state)
                ->where('country_id', $request->country_id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The state is already in use.', '', 409);
            }

            $insertedData = [
                'state' => $request->state,
                'country_id' => $request->country_id
            ];

            $storeInfo = DB::table('states')->insert($insertedData);

            return $this->sendSuccessResponse('State details inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('State details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function stateListing(Request $request): JsonResponse
    {
        try {

            $country_id = $request->input('country_id');
            $query = DB::table('states')
                ->select('states.id', 'states.state', 'country.name as country')
                ->leftJoin('country', 'country.id', '=', 'states.country_id')
                ->orderBy('id', 'desc');
            if (!is_null($country_id)) {
                $query->where('country_id', '=', $country_id);
            }
            $states =  $query->get();

            if (!$states) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All states fetched successfully.', $states);
        } catch (\Throwable $th) {
            Log::error('All states fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function stateUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $state = DB::table('states')
                ->select('states.id', 'states.state', 'country.name as country')
                ->leftJoin('country', 'country.id', '=', 'states.country_id')
                ->where('states.id', $id)
                ->first();

            if (!$state) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $countries = DB::table('country')
                ->select('id', 'name', 'calling_code')
                ->get();

            $state->countries = $countries;
            return $this->sendSuccessResponse('States updated data fetched successfully.', $state);
        } catch (\Throwable $th) {
            Log::error('States updated data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function stateDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'state' => 'required',
                'country_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $state = DB::table('states')
                ->where('id', $id)
                ->exists();

            if (!$state) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('states')
                ->where(function ($query) use ($request) {
                    $query->where('state', $request->state)
                        ->Where('country_id', $request->country_id);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The state is already in use.', '', 409);
            }

            $updatedData = [
                'state' => $request->state,
                'country_id' => $request->country_id,
            ];

            $storeInfo = DB::table('states')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('State details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('State details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteState(Request $request, $id): JsonResponse
    {
        try {

            $state = DB::table('states')
                ->where('id', $id)
                ->exists();

            if (!$state) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('states')->delete($id);

            return $this->sendSuccessResponse('State details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('State detailsdeleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //==================== State end ==========================

    //==================== City start =========================

    public function cityDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'country_id' => 'required',
                'state_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('cities')
                ->where('name', $request->name)
                ->where('country_id', $request->country_id)
                ->where('state_id', $request->state_id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The city is already in use.', '', 409);
            }

            $insertedData = [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('cities')->insert($insertedData);

            return $this->sendSuccessResponse('City details inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('City details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function cityListing(Request $request): JsonResponse
    {
        try {

            $state_id = $request->input('state_id');
            $query = DB::table('cities')
                ->select('cities.id', 'cities.name', 'states.state', 'country.name as country')
                ->leftJoin('country', 'country.id', '=', 'cities.country_id')
                ->leftJoin('states', 'states.id', '=', 'cities.state_id')
                ->orderBy('id', 'desc');
            if (!is_null($state_id)) {
                $query->where('state_id', '=', $state_id);
            }
            $cities =  $query->get();

            if (!$cities) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All cities fetched successfully.', $cities);
        } catch (\Throwable $th) {
            Log::error('All cities fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function cityUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $country = DB::table('cities')
                ->select('cities.id', 'cities.name', 'states.state', 'country.name as country')
                ->leftJoin('country', 'country.id', '=', 'cities.country_id')
                ->leftJoin('states', 'states.id', '=', 'cities.state_id')
                ->where('cities.id', $id)
                ->first();

            if (!$country) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Cities updated data fetched successfully.', $country);
        } catch (\Throwable $th) {
            Log::error('Cities updated data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function cityDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'country_id' => 'required',
                'state_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $city = DB::table('cities')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('cities')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name)
                        ->Where('country_id', $request->country_id)
                        ->Where('state_id', $request->state_id);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The City is already in use.', '', 409);
            }

            $updatedData = [
                'name' => $request->name,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
            ];

            $storeInfo = DB::table('cities')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('City details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('City details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteCity(Request $request, $id): JsonResponse
    {
        try {

            $city = DB::table('cities')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('cities')->delete($id);

            return $this->sendSuccessResponse('City details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('City details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    //==================== City end =========================

    //=================== Area start =======================

    public function areaDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'city_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('areas')
                ->where('name', $request->name)
                ->where('city_id', $request->city_id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The area is already in use.', '', 409);
            }

            $insertedData = [
                'name' => $request->name,
                'city_id' => $request->city_id,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('areas')->insert($insertedData);

            return $this->sendSuccessResponse('Area details inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Area details inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function areaListing(Request $request): JsonResponse
    {
        try {

            $states = DB::table('areas')
                ->select('areas.id', 'areas.name', 'cities.name as city')
                ->leftJoin('cities', 'cities.id', '=', 'areas.city_id')
                ->orderBy('id', 'desc')
                ->get();

            if (!$states) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All areas fetched successfully.', $states);
        } catch (\Throwable $th) {
            Log::error('All areas fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function areaUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $country = DB::table('areas')
                ->select('areas.id', 'areas.name', 'cities.name as city')
                ->leftJoin('cities', 'cities.id', '=', 'areas.city_id')
                ->where('areas.id', $id)
                ->first();

            if (!$country) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('Areas updated data fetched successfully.', $country);
        } catch (\Throwable $th) {
            Log::error('Areas updated data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function areaDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'city_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $city = DB::table('areas')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('areas')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name)
                        ->Where('city_id', $request->city_id);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The City is already in use.', '', 409);
            }

            $updatedData = [
                'name' => $request->name,
                'city_id' => $request->city_id,
            ];

            $storeInfo = DB::table('areas')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Area details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Area details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteArea(Request $request, $id): JsonResponse
    {
        try {

            $city = DB::table('areas')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('areas')->delete($id);

            return $this->sendSuccessResponse('Area details deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Area details deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=================== Area end =======================


    //=================== Qualification Start =======================
    public function qualificationDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('qualifications')
                ->where('name', $request->name)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The Qualification is already in use.', '', 409);
            }

            $insertedData = [
                'name' => $request->name,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('qualifications')->insert($insertedData);

            return $this->sendSuccessResponse('Qualification inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Qualification inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function qualificationListing(Request $request): JsonResponse
    {
        try {

            $data = DB::table('qualifications')
                ->select('id', 'name')
                ->orderBy('id', 'desc')
                ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All Qualifications fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('All qualifications fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function qualificationUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('qualifications')
                ->select('id', 'name')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }
            return $this->sendSuccessResponse('Qualification data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Qualification data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function qualificationDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $city = DB::table('qualifications')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('qualifications')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The Qualification is already in use.', '', 409);
            }

            $updatedData = [
                'name' => $request->name,
            ];

            $storeInfo = DB::table('qualifications')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Qualification details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Qualification details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteQualification(Request $request, $id): JsonResponse
    {
        try {

            $city = DB::table('qualifications')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('qualifications')->delete($id);

            return $this->sendSuccessResponse('Qualification deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Qualification deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=================== Qualification end =======================

    //=================== Currency Start =======================
    public function currencyDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required',
                'symbol' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('currency')
                ->where('code', $request->code)
                ->where('symbol', $request->symbol)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The Currency is already in use.', '', 409);
            }

            $insertedData = [
                'code' => $request->code,
                'symbol' => $request->symbol,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('currency')->insert($insertedData);

            return $this->sendSuccessResponse('Currency inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Currency inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function currencyListing(Request $request): JsonResponse
    {
        try {

            $data = DB::table('currency')
                ->select('id', 'code', 'symbol')
                ->orderBy('id', 'desc')
                ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All Currency fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('All Currency fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function currencyUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('currency')
                ->select('id', 'code', 'symbol')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }
            return $this->sendSuccessResponse('Currency data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Currency data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function currencyDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'code' => 'required',
                'symbol' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('currency')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('currency')
                ->where(function ($query) use ($request) {
                    $query->where('code', $request->code);
                    $query->orWhere('symbol', $request->symbol);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The currency is already in use.', '', 409);
            }

            $updatedData = [
                'code' => $request->code,
                'symbol' => $request->symbol,
            ];

            $storeInfo = DB::table('currency')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Currency details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Currency details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteCurrency(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('currency')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('currency')->delete($id);

            return $this->sendSuccessResponse('Currency deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Currency deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=================== Currency End =======================


    //=================== Language Start =======================
    public function languageDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('languages')
                ->where('name', $request->name)
                ->exists();

            if ($duplicate) {
                return $this->sendErrorResponse('The language is already in use.', '', 409);
            }

            $insertedData = [
                'name' => $request->name,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('languages')->insert($insertedData);

            return $this->sendSuccessResponse('Language inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Language inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function languageListing(Request $request): JsonResponse
    {
        try {

            $data = DB::table('languages')
            ->select('id', 'name')
            ->orderBy('id', 'desc')
            ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All languages fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('All languages fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function languageUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('languages')
                ->select('id', 'name')
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }
            return $this->sendSuccessResponse('Languages data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Languages data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function languageDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('languages')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('languages')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The languages is already in use.', '', 409);
            }

            $updatedData = [
                'name' => $request->name,
            ];

            $storeInfo = DB::table('languages')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Languages details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Languages details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteLanguage(Request $request, $id): JsonResponse
    {
        try {

            $city = DB::table('languages')
                ->where('id', $id)
                ->exists();

            if (!$city) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('languages')->delete($id);

            return $this->sendSuccessResponse('Language deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Language deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=================== Language End =======================


    //=================== Category Start =======================
    public function categoryDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'category_logo' => 'required',
                'is_top_category' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('categories')
                ->where('name', $request->name)
                ->exists();

            if ($duplicate) {
                return $this->sendErrorResponse('The category is already in use.', '', 409);
            }

            $path = public_path('uploads/categories');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = null;
            if ($request->hasFile('category_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('category_logo')->getClientOriginalName();
                $request->category_logo->move($path, $fileName);
                $filePath = "uploads/categories/" . $fileName;
            }

            $insertedData = [
                'name' => $request->name,
                'category_logo' => $filePath,
                'is_top_category' => $request->is_top_category,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('categories')->insert($insertedData);

            return $this->sendSuccessResponse('Category inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Category inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function categoryListing(Request $request): JsonResponse
    {
        try {

            $data = DB::table('categories')
            ->select('id', 'name', 'category_logo', 'is_top_category', 'status')
            ->orderBy('id', 'desc')
            ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All categories fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('All categories fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function categoryUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {

            $data = DB::table('categories')
                ->select('id', 'name', 'category_logo', 'is_top_category', 'status',)
                ->where('id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }
            return $this->sendSuccessResponse('Category data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Category data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function categoryDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            //return $this->sendSuccessResponse('Languages details updated successfully.', $request->all());
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('categories')
                ->select('id', 'name', 'category_logo', 'is_top_category', 'status',)
                ->where('id', $id)
                ->first();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('categories')
                ->where(function ($query) use ($request) {
                    $query->where('name', $request->name);
                })
                ->where('id', '!=', $id)
                ->exists();


            if ($duplicate) {
                return $this->sendErrorResponse('The category is already in use.', '', 409);
            }

            $path = public_path('uploads/categories');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = $checkData->category_logo;
            if ($request->hasFile('category_logo')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('category_logo')->getClientOriginalName();
                $request->category_logo->move($path, $fileName);
                $filePath = "uploads/categories/" . $fileName;

                if ($checkData->category_logo) {
                    $this->delete_file($checkData->category_logo);
                }
            }

            $updatedData = [
                'name' => $request->name,
                'category_logo' => $filePath,
                'is_top_category' => $request->is_top_category,
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('categories')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Category details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Category details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteCategory(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('categories')
                ->select('id', 'name', 'category_logo', 'is_top_category', 'status',)
                ->where('id', $id)
                ->first();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            if ($checkData->category_logo) {
                $this->delete_file($checkData->category_logo);
            }

            DB::table('categories')->delete($id);

            return $this->sendSuccessResponse('Category deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Category deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //=================== Category End =======================

    //================== Skill Satrt =======================

    public function skillDetailsInsert(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $duplicate = DB::table('skills')
                ->where('name', $request->name)
                ->where('category_id', $request->category_id)
                ->exists();

            if ($duplicate) {
                return $this->sendErrorResponse('The skill is already in use.', '', 409);
            }

            $insertedData = [
                'category_id' => $request->category_id,
                'name' => $request->name,
                "created_at" => \Carbon\Carbon::now(),
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('skills')->insert($insertedData);

            return $this->sendSuccessResponse('Skill inserted successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Skill inserted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function skillListing(Request $request): JsonResponse
    {
        try {

            $data = DB::table('skills')
                ->select('skills.id', 'skills.name', 'skills.skill', 'categories.name as category_name')
                ->leftJoin('categories', 'categories.id', '=', 'skills.category_id')
                ->orderBy('id', 'desc')
                ->get();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            return $this->sendSuccessResponse('All skills fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('All skills fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function skillUpdatedDataFetch(Request $request, $id): JsonResponse
    {
        try {
            $data = DB::table('skills')
                ->select('skills.id', 'skills.name', 'skills.skill', 'categories.name as category_name')
                ->leftJoin('categories', 'categories.id', '=', 'skills.category_id')
                ->where('skills.id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $categories = DB::table('categories')
                ->select('id', 'name')
                ->get();

            $data->categories = $categories;
            return $this->sendSuccessResponse('Skill data fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Skill data fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function skillDetailsUpdate(Request $request, $id): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required',
                'name' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $checkData = DB::table('skills')
                ->select('id')
                ->where('id', $id)
                ->first();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $duplicate = DB::table('skills')
                ->where(function ($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                    $query->where('name', $request->name);
                })
                ->where('id', '!=', $id)
                ->exists();

            if ($duplicate) {
                return $this->sendErrorResponse('The skill is already in use.', '', 409);
            }

            $updatedData = [
                'category_id' => $request->category_id,
                'name' => $request->name,
                "updated_at" => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('skills')->where('id', $id)->update($updatedData);

            return $this->sendSuccessResponse('Skill details updated successfully.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Skill details updated fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function deleteSkill(Request $request, $id): JsonResponse
    {
        try {

            $checkData = DB::table('skills')
                ->where('id', $id)
                ->exists();

            if (!$checkData) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            DB::table('skills')->delete($id);

            return $this->sendSuccessResponse('Skill deleted successfully.', '');
        } catch (\Throwable $th) {
            Log::error('Skill deleted error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    //================== Skill End =======================
}
