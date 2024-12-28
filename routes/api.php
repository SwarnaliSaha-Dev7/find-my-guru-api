<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CourseController;
use App\Http\Controllers\API\WebinarController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\CMSController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AdminMasterDataController;
use App\Http\Controllers\API\AdminPricingController;
use App\Http\Controllers\API\AdminLeadController;
use App\Http\Controllers\API\AdminCourseController;
use App\Http\Controllers\API\AdminContentPageController;
use App\Http\Controllers\API\AdminCMSController;
use App\Http\Controllers\API\FilterController;


Route::controller(UserController::class)->group(function () {
    Route::post('register', 'register');
    Route::post('send-otp', 'sendOTP');
    Route::post('resend-otp', 'reSendOTP');
    Route::post('login', 'login');
    Route::post('send-phone-otp', 'sendOTPToPhone');
    Route::post('otp-login', 'OTPlogin');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
    //Route::get('cron-test','cronTest');
});

//Route::get('user/view-profile/{user_id}', [UserController::class, 'view_user_profile']);
Route::middleware(['auth:sanctum', 'check.token'])->group(function () {
    
    Route::post('change-password', [UserController::class, 'changePassword']);

    Route::get('logout', [UserController::class, 'logout']);

    Route::get('forced-logout', [UserController::class, 'forcedLogout']);

    // user routes
    Route::get('user/view-profile/{user_id}', [UserController::class, 'view_user_profile']);
    Route::post('user/update/profile-pic', [UserController::class, 'updateProfilePic']);
    Route::post('user/update/personal-info', [UserController::class, 'updatePersonalInfo']);
    Route::post('user/update/educational-info', [UserController::class, 'updateEducationalInfo']);

    Route::get('user/dashboard/{user_id}', [DashboardController::class, 'dashboard']);

    //course routes
    Route::get('course/add', [CourseController::class, 'course_add_page']);
    Route::post('course/add', [CourseController::class, 'add']);
    Route::get('course/update/{course_id}', [CourseController::class, 'view_course_update']);
    Route::post('course/update/{course_id}', [CourseController::class, 'update']);
    Route::get('course/delete/{course_id}', [CourseController::class, 'delete']);
    Route::get('user/course/lead/{user_id}', [CourseController::class, 'userCourseLead']);
    Route::get('user/course/potential-lead/{user_id}', [CourseController::class, 'userCoursePotentialLead']);
    Route::get('user/course/listing', [CourseController::class, 'userCourseListing']);
    Route::post('user/unlock/potential-lead', [CourseController::class, 'unlockPotentialLead']);
    
    //webinar routes
    Route::get('webinar/add', [WebinarController::class, 'webinar_add_page']);
    Route::post('webinar/add', [WebinarController::class, 'add']);
    Route::get('webinar/update/{webinar_id}', [WebinarController::class, 'view_webinar_update']);
    Route::post('webinar/update/{webinar_id}', [WebinarController::class, 'update']);
    Route::get('webinar/delete/{webinar_id}', [WebinarController::class, 'delete']);
    Route::get('user/webinar/lead/{user_id}', [WebinarController::class, 'userWebinarLead']);
    Route::get('user/webinar/listing/{user_id}', [WebinarController::class, 'userWebinarListing']);

    // Subscriptions and coins
    Route::post('user/subscriptions/purchase', [SubscriptionController::class, 'subscriptionPurchase']);
    Route::get('user/subscriptions/history/{user_id}', [SubscriptionController::class, 'subscriptionHistory']);
    Route::get('user/subscriptions/delete/{subscription_id}', [SubscriptionController::class, 'deleteSubscription']);
    
    Route::post('user/coins/purchase', [SubscriptionController::class, 'coinPurchase']);
    Route::get('user/coins/purchase-history/{user_id}', [SubscriptionController::class, 'coinPurchaseHistory']);
    Route::get('user/coins/delete/{coin_id}', [SubscriptionController::class, 'deleteCoinHistory']);

    Route::post('user/coins/consumed', [SubscriptionController::class, 'coinConsumed']);
    Route::get('user/coins/consumed-history/{user_id}', [SubscriptionController::class, 'coinConsumedHistory']);

});

// Fetch states 
Route::get('/state/listing', [UserController::class, 'statesListing']);

// Fetch cities 
Route::get('/city/listing', [UserController::class, 'cityListing']);

// Fetch Skills 
Route::get('/skill/listing', [UserController::class, 'skillListing']);

// Fetch Language 
Route::get('/language/listing', [UserController::class, 'languageListing']);

// Fetch Categories 
Route::get('/category/listing', [UserController::class, 'categoryListing']);

// Fetch currency 
Route::get('/currency/listing', [UserController::class, 'currencyListing']);

// Fetch Qualifications 
Route::get('/qualification/listing', [UserController::class, 'qualificationListing']);

//=================== Course =========================
// /course/listing?rating=4&location='kolkata'&teaching_mode='online'&sortby='rating_high_to_low'&skill='php'
Route::get('course/listing', [CourseController::class, 'listing']);
// course details
Route::get('course/details/{course_id}', [CourseController::class, 'courseDetails']);
//contact with tutor
Route::post('course/contact', [CourseController::class, 'contactWithTrainer']);
// students review-rating
Route::post('course/review', [CourseController::class, 'studentsReview']);
// fetch course`s review-rating
Route::get('course/review/listing/{course_id}', [CourseController::class, 'coursesReviewListing']);

//======================= Webinar ===============================
// webinar listing with filter
// /webinar/listing?location='kolkata'&teaching_mode='online'&sortby='rating_high_to_low'&skill='php'
Route::get('webinar/listing', [WebinarController::class, 'listing']);
Route::get('webinar/details/{webinar_id}', [WebinarController::class, 'webinarDetails']);
Route::post('webinar/contact', [WebinarController::class, 'contactWithTrainer']);

//home page details
Route::get('home/listing', [HomeController::class, 'listing']);
Route::get('home/default', [HomeController::class, 'defaultValues']);

//trainer listing with filter
Route::get('trainer/listing', [UserController::class, 'trainerListing']);
Route::get('trainer/details/{user_id}', [UserController::class, 'trainerDetails']);

//Institute listing with filter
Route::get('institute/listing', [UserController::class, 'instituteListing']);
Route::get('institute/details/{user_id}', [UserController::class, 'instituteDetails']);

//================ Blog ==================================
// blog comment insert
Route::post('blog/comment', [BlogController::class, 'insertBlogComment']);
Route::get('blog/listing', [BlogController::class, 'listing']);
Route::get('blog/details/{blog_id}', [BlogController::class, 'details']);

//================== Content Pages ========================
Route::post('contact-us', [CMSController::class, 'contuctUsSendMail']);
Route::get('contact-us', [CMSController::class, 'contactUs']);
Route::get('about-us', [CMSController::class, 'aboutUs']);
Route::get('investor-connect', [CMSController::class, 'investorConnect']);
Route::get('privacy-policy', [CMSController::class, 'privacyPolicy']);
Route::get('faqs', [CMSController::class, 'allFaqs']);
Route::get('terms-and-conditions', [CMSController::class, 'termsAndConditions']);
Route::get('list-your-courses', [CMSController::class, 'listYourCourses']);

//================== Subscription-Coins =======================
Route::get('subscriptions', [CMSController::class, 'subscriptions']);
Route::get('coins', [CMSController::class, 'coins']);

//================= Student ============================
Route::post('student/send-otp', [UserController::class, 'studentSendOTP']);
Route::post('user/contact', [UserController::class, 'contactWithUser']);

//================= Filter Functions ============================
Route::get('FilterParameterList/{type}', [FilterController::class, 'FilterParameterList']);



////////////////////////////////////////////////////////////////////////////////////////
//======================================= Admin =====================================//
//////////////////////////////////////////////////////////////////////////////////////

Route::prefix('admin')->group(function () {

    Route::middleware(['auth:sanctum', 'check.token'])->group(function () {
        Route::controller(AdminController::class)->group(function () {
            //=============== Dashboard =========================
            Route::get('/dashboard/details', 'dashboardDetails');

            //=============== Review =========================
            Route::get('/reviews/listing', 'userReviewListing');    
            Route::post('/reviews/status/update', 'updateReviewStatus');
            Route::get('/reviews/delete/{review_id}', 'deleteReview');

            //================ Settings ====================
            //Route::post('/settings/update', 'updateSettings');
        });

        //=================== Master Data ===========================
        Route::controller(AdminMasterDataController::class)->group(function () {
            //==== Country ====
            Route::get('/country/listing', 'countryListing');
            Route::post('/country/add', 'countryDetailsInsert');
            Route::get('/country/update/{id}', 'countryUpdatedDataFetch');
            Route::post('/country/update/{id}', 'countryDetailsUpdate');
            Route::delete('/country/delete/{id}', 'deleteCountry');

            //==== State ====
            Route::post('/state/add', 'stateDetailsInsert');
            Route::get('/state/listing', 'stateListing');
            Route::get('/state/update/{id}', 'stateUpdatedDataFetch');
            Route::post('/state/update/{id}', 'stateDetailsUpdate');
            Route::delete('/state/delete/{id}', 'deleteState');

            //==== City ====
            Route::post('/city/add', 'cityDetailsInsert');
            Route::get('/city/listing', 'cityListing');
            Route::get('/city/update/{id}', 'cityUpdatedDataFetch');
            Route::post('/city/update/{id}', 'cityDetailsUpdate');
            Route::delete('/city/delete/{id}', 'deleteCity');

            //==== Area ====
            Route::post('/area/add', 'areaDetailsInsert');
            Route::get('/area/listing', 'areaListing');
            Route::get('/area/update/{id}', 'areaUpdatedDataFetch');
            Route::post('/area/update/{id}', 'areaDetailsUpdate');
            Route::delete('/area/delete/{id}', 'deleteArea');

            //==== Qualifications ====
            Route::post('/qualification/add', 'qualificationDetailsInsert');
            Route::get('/qualification/listing', 'qualificationListing');
            Route::get('/qualification/update/{id}', 'qualificationUpdatedDataFetch');
            Route::post('/qualification/update/{id}', 'qualificationDetailsUpdate');
            Route::delete('/qualification/delete/{id}', 'deleteQualification');

            //==== Currency ====
            Route::post('/currency/add', 'currencyDetailsInsert');
            Route::get('/currency/listing', 'currencyListing');
            Route::get('/currency/update/{id}', 'currencyUpdatedDataFetch');
            Route::post('/currency/update/{id}', 'currencyDetailsUpdate');
            Route::delete('/currency/delete/{id}', 'deleteCurrency');

            //==== Languages ====
            Route::post('/language/add', 'languageDetailsInsert');
            Route::get('/language/listing', 'languageListing');
            Route::get('/language/update/{id}', 'languageUpdatedDataFetch');
            Route::post('/language/update/{id}', 'languageDetailsUpdate');
            Route::delete('/language/delete/{id}', 'deleteLanguage');

            //==== Category ====
            Route::post('/category/add', 'categoryDetailsInsert');
            Route::get('/category/listing', 'categoryListing');
            Route::get('/category/update/{id}', 'categoryUpdatedDataFetch');
            Route::post('/category/update/{id}', 'categoryDetailsUpdate');
            Route::delete('/category/delete/{id}', 'deleteCategory');

            //==== Skill ====
            Route::post('/skill/add', 'skillDetailsInsert');
            Route::get('/skill/listing', 'skillListing');
            Route::get('/skill/update/{id}', 'skillUpdatedDataFetch');
            Route::post('/skill/update/{id}', 'skillDetailsUpdate');
            Route::delete('/skill/delete/{id}', 'deleteSkill');
        });

        //=================== Pricing Management ================
        Route::controller(AdminPricingController::class)->group(function () {

            //=============== Subscription =========================
            Route::post('/subscription/add', 'subscriptionDetailsInsert');
            Route::get('/subscription/listing', 'subscriptionListing');
            Route::get('/subscription/update/{id}', 'subscriptionUpdatedDataFetch');
            Route::post('/subscription/update/{id}', 'subscriptionDetailsUpdate');
            Route::delete('/subscription/delete/{id}', 'deleteSubscription');

            //=============== Coin =========================
            Route::post('/coin/add', 'coinDetailsInsert');
            Route::get('/coin/listing', 'coinListing');
            Route::get('/coin/update/{id}', 'coinUpdatedDataFetch');
            Route::post('/coin/update/{id}', 'coinDetailsUpdate');
            Route::post('/coin/update/status/{id}', 'coinStatusUpdate');
            Route::delete('/coin/delete/{id}', 'deleteCoin');
        });

        //=================== Lead Management =========================
        Route::controller(AdminLeadController::class)->group(function () {

            //==== Course Lead
            Route::get('/lead/course/listing', 'courseListing');
            Route::post('/lead/course/contact',  'contactWithTrainer');

            //==== Search enquiry
            Route::get('/lead/search-enquiry/listing', 'searchEnquiryListing');
            Route::delete('/lead/search-enquiry/delete/{id}', 'deleteSearchEnquiry');

            //==== Webinar Lead
            Route::get('/lead/webinar/listing', 'webinarListing');


        });

        //=================== Course Management =========================
        Route::controller(AdminCourseController::class)->group(function () {

            //=============== Course =========================
            Route::get('/course/listing', 'courseListing');
            Route::get('/course/listing/{id}', 'userCourseListing'); // Listing by user id
            Route::post('/course/add', 'add');
            Route::get('/course/add-page-data', 'course_add_page');
            Route::get('/course/update/{id}', 'view_course_update');
            Route::post('/course/update/{id}', 'courseUpdate');
            Route::delete('/course/delete/{id}', 'deleteCourse');

            //============== Business ========================
            //======== Users Routes
            Route::get('/business/user/listing', 'userListing');
            Route::post('/business/user/add', 'userDetailsInsert');
            Route::get('/business/user/update/{id}', 'userDetails');
            Route::post('/business/user/update/{id}', 'userDetailsUpdate');
            Route::delete('/business/user/delete/{id}', 'deleteUser');

            //======== Coins Routes
            Route::get('/business/coin/purchase/history/{id}', 'coinPurchaseHistory'); //history fetched by user_id
            Route::get('/business/coin/purchase/details/{id}', 'coinPurchaseDetails');
            Route::delete('/business/coin/purchase/details/{id}', 'deleteCoinPurchaseRecord');

            Route::get('/business/coin/consumed/history/{id}', 'coinConsumedHistory'); //history fetched by user_id

            //======= Subscription Routes
            Route::get('/business/subscription/purchase/history/{id}', 'subscriptionPurchaseHistory'); //history fetched by user_id
            Route::get('/business/subscription/purchase/details/{id}', 'subscriptionPurchaseDetails');
            Route::delete('/business/subscription/purchase/details/{id}', 'subscriptionPurchaseDetails');
        });

        //=================== Content Page Management =========================
        Route::controller(AdminContentPageController::class)->group(function () {
            //=============== Blog Management =========================
            Route::get('/blog/listing', 'blogListing');
            Route::post('/blog/add', 'blogDetailsInsert');
            Route::get('/blog/details/{id}', 'blogUpdatedDataFetch');
            Route::post('/blog/details/{id}', 'blogDetailsUpdate');
            Route::delete('/blog/details/{id}', 'deleteBlog');

            //=============== Contact-us Page Management =========================
            Route::get('/page/contactUs/details', 'contactPageDetails');
            Route::post('/page/contactUs/details', 'updateContactUs');

            //=============== Webinar Management =========================
            Route::get('/webinar/listing', 'webinarListing');
            Route::post('/webinar/add', 'webinarDetailsInsert');
            Route::get('/webinar/details/{id}', 'webinarUpdatedDataFetch');
            Route::post('/webinar/details/{id}', 'webinarDetailsUpdate');
            Route::delete('/webinar/details/{id}', 'deleteWebinar');

            //=============== Investor Connect Page Management
            Route::get('/page/investor-connect/details', 'investorConnectPageDetails');
            Route::post('/page/investor-connect/details', 'updateInvestorConnectPage');

            //=============== Privacy Policy Page Management
            Route::get('/page/privacy-policy/details', 'privacyPolicyPageDetails');
            Route::post('/page/privacy-policy/details', 'updateprivacyPolicyPage');

            //=============== Global Footer
            Route::get('/page/global/details', 'globalVariableDetails');
            Route::post('/page/global/details', 'updateGlobalVariable');

            //=============== Home Page management
            Route::get('/page/home/details', 'hpmePageDetails');
            Route::post('/page/home/details', 'updateHomePage');

            //=============== FAQ management
            Route::get('/page/faq/listing', 'faqListing');
            Route::post('/page/faq/add', 'faqInsert');
            Route::get('/page/faq/details/{id}', 'faqDetails');
            Route::post('/page/faq/details/{id}', 'faqDetailsUpdate');
            Route::delete('/page/faq/details/{id}', 'deleteFaq');
            
            //=============== Terms And Conditions Page Management
            Route::get('/page/terms-and-conditions/details', 'termsAndConditionsPageDetails');
            Route::post('/page/terms-and-conditions/details', 'updateTermsAndConditionsPage');
            
            //=============== List Your Courses Page Management
            Route::get('/page/list-your-courses/details', 'listYourCoursesPageDetails');
            Route::post('/page/list-your-courses/details', 'listYourCoursesPage');
        });
    });
});

//====== Handle Undefined URL ========
Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'Endpoint not found. Please check the URL or HTTP method.'
    ], 404);
});