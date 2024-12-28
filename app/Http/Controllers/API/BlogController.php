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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Throwable;
use DB;
use Session;

class BlogController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */

    public function insertBlogComment(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'blog_id' => 'required',
                'comment' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendErrorResponse('Validation Error.', $validator->errors(), 403);
            }

            $insertedData = [
                'blog_id' => $request->blog_id,
                'comment' => $request->comment,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];

            $storeInfo = DB::table('blog_comments')->insert($insertedData);
            return $this->sendSuccessResponse('Thank you for your comment.', $storeInfo);
        } catch (\Throwable $th) {
            Log::error('Blog comment error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }

    public function listing(Request $request): JsonResponse
    {
        try {
            $pageNumber = request()->input('page', 1);
            $perPage = 16;
            $searchKey = $request->input('searchKey'); // Optional filter by skill
            $sortby = $request->input('sortby'); // Optional sorting by rating
            $searchBycategory = $request->input('category'); // Optional sorting by rating
            // Build the query
            $query = DB::table('blogs')
                ->select(
                    'categories.name as category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.full_content',
                    'blogs.tags',
                    'blogs.created_at',
                    DB::raw('COUNT(blog_comments.id) as no_of_comments')
                )
                ->leftJoin('blog_comments', 'blogs.id', '=', 'blog_comments.blog_id')
                ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
                ->groupBy(
                    'category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.full_content',
                    'blogs.tags',
                    'blogs.created_at',
                );


            if (!is_null($searchKey)) {
                $query->where('blogs.title', 'like', '%' . $searchKey . '%');
            }

            // if (!is_null($searchKey)) {
            //     $query->where(DB::raw('LOWER(cities.name)'), 'like', '%' . strtolower($location) . '%');
            // }

            // Apply sorting if provided
            if ($sortby == 'comment_high_to_low') {
                $query->orderBy('no_of_comments', 'desc');
            } elseif ($sortby == 'comment_low_to_high') {
                $query->orderBy('no_of_comments', 'asc');
            } else {
                $query->orderBy('no_of_comments', 'desc');
            }
            
            if (!is_null($searchBycategory)) {
                $query->where('blogs.category_id', $searchBycategory);
            }
            
            // Execute the query
            $data = $query->paginate($perPage, ['*'], 'page', $pageNumber);
            
            //fetch categories
            $categories = DB::table('categories')
                            ->select('id','name')
                            ->where('status',1)
                            ->get();

            // Return the response
            // return $this->sendSuccessResponse('Blogs fetched successfully.', $data);
            $response = [
                'status' => true,
                'message' => 'Blogs fetched successfully.',
                'data'    => $data,
                'categories' => $categories,
            ];
            return response()->json($response, 200);
            
        } catch (\Throwable $th) {
            Log::error('Blogs fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }


    public function details(Request $request, $id): JsonResponse
    {

        try {
            // Get filter inputs from the request
            $blog_id = $id; // Optional filter by rating

            // Build the query
            $query = DB::table('blogs')
                ->select(
                    'categories.name as category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.full_content',
                    'blogs.tags',
                    'blogs.created_at',
                    'blogs.updated_at',
                    'blogs.category_id',
                )
                ->leftJoin('blog_comments', 'blogs.id', '=', 'blog_comments.blog_id')
                ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
                ->groupBy(
                    'category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.full_content',
                    'blogs.tags',
                    'blogs.created_at',
                    'blogs.updated_at',
                    'blogs.category_id',
                );

            // Conditionally apply filters if parameters exist

            $query->where('blogs.id', '=', $blog_id);

            // Execute the query
            $blogDetails = $query->first();

            $comments = DB::table('blog_comments')
                ->where('blog_id', $blog_id)
                ->orderBy('created_at', 'desc')
                ->get();


            $blogTags = explode(',', $blogDetails->tags); // Extract the tags of the blog

            $relatedQuery = DB::table('blogs')
                ->select(
                    'categories.name as category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.tags',
                    DB::raw(
                        '(
                        ' . implode(' + ', array_map(function ($tag) {
                            return "FIND_IN_SET('$tag', blogs.tags)";
                        }, $blogTags)) . ') as matching_tags_count'
                    )
                )
                ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
                ->where('blogs.id', '!=', $blogDetails->id) // Exclude the current blog
                ->where('blogs.category_id', '=', $blogDetails->category_id) // Same category
                ->groupBy(
                    'category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.tags'
                )
                ->orderBy('matching_tags_count', 'desc') // Order by matching tags count first
                ->orderBy('blogs.created_at', 'desc')
                ->limit(4);

            // Execute the query
            $relatedBlogs = $relatedQuery->get();

            $trendingQuery = DB::table('blogs')
                ->select(
                    'categories.name as category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.tags',
                )
                ->leftJoin('categories', 'blogs.category_id', '=', 'categories.id')
                ->where('blogs.is_trending', '=', '1')
                ->where('blogs.id', '!=', $blogDetails->id)
                ->groupBy(
                    'category_name',
                    'blogs.id',
                    'blogs.title',
                    'blogs.picture',
                    'blogs.short_content',
                    'blogs.tags',
                )
                ->orderBy('blogs.created_at', 'desc')
                ->limit(4);
            //->orderBy('no_of_comments', 'desc');

            // Execute the query
            $trendingBlogs = $trendingQuery->get();

            $blogDetails->comments = $comments;
            $blogDetails->relatedBlogs = $relatedBlogs;
            $blogDetails->trendingBlogs = $trendingBlogs;


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
                ->where('courses.category_id', '=', $blogDetails->category_id)
                ->where('courses.id', '!=', $blogDetails->id)
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

            $blogDetails->relatedCourses = $relatedCourses;
            // Return the response
            return $this->sendSuccessResponse('Blog details fetched successfully.', $blogDetails);
        } catch (\Throwable $th) {
            Log::error('Blog details fetched error: ' . $th->getMessage());
            return $this->sendErrorResponse('Something went wrong.', $th->getMessage(), 500);
        }
    }
}
