<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubmitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PlagiarismController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\OnlineIDEController;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Must be guest routes
Route::middleware('guest')->group(function(){
    Route::get('/login', function () {
        return view('login');
    })->name('login');
    Route::post('/login', [UserController::class,'login']);
    
    Route::get('/signup', function () {
        return view('signup');
    })->name('signup');

    Route::get('/landing', function () {
        return view('landing');
    })->name('landing');

    // Route::get('/logout', 'UserController@logout')->name('logout');

    

    Route::get('/jplag', function () {
        return view('home');
    })->name('jplag');

    Route::post('/signup/createUser', [UserController::class, 'createUser'])->name('createUser');
});

//Must be authenticated routes
Route::middleware('auth')->group(function (){

    Route::get('/logout', [UserController::class,'logout'])->name('logout');

    Route::get('/jplag', function () {
        return view('home');
    })->name('jplag');

    Route::post('/submit', [SubmitController::class,'submit']);

    // Student routes
    Route::get('/', [CourseController::class, 'view_courses'])->name('home');
    Route::get('/home', function () {
        return redirect()->route('home');
    });
    Route::get('/my-courses', function () {
        return redirect()->route('home');
    })->name('my-courses');

    Route::get('/badges', [BadgeController::class, "view"])->name('view_badges');

    Route::get('/all-courses', [UserController::class, "all_courses"])->name('all_courses');

    Route::get('/contact-us', [UserController::class, "view_contact_us"])->name('contact_us');
    Route::post('/contact-us', [UserController::class, "store_contact_us"]);
    
    Route::get('/logout', [UserController::class, "logout"])->name('logout');

    Route::prefix('/course/{id}')->as('course.')->group(function ($id) {
        Route::get('/', [CourseController::class, 'index'])->name('view');
        Route::get('/assignments', [CourseController::class, 'assignments'])->name('assignments');
        
    });

    Route::prefix('/course')->as('course.')->group(function ($id) {
        Route::get('/enroll/{course_id}', [CourseController::class, "student_enroll"])->name('student-enroll');

        Route::post('/enroll/{course_id}', [CourseController::class, "student_enroll_code"]);
    });
    

    Route::get('/assignment/ide/{id}', [OnlineIDEController::class, "view"])->name('ide');
    Route::post('/assignment/ide/{id}', [OnlineIDEController::class, "run"]);

    Route::get('/assignment/skeleton/{id}', function($id){
        $question = Questions::with('programming_language')->findOrFail($id);
        $file_name = $question->name . "." .explode(',', $question->programming_language->extensions)[0];
        header('Content-Type: application/octet-stream');
        header("Content-disposition: attachment; filename=\"" . $file_name . "\""); 
        echo $question->skeleton;
    })->name('skeleton');

    Route::get('/assignment/{id}', [AssignmentController::class, "view"])->name('assignment');
    Route::post('/assignment/{id}', [QuestionController::class, "student_submit"]);

    Route::get('/submission', function () {
        return view('submission');
    });

    Route::middleware('dashboard-access')->prefix('/dashboard')->as('dashboard.')->group(function () {
        Route::get('/', [UserController::class, "home_instructor"])->name('home_instructor');
        Route::get('/form-zip', [PlagiarismController::class, "formZip"]);

        Route::prefix('/users')->as('users.')->group(function ($user_id) {
            Route::prefix('/instructors')->as('instructors.')->group(function ($user_id) {
                Route::get('/view-assignments/{course_id}', [UserController::class, "dashboard_view_assignments"])->name('view_assignments');
                Route::post('/delete', [AssignmentController::class, "delete"]);
                Route::get('/delete-assignments/{assignment_id}', [AssignmentController::class, "delete_assignment"])->name('delete_assignment');
                Route::get('/view-assignments-questions/{assignment_id}', [UserController::class, "view_assignment_questions"])->name('view_assignment_questions');
                Route::get('/edit-question/{question_id}', [QuestionController::class, "edit"])->name('edit_question');
                Route::get('/delete-question/{question_id}', [QuestionController::class, "delete_question"])->name('delete_question');
                Route::post('/edit-question/{question_id}', [QuestionController::class, "edit_question"]);
                Route::get('/edit-assignment/{assignment_id}', [AssignmentController::class, "edit_assignment"])->name('edit_assignment');
                Route::post('/edit-assignment/{assignment_id}', [AssignmentController::class, "edit"]);
                Route::get('/view-question-submission/{question_id}', [UserController::class, "view_question_submission"])->name('view_question_submission');
                Route::get('/delete-submission/{submission_id}', [AssignmentController::class, "delete_submission"])->name('delete_submission');
                Route::get('/run-plag/{zipPath}/{type}/{question_id}', [PlagiarismController::class, "run_plag"])->name('run_plag');
                Route::get('/plagiarism-report', [PlagiarismController::class, "plagiarism_report"])->name('plagiarism_report');
                Route::get('/report/{report}', [PlagiarismController::class, "report"])->name('report');
                Route::get('/view-plagiarism-reports', [PlagiarismController::class, "view_plagiarism_reports"])->name('view_plagiarism_reports');
                Route::get('/edit-submission/{submission_id}', [UserController::class, "edit_submission"])->name('edit_submission');
                Route::post('/edit-submission/{submission_id}', [UserController::class, "edit_sub"]);
                Route::get('/view-submission/{submission_id}', [UserController::class, "view_submission"])->name('view_submission');

                Route::get('/view-all-assignments', [UserController::class, "dashboard_view_all_assignments"])->name('view_all_assignments');
            });
            Route::prefix('/students')->as('students.')->group(function () {
                Route:: get('/', [UserController::class, "dashboard_view_students"])->name('view');
                Route::get('/enroll', [CourseController::class, "enroll_user"])->name('enroll');
                Route::post('/enroll', [CourseController::class, "enroll_user_store"]);

                Route:: get('/search', [UserController::class, "search"])->name('search');

                Route::get('/assign-to-course/{user_id}/{course_id}', [CourseController::class, "assign_to_course"])->name('assign-to-course');
                Route::get('/remove-from-course/{user_id}/{course_id}', [CourseController::class, "remove_from_course"])->name('remove-from-course');
            });

            Route::get('/add', [UserController::class, "add"])->name('add');
            Route::post('/add', [UserController::class, "store"]);
        });
        
        Route::prefix('/assignments')->as('assignments.')->group(function () {
            Route::get('/add', [AssignmentController::class, "add"])->name('add_assignment');
            Route::post('/add', [AssignmentController::class, "store"]);
        });
        Route::prefix('/qestions/{id}')->group(function ($id) {
            Route::get('/add', [QuestionController::class, "add"])->name('add_question');
            Route::post('/add', [QuestionController::class, "store"]);
        });

        Route::prefix('/code_searcher')->as('code_searcher.')->group(function () {
            Route::get('/search', [QuestionController::class, "fetch_external_plagiarism_files"])->name('search');
        });
        Route::prefix('/plagiarism-checker')->as('plagiarism_checker.')->group(function(){
            Route::get('/question/{id}',[PlagiarismController::class,'check_submissions_plagiarism'])->name('question');
            Route::get('/compare/report/{id}',[PlagiarismController::class, 'compare_files'])->name('compare');
        });



        Route::middleware('dashboard-admins')->group(function () {
            Route::prefix('/users')->as('users.')->group(function ($user_id) {
                Route::prefix('/admins')->as('admins.')->group(function ($user_id) {

                    
                });
                Route::prefix('/instructors')->as('instructors.')->group(function () {
                    Route::get('/', [UserController::class, "dashboard_view_instructors"])->name('view');
                    Route::get('/edit-user/{user_id}', [UserController::class, "edit_user"])->name('edit-user');
                    Route::post('/edit-user/{user_id}', [UserController::class, "edit"]);
                    Route::get('/delete-user/{user_id}', [UserController::class, "delete_user"])->name('delete-user');
                });
                
            });

            Route::prefix('/courses')->as('courses.')->group(function () {
                Route::get('/', [CourseController::class, "dashboard_view"])->name('view');
                Route::get('/add', [CourseController::class, "add"])->name('add');
                Route::post('/add', [CourseController::class, "store"]);

                Route::get('/view_course/{course_id}', [CourseController::class, "view_course"])->name('view-course');

                Route::get('/edit-course/{course_id}', [CourseController::class, "edit_course"])->name('edit-course');
                Route::post('/edit-course/{course_id}', [CourseController::class, "edit"]);

                Route::get('/delete/{course_id}', [CourseController::class, "delete"])->name('delete');
            });

        });
    });

    

});


Route::get('/generate_password_for_users',function(){
    $users = User::all();
    foreach($users as $user){
        if(strlen($user->password) <60){
            $user->password = bcrypt($user->password);
            $user->save();
        }
    }
});


Route::get('/assign_all_users_to_computer_science_course', function () {
    $users = User::all();
    foreach ($users as $user) {
        if($user->hasRole('student'))
            continue;
        $user->assignRole('student');
        $user->courses()->attach(1);
    }
});

Route::get('/give_early_bird_badge_to_all_students', function () {
    $users = User::all();
    foreach ($users as $user) {
        $date = date('Y/m/d h:i:s', time());
        for($i=0;$i<3;$i++) {
            DB::table('user_badges')->insert([
            'id' => null,
            'user_id' => $user->id,
            'badge_id' => 3,
            'created_at' => $date,
            'updated_at' => $date
            ]);
        }
    }
});

Route::get('/make_all_users_student', function () {
    $users = User::all();
    foreach ($users as $user) {
        if ($user->hasRole('student'))
        continue;
        $user->assignRole('student');
    }
});

