<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('qualification');
            $table->timestamps();
        });

        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('skill');
            $table->timestamps();
        });

        Schema::create('user_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('language');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('category_id')->index();
            $table->string('course_name');
            $table->string('year_of_exp');
            $table->integer('duration_value');
            $table->enum('duration_unit', ['hours','days', 'weeks', 'months', 'years']);
            $table->enum('batch_type', ['Weekday','Weekend', 'Both']);
            $table->enum('teaching_mode', ['Online','Offline', 'Both']);
            $table->decimal('fee', 10, 2)->nullable();
            $table->foreignId('currency_id')->index();
            $table->boolean('fee_upon_enquiry')->default(false);
            $table->boolean('first_class_free')->default(false);
            $table->string('demo_video_url')->nullable();
            $table->longText('course_content')->nullable();
            $table->string('course_logo')->nullable();
            $table->string('mete_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->text('search_tag')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('seo1')->nullable();
            $table->text('seo2')->nullable();
            $table->text('seo3')->nullable();
            $table->enum('status', ['Approved','Rejected'])->default('Rejected');
            $table->boolean('top_tranding_course')->default(false);
            $table->boolean('featured')->default(false);
            $table->string('feature_field1')->nullable();
            $table->string('feature_field2')->nullable();
            $table->string('feature_field3')->nullable();
            $table->timestamps();
        });

        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('category_id')->index();
            $table->string('title');
            $table->string('language');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable(); 
            $table->time('end_time')->nullable(); 
            $table->decimal('fee', 10, 2)->nullable();
            $table->foreignId('currency_id')->index();
            $table->integer('no_of_seats')->nullable();
            $table->enum('delivery_mode', ['Online','Offline', 'Both'])->nullable();
            $table->string('address');
            $table->string('agenda');
            $table->string('demo_video_url')->nullable();
            $table->string('logo')->nullable();
            $table->string('mete_title')->nullable();
            $table->longText('meta_description')->nullable();
            $table->text('search_tag')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('seo1')->nullable();
            $table->text('seo2')->nullable();
            $table->text('seo3')->nullable();
            $table->enum('status', ['Approved','Rejected'])->default('Rejected');
            $table->boolean('top_tranding_course')->default(false);
            $table->boolean('featured')->default(false);
            $table->string('feature_field1')->nullable();
            $table->string('feature_field2')->nullable();
            $table->string('feature_field3')->nullable();
            $table->timestamps();
        });
        
        Schema::create('courses_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->index();
            $table->string('skill_name');
            $table->timestamps();
        });
        
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('duration_in_months');
            $table->decimal('actual_price', 10, 2)->nullable();
            $table->decimal('offer_price', 10, 2)->nullable();
            $table->integer('free_coins')->nullable();
            $table->boolean('featured_listing')->default(false);
            $table->longText('description')->nullable();
            $table->enum('status', ['Active','Inactive'])->default('Active');
            $table->timestamps();
        });
        
        Schema::create('coin_packages_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->integer('duration_in_months');
            $table->decimal('min_amount', 10, 2)->nullable();
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->decimal('coin_to_rupee_ratio', 10, 2)->nullable();
            $table->datetime('expiry_date')->nullable();
            $table->enum('status', ['Active','Inactive'])->default('Active');
            $table->timestamps();
        });

        Schema::create('user_subscription_purchase_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('subcription_id')->index();
            $table->datetime('subcription_date')->nullable();
            $table->string('package_name');
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->decimal('gst_amount', 10, 2)->nullable();
            $table->enum('payment_status', ['Success','Failed','Pending'])->nullable();
            $table->string('transuction_id');
            $table->timestamps();
        });

        Schema::create('user_coin_purchase_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('coin_package_id')->index();
            $table->datetime('purchase_date')->nullable();
            $table->integer('coins_received')->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->enum('payment_status', ['Success','Failed','Pending'])->nullable();
            $table->string('transuction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('user_coin_consumption_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->string('student_name');
            $table->string('skill_name');
            $table->datetime('enquiry_date')->nullable();
            $table->datetime('coin_consumed_date')->nullable();
            $table->integer('coins_consumed')->nullable();
            $table->timestamps();
        });

        
        Schema::create('user_course_student_lead', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('course_id')->index();
            $table->string('user_type')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_email')->nullable();
            $table->string('student_phone')->nullable();
            $table->longText('student_message')->nullable();
            $table->string('tutor_action')->nullable();
            $table->text('tutor_notes')->nullable();
            $table->timestamps();
        });
        
        Schema::create('user_action', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->nullable();
            $table->foreignId('course_id')->index();
            $table->string('tutor_action')->nullable();
            $table->longText('tutor_action_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        Schema::create('user_webinar_student_lead', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('webinar_id')->index();
            $table->string('webinar_title')->nullable();
            $table->string('user_type')->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_email')->nullable();
            $table->string('student_phone')->nullable();
            $table->longText('student_message')->nullable();
            $table->string('tutor_action')->nullable();
            $table->text('tutor_notes')->nullable();
            $table->timestamps();
        });
        
        Schema::create('student_course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreignId('course_id')->index();
            $table->foreignId('student_id')->index()->nullable();
            $table->string('student_name')->nullable();
            $table->string('student_email')->nullable();
            $table->string('student_phone')->nullable();
            $table->tinyInteger('rating')->nullable();
            $table->string('review')->nullable();
            $table->datetime('date')->nullable();
            $table->enum('approval_status', ['Success','Failed','Pending'])->nullable();
            $table->timestamps();
        });
        
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->index()->nullable();
            $table->string('title');
            $table->string('category');
            $table->string('picture');
            $table->text('short_content')->nullable();
            $table->longText('full_content')->nullable();
            $table->string('tags');
            $table->string('mete_title')->nullable();
            $table->text('meta_tag')->nullable();
            $table->longText('meta_description')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->text('seo1')->nullable();
            $table->text('seo2')->nullable();
            $table->text('seo3')->nullable();
            $table->enum('status', ['Published','Draft','Archived'])->nullable();
            $table->timestamps();
        });
        
        Schema::create('admin_search_table', function (Blueprint $table) {
            $table->id();
            $table->string('searched_skill')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_qualifications');
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('user_languages');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('webinars');
        Schema::dropIfExists('courses_skills');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('coin_packages_plans');
        Schema::dropIfExists('user_subscription_purchase_history');
        Schema::dropIfExists('user_coin_purchase_history');
        Schema::dropIfExists('user_coin_consumption_history');
        Schema::dropIfExists('user_course_student_lead');
        Schema::dropIfExists('user_action');
        Schema::dropIfExists('user_webinar_student_lead');
        Schema::dropIfExists('student_course_reviews');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('admin_search_table');
    }
};
