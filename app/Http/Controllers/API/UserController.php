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
use App\Http\Controllers\API\Exception;
use Throwable;
use DB;
use Session;
use DateTime;

class UserController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_type' => 'required',
                'f_name' => 'required',
                'email' => 'required|email|unique:users,email',
                'phone' => 'required|unique:users,phone',
                'password' => 'required',
                'email_otp' => 'required',
                'phone_otp' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

  
            $verify_otp = DB::table('verify_otp')->where('email', $request->email)->orderBy('id','DESC')->first();

            if (!$verify_otp) {
                return $this->sendErrorResponse('OTP details not found.', '', 404);
            }

            if (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($verify_otp->email_otp_expire))) {
                return $this->sendErrorResponse('OTP has expired.', '', 400);
            }

            if ($request->email_otp && $request->email_otp != $verify_otp->email_otp ) {
                return $this->sendErrorResponse('Email otp did not match.', '', 400);
            }

            if ($request->phone_otp && $request->phone_otp != $verify_otp->phone_otp) {
                return $this->sendErrorResponse('Phone otp did not match.', '', 400);
            }

            $input = $request->all();
            //$input = $request->except(['email', 'phone']);
            $input['password'] = bcrypt($input['password']);

            $user = User::create($input);
            DB::table('verify_otp')->where('email', $request->email)->delete();

            // $success['token'] =  $user->createToken($user->email)->plainTextToken;
            // $success['name'] =  $user->f_name;

            $name = $request->f_name;
            $email = $request->email;
            $data = array("email" => $email, "name" => $name);
            // Send email
            Mail::send('email.userRegister', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('Your FindMyGuru Profile is Under Review!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });
            
            $date = \Carbon\Carbon::now();
            $adminData = array("email" => env('ADMIN_MAIL'), "name" =>$name, "user_email" => $email, "phone" => $request->phone, "date" => $date);
            $adminEmail = env('ADMIN_MAIL');
            // Send email
            Mail::send('email.admin.registrationAlert', $adminData, function ($message) use ($adminEmail) {
                $message->to($adminEmail) // Use the recipient's email
                    ->subject('New Tutor/Institute Registration Alert on FindMyGuru');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });
            return $this->sendSuccessResponse('User register successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User register error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    function sendSms($templateId, $mobile, $variables = [])
    {
        $url = env('MSG91_BASE_URL');
        $authKey = env('SMS_MSG91_AUTH_KEY');

        $recipients = array_merge(['mobiles' => $mobile], $variables);

        $payload = [
            'template_id' => $templateId,
            'realTimeResponse' => "1",
            'recipients' => [$recipients],
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "authkey: $authKey",
                "content-type: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new Exception("CURL Error: " . $error);
        }

        return json_decode($response, true);
    }

    public function sendOTP(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                    'f_name' => 'required',
                    'email' => 'required|email|unique:users,email',
                    'phone' => 'required|unique:users,phone',
                ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $emailOTP = rand(1000, 9999);
            $phoneOTP = rand(1000, 9999);
            $expirationTime = \Carbon\Carbon::now()->addMinutes(5);
            $input = $request->all();
            $input['email_otp'] = $emailOTP;
            $input['phone_otp'] = $phoneOTP;
            $input['email_otp_expire'] = $expirationTime;
            $input['phone_otp_expire'] = $expirationTime;

            DB::table('verify_otp')->insert($input);


            $email = $request->email;
            $name = $request->f_name;

            $data = array("email" => $email, "otp" => $emailOTP, "name" => $name);

            // Send email
            Mail::send('email.sendOTP', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('Verify Your Email to Complete Your FindMyGuru Signup!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            if ($request->phone) {
                $templateId = "674f05a7d6fc054fe8452692";
                $mobile = "91". $request->phone;
                $variables = [
                    'number' => $phoneOTP,
                    //'VAR2' => 'VALUE 2',
                ];
                $response = $this->sendSms($templateId, $mobile, $variables);
            }

            return $this->sendSuccessResponse('OTP send successfully.', '');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function reSendOTP(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'otp_type'=> 'required',
                'f_name' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $emailOTP = rand(1000, 9999);
            $phoneOTP = rand(1000, 9999);
            $expirationTime = \Carbon\Carbon::now()->addMinutes(5);

            if($request->otp_type == 'email'){

                $verify_otp = DB::table('verify_otp')->where('email', $request->email)->orderBy('id', 'DESC')->first();

                if (!$verify_otp) {
                    return $this->sendErrorResponse('OTP details not found.', '', 404);
                }
                DB::table('verify_otp')->where('id', $verify_otp->id)->update([
                    'email_otp' => $emailOTP,
                    'email_otp_expire' => $expirationTime,
                    'phone_otp_expire' => $expirationTime,
                ]);
                $email = $request->email;
                $name = $request->f_name;
                $data = array("email" => $email, "otp" => $emailOTP, "name" => $name);
                // Send email
                Mail::send('email.sendOTP', $data, function ($message) use ($email) {
                    $message->to($email) // Use the recipient's email
                        ->subject('Verify Your Email to Complete Your FindMyGuru Signup!');
                    $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
                });
                return $this->sendSuccessResponse('OTP resend successfully.', '');

            } elseif ($request->otp_type == 'phone') {
                $verify_otp = DB::table('verify_otp')->where('phone', $request->phone)->orderBy('id', 'DESC')->first();

                if (!$verify_otp) {
                    return $this->sendErrorResponse('OTP details not found.', '', 404);
                }
                DB::table('verify_otp')->where('id', $verify_otp->id)->update([
                    'phone_otp' => $phoneOTP,
                    'email_otp_expire' => $expirationTime,
                    'phone_otp_expire' => $expirationTime,
                ]);
                $templateId = "674f05a7d6fc054fe8452692";
                $mobile = "91". $request->phone;
                $variables = [
                    'number' => $phoneOTP,
                    'VAR2' => 'VALUE 2',
                ];
                $response = $this->sendSms($templateId, $mobile, $variables);
                return $this->sendSuccessResponse('OTP resend successfully.', '');
            } else {
                return $this->sendErrorResponse('Please send a correct otp type', '', 404);
            }
           

        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function studentSendOTP(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $email = $request->email;
            $otp = rand(1000, 9999);

            $data = array("email" => $email, "otp" => $otp);
            // Send email
            Mail::send('email.studentSendOTP', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('Verify Your Email to Complete Your Process!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('Email sent to student successfully.', $otp);
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $token = $user->createToken($request->email);

                $expirationTime = now()->addMinutes(config('sanctum.expiration'));
                $token->accessToken->expires_at = $expirationTime;
                $token->accessToken->save();

                $success['token'] =  $token->plainTextToken;
                $success['token_expiration_time'] =  $expirationTime;
                $success['user_id'] =  $user->id;
                $success['f_name'] =  $user->f_name;
                $success['email'] =  $user->email;
                $success['profile_pic'] =  $user->profile_pic;
                $success['user_type'] =  $user->user_type;
                $success['preview_profile_pic'] =  $user->preview_profile_pic;

                if ($user) {
                    Session::put('user_email', $request->email);
                    //Session::put('user_id', $user->id);
                } else {
                    Session::put('user_email', '');
                    Session::put('user_id', '');
                }

                return $this->sendSuccessResponse('User login successfully .', $success);
            } else {
                return $this->sendErrorResponse('Unauthorised access.', 'Unauthorised', 401);
            }
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function sendOTPToPhone(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }
            $user = DB::table('users')
                ->where('phone', $request->phone)
                ->first();
            //dd($user);
            if (!$user) {
                return $this->sendErrorResponse('User not found.', '', 404);
            }

            $phoneOTP = rand(1000, 9999);
            $expirationTime = \Carbon\Carbon::now()->addMinutes(5);
            DB::table('users')->where('id', $user->id)->update([
                'phone_otp' => $phoneOTP,
                'phone_otp_expire' => $expirationTime,
            ]);
            $templateId = "674f087bd6fc051c445c2602";
            $mobile = "91" . $request->phone;
            $variables = [
                'number' => $phoneOTP,
                //'VAR2' => 'VALUE 2',
            ];
            $response = $this->sendSms($templateId, $mobile, $variables);

            return $this->sendSuccessResponse('OTP send successfully.', '');

        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    public function OTPlogin(Request $request): JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'phone' => 'required',
                'otp' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return $this->sendErrorResponse('User not found.', '', 404);
            }
            
            if ($user->phone_otp != $request->otp) {
                return $this->sendErrorResponse('Invalid OTP.', '', 401);
            }

            if (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($user->phone_otp_expire))) {
                return $this->sendErrorResponse('OTP has expired.', '', 401);
            }

            Auth::login($user);

            // Create a token with expiry
            $token = $user->createToken($request->phone);
            $expirationTime = now()->addMinutes(config('sanctum.expiration'));
            $token->accessToken->expires_at = $expirationTime;
            $token->accessToken->save();

            $success = [
                'token' => $token->plainTextToken,
                'token_expiration_time' => $expirationTime,
                'user_id' => $user->id,
                'f_name' => $user->f_name,
                'email' => $user->email,
                'profile_pic' => $user->profile_pic,
                'user_type' => $user->user_type,
            ];

            return $this->sendSuccessResponse('User logged in successfully.', $success);
            
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Get the currently authenticated user
            $user = Auth::user();

            if ($user) {
                // Revoke all tokens issued to the user
                //$user->tokens()->delete();

                // Revoke current user token
                $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

                // Clear the session data
                Session::forget('user_email');
                Session::forget('user_id');
                // Session::flush(); // Optional: Clears the entire session

                return $this->sendSuccessResponse('User logged out successfully.', '');
            }

            return $this->sendErrorResponse('User is not logged in.', '', 401);
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function forcedLogout(Request $request)
    {
        try {
            // Get the currently authenticated user
            $user = Auth::user();

            if ($user) {
                // Revoke all tokens issued to the user
                $user->tokens()->delete();

                // Clear the session data
                Session::forget('user_email');
                Session::forget('user_id');

                // Session::flush(); // Optional: Clears the entire session

                return $this->sendSuccessResponse('User logged out from all device successfully.', '');
            }

            return $this->sendErrorResponse('User is not logged in.', '', 401);
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user_email = session()->get('user_email');
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'current_password' => 'required',
                'new_password' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }
            $user = DB::table('users')
                ->where('email', $request->email)
                ->first();
            //dd($user);
            if (!$user) {
                return $this->sendErrorResponse('User not found.', '', 404);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->sendErrorResponse('Current password is incorrect.', '', 400);
            }

            // $user->password = bcrypt($request->new_password);
            // $user->save();
            DB::table('users')->where('email', $request->email)->update([
                'password' => bcrypt($request->new_password),
            ]);


            $email = $user->email;
            $name = $user->f_name;

            $data = array("email" => $email, "name" => $name);
            // Send email
            Mail::send('email.changePasswordConfirmmation', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('Password Change Confirmation.');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('Password updated successfully.', '');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $user_email = session()->get('user_email');
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 400);
            }
            $user = DB::table('users')
            ->where('email', $request->email)
                ->first();
            //dd($user);
            if (!$user) {
                return $this->sendErrorResponse('User not found.', '', 404);
            }

            $email = $user->email;
            $name = $user->f_name;
            $otp = rand(1000, 9999);

            $data = array("email" => $email, "name" =>$name, "otp" => $otp);
            // Send email
            Mail::send('email.emailVerification', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('Request to Change Password.');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('Password change request send successfully.', $otp);
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'new_password' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 400);
            }
            $user = DB::table('users')
                ->where('email', $request->email)
                ->first();
            //dd($user);
            if (!$user) {
                return $this->sendErrorResponse('User not found.', '', 404);
            }

            DB::table('users')->where('email', $request->email)->update([
                'password' => bcrypt($request->new_password),
            ]);


            $email = $user->email;
            $name = $user->f_name;

            $data = array("email" => $email, "name" => $name);
            // Send email
            Mail::send('email.changePasswordConfirmmation', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('Password Reset Confirmation.');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('Password Reset successfully.', '');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function view_user_profile(Request $request, $id): JsonResponse
    {
        try {
            $user_id = Auth::user()->id;
            if ($user_id != $id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }

            $data = DB::table('users')
                ->select(
                    'users.id',
                    'users.user_type',
                    'users.f_name',
                    'users.l_name',
                    'users.email',
                    'users.phone',
                    'users.profile_pic',
                    'preview_profile_pic',
                    'users.bio',
                    'users.country',
                    'country.name as country_name',
                    'users.state',
                    'states.state as state_name',
                    'users.city',
                    'cities.name as city_name',
                    'users.area',
                    'users.address',
                    'users.postcode',
                    'users.gst_no',
                    'users.year_of_exp',
                )
                ->leftJoin('country', 'users.country', '=', 'country.id')
                ->leftJoin('states', 'users.state', '=', 'states.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('users.id', $id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Not found.', '');
            }

            $skill = DB::table('user_skills')
                ->where('user_id', $id)
                ->pluck('skill')
                ->toArray();
            
            
            $skills = DB::table('skills')->select('id as value', 'name as label')->whereIn('id', $skill)->get();

            $qualification = DB::table('user_qualifications')
            ->where('user_id', $id)
            ->pluck('qualification')
            ->toArray();

            $qualifications = DB::table('qualifications')->select('id as value', 'name as label')->whereIn('id', $qualification)->get();

            $language = DB::table('user_languages')
            ->where('user_id', $id)
            ->pluck('language')
            ->toArray();

            $languages = DB::table('languages')->select('id as value', 'name as label')->whereIn('id', $language)->get();

            // $user_skills =  DB::table('user_skills')
            //     ->select('id', 'skill')
            //     ->where('user_id', $id)
            //     ->get();


            $data->skill = $skills;
            $data->qualification = $qualifications;
            $data->language = $languages;

            //$skills = DB::table('skills')->get();

            // $states = DB::table('states')
            //     ->get();

            $countries = DB::table('country')->select('id as value', 'name as label')->get();

            $data->countries = $countries;
            //$areas = DB::table('areas')->get();


            //$data->user_skills = $user_skills;
            //$data->skills = $skills;
            //$data->states = $states;
            //$data->areas = $areas;

            return $this->sendSuccessResponse('Course details fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Course details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function delete_file($path)
    {
        //$file_path = public_path('path/to/your/file.txt');
        $file_path = public_path($path);
        if (File::exists($file_path)) {
            File::delete($file_path);
            // echo 'File deleted successfully.';
        } else {
            // echo 'File does not exist.';
        }
    }

    public function updateProfilePic(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'profile_pic' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors());
            }

            $user_id = Auth::user()->id;
            if ($user_id != $request->user_id) {
                return $this->sendErrorResponse('Authentication error.', '');
            }

            $data = DB::table('users')
                ->select('id', 'profile_pic')
                ->where('id', $request->user_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }
            // Save new file
            $path = public_path('uploads/profile_pic');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $fileName = null;
            $filePath = $data->profile_pic;
            if ($request->hasFile('profile_pic')) {
                $fileName = time() . rand(1000, 9999) . "_" . $request->file('profile_pic')->getClientOriginalName();
                $request->profile_pic->move($path, $fileName);
                $filePath = "uploads/profile_pic/" . $fileName;

                if ($data->profile_pic) {
                    $this->delete_file($data->profile_pic);
                }
            }

            $updatedData = [
                'profile_pic' => $filePath,
                'preview_profile_pic' => $request->preview_profile_pic,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('users')->where('id', $request->user_id)->update($updatedData);

            // return $this->sendSuccessResponse('User profile picture updated successfully.', '');
            
            return $this->sendSuccessResponse('User profile picture updated successfully.', $updatedData);
            
            // $response = [
            //             'status' => true,
            //             'message' => 'User19 profile picture updated successfully.',
            //             'data'    => $updatedData,
            //             ];
            // return response()->json($response, 200);
        
        
        
        } catch (\Throwable $th) {
            Log::error('User profile picture updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updatePersonalInfo(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'f_name' => 'required',
                'email' => 'required|email',
                'phone' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $data = DB::table('users')
                ->select('id', 'email','phone', 'profile_pic')
                ->where('id', $request->user_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            $updatedData = [
                'f_name' => $request->f_name,
                'l_name' => $request->l_name,
                'bio' => $request->bio,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'area' => $request->area,
                'address' => $request->address,
                'postcode' => $request->postcode,
                'gst_no' => $request->gst_no,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            if ($request->email != $data->email) {
                return $this->sendErrorResponse('Email can`t be changed.', '', 400);
                // $emailExists = DB::table('users')->where('email', $request->email)
                //     ->where('id', '!=', $request->user_id) // Exclude the current user's ID
                //     ->exists();

                // if ($emailExists) {
                //     return $this->sendErrorResponse('Email already in use.', '', 400);
                // }
                // $updatedData['email'] = $request->email;
            }

            if ($request->phone != $data->phone) {
                return $this->sendErrorResponse('Phone can`t be changed.', '', 400);

                // $emailExists = DB::table('users')->where('phone', $request->phone)
                //     ->where('id', '!=', $request->user_id) // Exclude the current user's ID
                //     ->exists();

                // if ($emailExists) {
                //     return $this->sendErrorResponse('Phone already in use.', '', 400);
                // }
                // $updatedData['phone'] = $request->phone;
            }

            $storeInfo = DB::table('users')->where('id', $request->user_id)->update($updatedData);

            return $this->sendSuccessResponse('User personal info updated successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User personal info updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function updateEducationalInfo(Request $request): JsonResponse
    {
        try {


            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(),403);
            }

            $data = DB::table('users')
                ->select('id', 'email', 'profile_pic')
                ->where('id', $request->user_id)
                ->first();

            if (!$data) {
                return $this->sendErrorResponse('Data not found.', '');
            }

            $updatedData = [
                'year_of_exp' => $request->year_of_exp,
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('users')->where('id', $request->user_id)->update($updatedData);

            if ($request->qualification && count(json_decode($request->qualification)) > 0) {
                $qualificationArr = json_decode($request->qualification);
                DB::table('user_qualifications')->where('user_id', $request->user_id)->delete();

                foreach ($qualificationArr as $x) {
                    $insertedData = [
                        'user_id' => $request->user_id,
                        'qualification' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_qualifications')->insert($insertedData);
                }
            }

            if ($request->skill && count(json_decode($request->skill)) > 0) {
                $skillArr = json_decode($request->skill);
                DB::table('user_skills')->where('user_id', $request->user_id)->delete();

                foreach ($skillArr as $x) {
                    $insertedData = [
                        'user_id' => $request->user_id,
                        'skill' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_skills')->insert($insertedData);
                }
            }

            if ($request->language && count(json_decode($request->language)) > 0) {
                $languageArr = json_decode($request->language);
                DB::table('user_languages')->where('user_id', $request->user_id)->delete();

                foreach ($languageArr as $x) {
                    $insertedData = [
                        'user_id' => $request->user_id,
                        'language' => $x,
                        'created_at' => \Carbon\Carbon::now(),
                        'updated_at' => \Carbon\Carbon::now(),
                    ];
                    DB::table('user_languages')->insert($insertedData);
                }
            }

            //return $this->sendErrorResponse('Validation Error.', $arr);  

            return $this->sendSuccessResponse('User educational info updated successfully.', '');
        } catch (\Throwable $th) {
            Log::error('User educational info updated error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function trainerListing(Request $request): JsonResponse
    {

        try {
            // Get filter inputs from the request
            $rating = $request->input('rating'); // Optional filter by rating
            $location = $request->input('location'); // Optional filter by city
            $search_key = $request->input('search_key'); // Optional filter by city
            // $teaching_mode = $request->input('teaching_mode'); // Optional filter by teaching mode
            // $skill = $request->input('skill'); // Optional filter by skill
            $sortby = $request->input('sortby'); // Optional sorting by rating

            // Build the query
            $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'cities.name as city_name',
                    DB::raw('GROUP_CONCAT(DISTINCT skills.name) as skill_name'),
                    DB::raw('GROUP_CONCAT(DISTINCT languages.name) as languages'),
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                    DB::raw('COUNT(DISTINCT user_course_student_lead.id) as contactListCount')
                )
                ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
                ->leftJoin('skills', 'skills.id', '=', 'user_skills.skill')
                ->leftJoin('user_languages', 'users.id', '=', 'user_languages.user_id')
                ->leftJoin('languages', 'languages.id', '=', 'user_languages.language')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('student_course_reviews', 'student_course_reviews.user_id', '=', 'users.id')
                ->leftJoin('user_course_student_lead', 'user_course_student_lead.user_id', '=', 'users.id')
                ->where('users.user_type', '=', 'tutor')
                ->groupBy(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'city_name',
                );
            
            
            
            // Conditionally apply filters if parameters exist

            if (!is_null($rating)) {
                $query->having('average_rating', '>=', $rating);
            }

            // if (!is_null($skill)) {
            //     $query->where('skills.name', 'like', '%' . $skill . '%');
            // }

            if (!is_null($location)) {
                $query->where(DB::raw('LOWER(cities.name)'), 'like', '%' . strtolower($location) . '%');
            }

            if (!is_null($search_key)) {
                $query->where(DB::raw('LOWER(skills.name)'), 'like', '%' . strtolower($search_key) . '%');
            }

            // if (!is_null($teaching_mode)) {
            //     $query->where('courses.teaching_mode', '=', $teaching_mode);
            // }

            // Apply sorting if provided
            if ($sortby == 'rating-high') {
                $query->orderBy('average_rating', 'desc');
            }
            elseif ($sortby == 'rating-low') {
                $query->orderBy('average_rating', 'asc');
            }
            elseif ($sortby == 'popular') {
                $query->orderBy('contactListCount', 'desc');
            }
            else {
                // Default sorting (optional, if you want)
                $query->orderBy('average_rating', 'desc');
            }

            // Execute the query
            $tutors = $query->get();
            
            $tutors->transform(function ($tutors) {
                $tutors->skill_name = $tutors->skill_name ? explode(',', $tutors->skill_name) : [];
                return $tutors;
            });
            
            
            $tutors->transform(function ($tutors) {
                $tutors->languages = $tutors->languages ? explode(',', $tutors->languages) : [];
                return $tutors;
            });

            // Return the response
            return $this->sendSuccessResponse('Tutors fetched successfully.', $tutors);
        } catch (\Throwable $th) {
            Log::error('Tutor fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function trainerDetails(Request $request, $id): JsonResponse
    {

        try {

            $user_id = $id;

            // Build the query
            $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'cities.name as city_name',
                    DB::raw('GROUP_CONCAT(DISTINCT skills.name) as skill_name'),
                    DB::raw('GROUP_CONCAT(DISTINCT languages.name) as languages'),
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                )
                ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
                ->leftJoin('skills', 'skills.id', '=', 'user_skills.skill')
                ->leftJoin('user_languages', 'users.id', '=', 'user_languages.user_id')
                ->leftJoin('languages', 'languages.id', '=', 'user_languages.language')
                ->leftJoin('student_course_reviews', 'student_course_reviews.user_id', '=', 'users.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('users.id', '=', $user_id)
                ->groupBy(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'city_name',
                );

            // Execute the query
            $tutors = $query->first();
            
            if (!$tutors) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            if ($tutors) {
                $tutors->skill_name = $tutors->skill_name ? explode(',', $tutors->skill_name) : [];
                $tutors->languages = $tutors->languages ? explode(',', $tutors->languages) : [];
            }


            $totalReviews = DB::table('student_course_reviews')->where('user_id', $user_id)->count('id');
            $courseStudents = DB::table('user_course_student_lead')->where('user_id', $user_id)->count('id');
            $webinarStudents = DB::table('user_webinar_student_lead')->where('user_id', $user_id)->count('id');
            $totalStudents = $courseStudents + $webinarStudents;

            $tutors->totalReviews = $totalReviews;
            $tutors->totalStudents = $totalStudents;

            $relatedCourseQuery = DB::table('courses')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'skills.name as skill_name',
                    'courses.user_id',
                    'courses.id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.featured',
                    'courses.course_logo',
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating')
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('courses.user_id', '=', $user_id)
                ->groupBy(
                    'users.f_name',
                    'city_name',
                    'category_name',
                    'skill_name',
                    'courses.user_id',
                    'courses.id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.course_logo',
                    'courses.featured',
                );

            $relatedCourseQuery->orderBy('average_rating', 'desc');

            $relatedCourses = $relatedCourseQuery->get();

            $tutors->relatedCourses = $relatedCourses;

            // Return the response
            return $this->sendSuccessResponse('Tutors fetched successfully.', $tutors);
        } catch (\Throwable $th) {
            Log::error('Tutor listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function instituteListing(Request $request): JsonResponse
    {

        try {
            // Get filter inputs from the request
            $rating = $request->input('rating'); // Optional filter by rating
            $location = $request->input('location'); // Optional filter by city
            $search_key = $request->input('search_key'); // Optional filter by city
            // $teaching_mode = $request->input('teaching_mode'); // Optional filter by teaching mode
            // $skill = $request->input('skill'); // Optional filter by skill
            $sortby = $request->input('sortby'); // Optional sorting by rating

            // Build the query
            $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'cities.name as city_name',
                    DB::raw('GROUP_CONCAT(DISTINCT skills.name) as skill_name'),
                    DB::raw('GROUP_CONCAT(DISTINCT languages.name) as languages'),
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                    DB::raw('COUNT(DISTINCT student_course_reviews.id) as totalReviews'),
                    DB::raw('COUNT(DISTINCT user_course_student_lead.id) as contactListCount')
                )
                ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
                ->leftJoin('skills', 'skills.id', '=', 'user_skills.skill')
                ->leftJoin('user_languages', 'users.id', '=', 'user_languages.user_id')
                ->leftJoin('languages', 'languages.id', '=', 'user_languages.language')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->leftJoin('student_course_reviews', 'student_course_reviews.user_id', '=', 'users.id')
                ->leftJoin('user_course_student_lead', 'user_course_student_lead.user_id', '=', 'users.id')
                ->where('users.user_type', '=', 'institute')
                ->groupBy(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'cities.name',
                );

            // Conditionally apply filters if parameters exist
            if (!is_null($rating)) {
                $query->having('average_rating', '>=', $rating);
            }

            if (!is_null($location)) {
                $query->where(DB::raw('LOWER(cities.name)'), 'like', '%' . strtolower($location) . '%');
            }

            if (!is_null($search_key)) {
                $query->where(DB::raw('LOWER(skills.name)'), 'like', '%' . strtolower($search_key) . '%');
            }
            
            // Apply sorting if provided
            if ($sortby == 'rating-high') {
                $query->orderBy('average_rating', 'desc');
            }
            elseif ($sortby == 'rating-low') {
                $query->orderBy('average_rating', 'asc');
            }
            elseif ($sortby == 'popular') {
                $query->orderBy('contactListCount', 'desc');
            }
            else {
                // Default sorting (optional, if you want)
                $query->orderBy('average_rating', 'desc');
            }

            // Execute the query
            $tutors = $query->get();
            
            if (!$tutors) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            $tutors->transform(function ($tutors) {
                $tutors->skill_name = $tutors->skill_name ? explode(',', $tutors->skill_name) : [];
                return $tutors;
            });
            
            $tutors->transform(function ($tutors) {
                $tutors->languages = $tutors->languages ? explode(',', $tutors->languages) : [];
                return $tutors;
            });
            
            // Return the response
            return $this->sendSuccessResponse('Institutions fetched successfully.', $tutors);
        } catch (\Throwable $th) {
            Log::error('Institutions listing error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function instituteDetails(Request $request, $id): JsonResponse
    {

        try {

            $user_id = $id;

            // Build the query
            $query = DB::table('users')
                ->select(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'cities.name as city_name',
                    DB::raw('GROUP_CONCAT(DISTINCT skills.name) as skill_name'),
                    DB::raw('GROUP_CONCAT(DISTINCT languages.name) as languages'),
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating'),
                )
                ->leftJoin('user_skills', 'users.id', '=', 'user_skills.user_id')
                ->leftJoin('skills', 'skills.id', '=', 'user_skills.skill')
                ->leftJoin('user_languages', 'users.id', '=', 'user_languages.user_id')
                ->leftJoin('languages', 'languages.id', '=', 'user_languages.language')
                ->leftJoin('student_course_reviews', 'student_course_reviews.user_id', '=', 'users.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('users.id', '=', $user_id)
                ->groupBy(
                    'users.id',
                    'users.f_name',
                    'users.l_name',
                    'users.profile_pic',
                    'users.preview_profile_pic',
                    'users.year_of_exp',
                    'users.bio',
                    'city_name',
                )
                ->limit(4);

            // if (!is_null($location)) {
            //     $query->where(DB::raw('LOWER(cities.name)'), 'like', '%' . strtolower($location) . '%');
            // }

            // if (!is_null($search_key)) {
            //     $query->where(DB::raw('LOWER(user_skills.skill)'), 'like', '%' . strtolower($search_key) . '%');
            // }


            // Execute the query
            $tutors = $query->first();
            
            if (!$tutors) {
                return $this->sendErrorResponse('Data not found.', '', 404);
            }

            if ($tutors) {
                $tutors->skill_name = $tutors->skill_name ? explode(',', $tutors->skill_name) : [];
                $tutors->languages = $tutors->languages ? explode(',', $tutors->languages) : [];
            }

            $totalReviews = DB::table('student_course_reviews')->where('user_id', $user_id)->count('id');
            $courseStudents = DB::table('user_course_student_lead')->where('user_id', $user_id)->count('id');
            $webinarStudents = DB::table('user_webinar_student_lead')->where('user_id', $user_id)->count('id');
            $totalStudents = $courseStudents + $webinarStudents;

            $tutors->totalReviews = $totalReviews ;
            $tutors->totalStudents = $totalStudents ;

            $relatedCourseQuery = DB::table('courses')
                ->select(
                    'users.f_name',
                    'cities.name as city_name',
                    'categories.name as category_name',
                    'skills.name as skill_name',
                    'courses.user_id',
                    'courses.id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.featured',
                    'courses.course_logo',
                    DB::raw('COALESCE(CAST(ROUND(AVG(student_course_reviews.rating), 1) AS DECIMAL(10, 1)), 0) as average_rating')
                )
                ->leftJoin('student_course_reviews', 'courses.id', '=', 'student_course_reviews.course_id')
                ->leftJoin('users', 'courses.user_id', '=', 'users.id')
                ->leftJoin('categories', 'courses.category_id', '=', 'categories.id')
                ->leftJoin('skills', 'courses.skill_id', '=', 'skills.id')
                ->leftJoin('cities', 'users.city', '=', 'cities.id')
                ->where('courses.user_id', '=', $user_id)
                ->groupBy(
                    'users.f_name',
                    'city_name',
                    'category_name',
                    'skill_name',
                    'courses.user_id',
                    'courses.id',
                    'courses.course_name',
                    'courses.year_of_exp',
                    'courses.duration_value',
                    'courses.duration_unit',
                    'courses.teaching_mode',
                    'courses.batch_type',
                    'courses.course_logo',
                    'courses.featured',
                );

            $relatedCourseQuery->orderBy('average_rating', 'desc');

            $relatedCourses = $relatedCourseQuery->get();

            $tutors->relatedCourses = $relatedCourses;

            // Return the response
            return $this->sendSuccessResponse('Institute details fetched successfully.', $tutors);
        } catch (\Throwable $th) {
            Log::error('Institute details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function cronTest(Request $request): JsonResponse
    {
        try {
            $currentPackage = DB::table('user_subscription_purchase_history as usph')
                ->select('users.f_name', 'users.email', 'users.phone', 'usph.package_name', 'usph.end_date',)
                ->leftJoin('users', 'usph.user_id', '=', 'users.id')
                ->where('end_date', '>=', \Carbon\Carbon::now())
                ->get();

            foreach ($currentPackage as $key => $value) {
                $end_date = $value->end_date;
                $current_date = \Carbon\Carbon::now();

                $datetime1 = new DateTime($end_date);
                $datetime2 = \Carbon\Carbon::now();
                $interval = $datetime1->diff($datetime2);

                $email = $value->email;
                $name =  $value->f_name;
                // echo($interval->days);
                // echo(',');
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

            return $this->sendSuccessResponse('Cron test.','');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function statesListing(Request $request): JsonResponse
    {
        try {
            $country_id = $request->input('country_id');
            $query = DB::table('states')->select('id as value', 'state as label');
            if (!is_null($country_id)) {
                $query->where('country_id', '=', $country_id);
            }
               $states =  $query->get();
            return $this->sendSuccessResponse('States fetched successfully.', $states);
        } catch (\Throwable $th) {
            Log::error('States fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function cityListing(Request $request): JsonResponse
    {
        try {

            $state_id = $request->input('state_id');
            $query = DB::table('cities')->select('id as value', 'name as label');
            if (!is_null($state_id)) {
                $query->where('state_id', '=', $state_id);
            }
            $cities =  $query->get();
            return $this->sendSuccessResponse('Cities fetched successfully.', $cities);
        } catch (\Throwable $th) {
            Log::error('Cities fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function skillListing(Request $request): JsonResponse
    {
        try {
            $key = $request->input('key');
            $query = DB::table('skills')->select('id as value', 'name as label');
            if (!is_null($key)) {
                $query->where('name', 'like', '%' . $key . '%');
            }
            $skills =  $query->limit(15)->get();
            return $this->sendSuccessResponse('Skills fetched successfully.', $skills);
        } catch (\Throwable $th) {
            Log::error('Skills fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function languageListing(Request $request): JsonResponse
    {
        try {
            $key = $request->input('key');
            $query = DB::table('languages')->select('id as value', 'name as label');
            if (!is_null($key)) {
                $query->where('name', 'like', '%' . $key . '%');
            }
            $data =  $query->limit(15)->get();
            return $this->sendSuccessResponse('Languages fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Languages fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function categoryListing(Request $request): JsonResponse
    {
        try {
            $key = $request->input('key');
            $query = DB::table('categories')->select('id as value', 'name as label');
            if (!is_null($key)) {
                $query->where('name', 'like', '%' . $key . '%');
            }
            $data =  $query->limit(15)->get();
            return $this->sendSuccessResponse('Categories fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Categories fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function currencyListing(Request $request): JsonResponse
    {
        try {
            $key = $request->input('key');
            $query = DB::table('currency')->select('id as value', 'code as label', 'symbol');
            $data =  $query->get();
            return $this->sendSuccessResponse('Currency fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Currency fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function qualificationListing(Request $request): JsonResponse
    {
        try {
            $key = $request->input('key');
            $query = DB::table('qualifications')->select('id as value', 'name as label');
            if (!is_null($key)) {
                $query->where('name', 'like', '%' . $key . '%');
            }
            $data =  $query->get();
            return $this->sendSuccessResponse('Qualifications fetched successfully.', $data);
        } catch (\Throwable $th) {
            Log::error('Qualifications fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
    
    
    public function contactWithUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'student_name' => 'required',
                'student_email' => 'required|email',
                'student_phone' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $user_details = DB::table('users')
            ->select('id', 'email', 'f_name')
            ->where('id', $request->user_id)->first();

            if (!$user_details) {
                return $this->sendErrorResponse('Data not found', '');
            }


            //$storeInfo = DB::table('user_webinar_student_lead')->insert($insertedData);

            $email = $user_details->email;
            $data = array(
                "name" => $user_details->f_name,
                'student_name' => $request->student_name,
                'student_email' => $request->student_email,
                'student_phone' => $request->student_phone,
                'student_message' => $request->student_message,
            );
            // Send email
            Mail::send('email.contactToUser', $data, function ($message) use ($email) {
                $message->to($email) // Use the recipient's email
                    ->subject('New Student Lead on FindMyGuru!');
                $message->from(env('MAIL_FROM_ADDRESS'), "Find My Guru");
            });

            return $this->sendSuccessResponse('User will contact you soon.', '');
        } catch (\Throwable $th) {
            Log::error('Token generation error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
