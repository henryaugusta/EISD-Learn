<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\DropzoneController;
use App\Http\Controllers\FileOnS3Controller;
use App\Http\Controllers\GraphController;
use App\Http\Controllers\LaravelEstriController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QRLoginController;
use App\Http\Controllers\RegistrationCodeController;
use App\Http\Controllers\SendEmailController;
use App\Http\Controllers\TeamsPlannerController;
use App\Http\Controllers\TestEmailController;
use App\Http\Controllers\VisualizationDetailController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use UniSharp\LaravelFilemanager\Lfm;






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

Route::get('/iseng-iseng-aja', 'AitiHubController@checkAitiHub');
Route::get('mobile/course/{lesson}/section/{section}', 'MobileSeeCourseController@seeSection');
Route::get('mobile-api/course/{lesson}/section/{section}', 'MobileLmsViewerController@seeSection');


Route::get('public-exam/{sessionId}', 'CourseSectionController@publicExam');

Route::get('/', 'LandingController@landing');
Route::get('/classes', 'LandingController@classes');
Route::get('/blogs', 'LandingController@blogs');
Route::get('/home', 'HomeController@index');

// Route::view('forgotpass', 'auth.forgotpass');

// HANDLING RESET PASSWORD
Route::get('forgotpass', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('forgotpass');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('/password/sent', function () {
    return view('auth.alert_emailSent');
})->name('password.sent');


// HANDLING CHANGE PASSWORD
Route::get('password/change', [ChangePasswordController::class, 'showChangeForm'])->name('password.change');
Route::post('password/change-pass', [ChangePasswordController::class, 'change'])->name('password.updateChange');



Route::get('/profile', 'ProfileController@index')->middleware('auth')->name('profile');
Route::post('/profile/update', 'ProfileController@update')->name('profile.update')->middleware('auth');
Route::post('/profile/update/socmed', 'ProfileController@updateSocMed')->name('profile.updateSocMed')->middleware('auth');

// Route::post('/class/class-list/check/{lessonId}', 'DetailClassController@viewStudents')->name('view_students.viewStudents')->middleware('auth');
// Route::post('/class/class-list/students/{lessonId}')->name('view_students.viewStudents')->middleware('auth');

Route::get('/course/{lesson}/section/{section}', 'CourseSectionController@seeSection')->name('course.see_section');


Route::get('/encrypt', [AbsensiController::class, 'encryptData']);
Route::get('/decrypt', [AbsensiController::class, 'decryptData']);

Route::get('/datatable', function () {
    return view('blog.datatable');
});

Route::get('s3/image-upload', [LaravelEstriController::class, 'imageUpload'])->name('image.upload');
Route::post('s3/image-upload', [LaravelEstriController::class, 'imageUploadPost'])->name('image.upload.post');
Route::get('s3/get', [LaravelEstriController::class, 'getAllFilesWithUrls']);

Route::redirect('/course', '/classes');


Route::get('/template', function () {
    return view('template.atlantis');
});

Route::get('/loginz', function () {
    return view('neo_login');
});


Route::get('/visualization/main-pie-chart-details', [VisualizationDetailController::class, 'seeMainPieChartDetail']);


Route::post("/mitra/registration/", 'MitraController@registerOnWeb')->name('partner-register');


Route::get('/open-lms-from-ithub', 'ModernlandIntegrationController@proceedLoginFromIthub');
// ROUTING SETELAH LOGIN
Route::group(['middleware’' => ['auth']], function () {

    Route::get('sync', [LaravelEstriController::class, 'syncDataWithIthub']);
    // Route to create a new folder (this is triggered by the modal form)
    Route::post('/folders/create', [GraphController::class, 'create'])->name('folders.create');
    Route::get('/onedrive/folders', [GraphController::class, 'listOneDriveFolders']);
    Route::get('/365/users', [GraphController::class, 'listUsers']);
    Route::get('/download-file/{driveId}/{fileId}', [GraphController::class, 'downloadFile'])
        ->name('download-file-graph');
    Route::get('/sites', [GraphController::class, 'listSites'])->name('sites');
    Route::get('/sharepoint/{siteId}', 'GraphController@showSharePoint')->name('sharepoint');
    Route::get('/drives/{siteId}', [GraphController::class, 'showDrives'])->name('drives');
    Route::delete('/files/{fileId}', [GraphController::class, 'deleteFile'])->name('delete-file-graph');
    Route::get('/folders/{siteId}/{driveId}', [GraphController::class, 'readFolders'])->name('folders');
    Route::get('/files/{siteId}/{driveId}/{folderId}', [GraphController::class, 'readFiles'])->name('files');
    Route::post('/upload/{siteId}/{driveId}/{folderId}', [GraphController::class, 'uploadFile'])->name('upload');


    // Prefix Planner group
    Route::prefix("planner")->group(function () {
        Route::get('my-teams', [TeamsPlannerController::class, 'listUserTeams']);
    });


    // List all files (index)
    Route::get('/filemanager/s3', [FileOnS3Controller::class, 'index'])->name('filemanager.s3.index');

    // Show form to upload a file (create)
    Route::get('/filemanager/s3/create', [FileOnS3Controller::class, 'create'])->name('filemanager.s3.create');

    // Handle file upload (store)
    Route::post('/filemanager/s3', [FileOnS3Controller::class, 'store'])->name('filemanager.s3.store');
    // Delete file (destroy)
    Route::delete('/filemanager/s3/{file}', [FileOnS3Controller::class, 'destroy'])->name('filemanager.s3.destroy');


    Route::any('/exam/save-user-answer', 'ExamTakerController@submitQuiz');
    Route::any('/exam/fetch-result-on-section', 'ExamTakerController@fetchResultByStudentOnSection');

    Route::post('/dfef', 'ProfileController@updatePasswordz');
    Route::get('/class/class-list/view-class/{id}', 'DetailClassController@viewClass');
    Route::get('/class/class-list/mentor-view-class/{id}', 'DetailClassController@mentor_viewClass');
    Route::get('/class/class-list/students/{lessonId}', 'DetailClassController@viewStudents');
    Route::get('/class/students/{lessonId}', 'CourseSectionController@viewStudents');
    // Route::post('sortBy', 'CourseSectionController@sortBy')->name('sortBy');
    Route::post('/sortBy/{lessonId?}', [CourseSectionController::class, 'sortBy'])->name('sortBy');



    Route::get('/class/class-list', 'ClassListController@classList');
    Route::get('/class/my-class', 'MyClassController@myClass');
    Route::get('/my-class/open/{lessonId}/section/{sectionId}', 'OpenClassController@openClass')->name('course.openClass');

    // Route::get('/class/class-list/', 'CountingController@countStudents');
    Route::post('/input-pin', 'ClassListController@validatePIN');
    Route::post('/add-new-student/{lessonId}', 'CourseSectionController@add_Students');
    Route::post('find-student-by-department', 'CourseSectionController@find_student_by_dept');
    Route::get('fetch-departments', 'LessonController@fetchDepartments');
    Route::get('fetch-positions', 'LessonController@fetchPositions');
    Route::post('fetch-show', 'LessonController@fetchShowCourse');
    Route::post('/class/class-list/students/{lessonId}', 'DetailClassController@viewStudents');
    Route::post('/lesson/search_class', 'LessonController@search')->name('lesson.search')->middleware('auth');

    // ROUTING KHUSUS MENTOR
    Route::group(['middleware' => ['mentor']], function () {


        

        Route::resource('users', UserManagementController::class);
        Route::post('/users/{id}/reset-password', 'UserManagementController@resetPassword')->name('users.resetPassword');

        Route::prefix("lesson")->group(function () {

            Route::get('/{id}/dashboard', 'ClassDashboardController@viewClassDashboard');

            Route::get('category', ['uses' => 'LessonCategoryController@manage']);
            Route::get('category/create', ['uses' => 'LessonCategoryController@create']);
            Route::post('category/store', 'LessonCategoryController@store')->name('lesson_category.store');
            Route::any('category/{id}/delete', 'LessonCategoryController@destroy')->name('lesson_category.destroy');
            Route::get('category/{id}/update', 'LessonCategoryController@update')->name('lesson_category.update');
            Route::post('category/{id}/edit', 'LessonCategoryController@edit')->name('lesson_category.edit');
        });

        Route::get('/lesson/manage', ['uses' => 'LessonController@manage']);
        Route::get('/lesson/manage_v2', ['uses' => 'LessonController@manageV2']);
        Route::get('/lesson/create_v2', ['uses' => 'LessonController@createV2']);
        Route::post('/lesson/create_class', ['uses' => 'LessonController@storeV2']);
        Route::get('/lesson/edit_class/{lesson}', ['uses' => 'LessonController@editClassV2']);
        Route::post('/update-lesson/{lesson}', ['uses' => 'LessonController@updateClassV2']);
        Route::get('/lesson/delete_class/{lesson}', ['uses' => 'LessonController@delete_class_v2']);


        Route::get('/lesson/store', 'LessonController@add');
        Route::get('/lesson/{lesson}/students/', 'LessonController@seeStudent');
        Route::get('/lesson/correct', 'FinalProjectController@correction');
        Route::resource('lesson', LessonController::class);
        Route::resource('section', CourseSectionController::class);
        Route::get('/lesson/{lesson}/section/', 'CourseSectionController@manage_section');
        Route::get('/lesson/manage-materials/{lesson}/', 'CourseSectionController@manage_section_v2');
        Route::get('/lesson/manage-materials/{lesson}/edit/{section_id}', 'CourseSectionController@edit_material_v2')->name('materials.edit');
        Route::post('/update-material/{lesson}', 'CourseSectionController@update_materials')->name('update-material');
        Route::delete('/delete-material/{lesson}/{sectionId}', 'CourseSectionController@delete_materials')->name('materials.delete');

        Route::post('/lesson/create_materials/{lesson}', 'CourseSectionController@store_materials');
        Route::get('lesson/rearrange/{lesson_id}', 'CourseSectionController@rearrange_materials');
        Route::post('/update-order', 'CourseSectionController@updateOrder')->name('update-order');

        Route::get('/lesson/{lessonId}/section/{sectionId}/input-score', 'CourseSectionController@viewInputScore');
        Route::get('/dashboard/mentor', 'LessonController@viewDashboard');
        Route::get('/dashboard/mentor/course/{lesson_id}', 'LessonController@view_courseDashboard');
        // Route::post('/lesson/{lesson}/store/','CourseSectionController@store')->name('section.store');
        // Route::delete('/lesson/{lesson}/section/s','CourseSectionController@destroy')->name('section.delete');
        Route::get('/lesson/{lesson}/section/create', 'CourseSectionController@create_section');
        Route::get('/student/{id}/all-scores', 'ScoreController@seeByStudent');
        Route::post('/update-scores', 'CourseSectionController@updateScores');
        Route::post('/course/submission/scoring', 'FinalProjectController@score')->name('course.submission.scoring');

        //update switch absensi enable-disabled, used by fetch
        Route::post('/update-absensi', 'AbsensiController@updateAbsensi');

        //Absensi
        Route::get('/lesson/{lesson_id}/absensi/{section_id}', 'AbsensiController@manage')->name("absensi.manage");


        Route::prefix("exam")->group(function () {
            Route::get('new', 'MentorExamController@viewCreateNew');
            Route::post('store', 'MentorExamController@storeNewExam');
            Route::post('store/quiz', 'MentorExamController@storeNewExam')->name('store.quiz');
            Route::post('update', 'MentorExamController@updateExam');
            // Route::post('update-question', 'MentorExamController@updateQuestion');
            Route::post('update-question/{examId}/{id}', 'MentorExamController@updateQuestion_v2')->name('exam.updateQuestion_v2');
            Route::post('{examId}/edit/', 'MentorExamController@viewEditExam')->name('exam.update-question');
            Route::post('{examId}/update/', 'MentorExamController@updateExam')->name('update-quiz-new');
            Route::get('manage', 'MentorExamController@viewManageExam');
            Route::get('manage-exam-v2', 'MentorExamController@viewManageExam_v2');
            Route::get('manage-exam-v2/create-exam', 'MentorExamController@viewCreateExam_v2');
            Route::get('manage-exam-v2/{examId}/create-question', 'MentorExamController@viewCreateQuest_v2');
            Route::get('manage-exam-v2/{examId}/edit-question', 'MentorExamController@viewEditQuest_v2')->name('exam.edit-exam_v2');
            // Route::get('manage-exam-v2/{examId}/load-exam', 'MentorExamController@viewLoadExam_v2');
            Route::get('download-exam/{examId}', 'MentorExamController@downloadExam');

            Route::delete('{id}/delete', 'MentorExamController@deleteExam')->name("exam.delete");
            Route::get('{id}/edit', 'MentorExamController@viewEditExam')->name("exam.edit");
            Route::delete('delete-student-in-class/{id}/{lessonId}', 'CourseSectionController@delete_Students')->name("student.delete");

            Route::get('session', 'MentorExamSessionController@viewManageSession');


            Route::prefix("session")->group(function () {
                Route::get('{id}/view', 'MentorExamSessionController@viewDetailSession');
                Route::any('{id}/mquestions', 'MentorExamSessionController@fetchQuestions');
                Route::any('{id}/edit', 'MentorExamSessionController@editSession');
                Route::any('{id}/data', 'MentorExamSessionController@getSessionData');
                Route::any('update', 'MentorExamSessionController@updateSessionData');
            });

            Route::get('{id}/question', 'MentorExamController@viewManageQuestion');
            Route::get('{id}/session', 'MentorExamSessionController@viewManageSession');

            Route::post('store-exam-session', 'MentorExamSessionController@storeSession');
            Route::get('refresh-session-table', 'MentorExamSessionController@getExamSession');
            Route::post('delete-exam-session', 'MentorExamSessionController@destroyExamSession');

            // Route::get('question/{id}/edit', 'MentorExamController@viewEditQuestion');
            // Route::get('question/{id}/edit', 'MentorExamController@viewEditQuestion_v2');
            Route::get('{examId}/question/{id}/edit', 'MentorExamController@viewEditQuestion_v2')->name('edit.question');

            Route::post('question/{id}/delete', 'MentorExamController@deleteQuestion');

            Route::get('{id}/question-order', 'MentorExamController@viewManageQuestionOrder');
            Route::post('save-question', 'MentorExamController@storeQuestion');
            Route::post('save-question-to-db', 'MentorExamController@storeQuestion_v2')->name('store.question');
            Route::post('delete-question-from-db/{id}', 'MentorExamController@deleteQuestion')->name('delete.question');

            Route::any('mquestions', 'MentorExamController@fetchQuestions');
            Route::any('question/edit-questions', 'MentorExamController@fetchQuestions')->name("edit_question");
            Route::any('update-question-order', 'MentorExamController@updateQuestionOrder')->name("update_question_order");
        });
    });


    // ROUTING KHUSUS SUPERADMIN
    Route::group(['middleware' => ['superadmin']], function () {
        Route::prefix("lesson")->group(function () {
            Route::get('/{id}/dashboard', 'ClassDashboardController@viewClassDashboard');
            Route::get('category', ['uses' => 'LessonCategoryController@manage']);
            Route::get('category/create', ['uses' => 'LessonCategoryController@create']);
            Route::post('category/store', 'LessonCategoryController@store')->name('lesson_category.store');
            Route::any('category/{id}/delete', 'LessonCategoryController@destroy')->name('lesson_category.destroy');
            Route::get('category/{id}/update', 'LessonCategoryController@update')->name('lesson_category.update');
            Route::post('category/{id}/edit', 'LessonCategoryController@edit')->name('lesson_category.edit');
        });

        Route::prefix("admin")->group(function () {
            Route::get('manage-mentor', ['uses' => 'AdministratorController@index']);
            Route::get('category/create', ['uses' => 'LessonCategoryController@create']);
            Route::post('category/store', 'LessonCategoryController@store')->name('lesson_category.store');
        });

        Route::prefix('registration-code-management')->group(function () {
            Route::get('/', ['uses' => 'RegistrationCodeController@index']);
            Route::get('/create', ['uses' => 'RegistrationCodeController@create']);
            Route::post('/store', ['uses' => 'RegistrationCodeController@store'])->name('registration_code.store');
            Route::any('/destroy', ['uses' => 'RegistrationCodeController@store'])->name('registration_code.store');
            Route::get('/{id}/edit', [RegistrationCodeController::class, 'edit'])->name('registration_code.edit');
            Route::put('/registration-code/{id}', [RegistrationCodeController::class, 'update'])->name('registration_code.update');
        });
    });


    Route::get('/progress', 'StudentProgressController@startSection');

    Route::prefix("quiz")->group(function () {
        Route::prefix("session")->group(function () {
            Route::get('{id}/initial', 'ExamTakerController@viewInitialTakeSession');
            Route::get('{id}/take-play', 'ExamTakerController@viewInitialTakeSession');
            Route::get('{id}/play', 'ExamTakerController@viewInitialTakeSession');
            Route::get('{id}/view', 'MentorExamSessionController@viewDetailSession');
            Route::any('{id}/mquestions', 'ExamTakerController@fetchQuestions');
            Route::any('save-answers', 'ExamTakerController@saveAnswers');
        });
        Route::any('{id}/mquestions', 'MentorExamSessionController@fetchQuestions');
    });

    // ROUTING KHUSUS STUDENTS
    Route::group(['middleware' => ['student']], function () {

        Route::get('/portfolio/see/{portfolio}/', 'PortfolioController@show')->name('portfolio.show');
        Route::get('/portfolio/create', 'PortfolioController@create')->name('portfolio.create');
        Route::get('/portfolio/manage', 'PortfolioController@manage')->name('portfolio.manage');
        Route::get('/portfolio/{portfolio}/edit', 'PortfolioController@edit')->name('portfolio.edit');
        Route::delete('/portfolio/{portfolio}/destroy', 'PortfolioController@destroy')->name('portfolio.destroy');
        Route::post('/portfolio/store', 'PortfolioController@store')->name('portfolio.store');
        Route::post('/portfolio/{portfolio}/update', 'PortfolioController@update')->name('portfolio.update');

        Route::post('/course/register', 'LessonController@studentRegister')->name('course.register');
        Route::post('/course/drop', 'LessonController@drop')->name('course.drop');
        Route::post('/submission', 'FinalProjectController@see_correction')->name('submission');
        Route::get('/course/{lesson}/submission', 'FinalProjectController@submit_page')->name('course.submission');
        Route::get('/course/{lesson}/certificate', 'FinalProjectController@certificate')->name('course.certificate');
        Route::post('/course/submission/store', 'FinalProjectController@store')->name('course.submission.store');
        // Route::post('/lesson/student/search_class', 'ClassListController@search')->name('lessonStudent.search')->middleware('auth');

    });
});

Route::get('/lesson/{lesson}', 'LessonController@show')->name('lesson.show');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/blog/create', ['uses' => 'BlogController@create'])->middleware('auth');
    Route::get('/blog/manage', ['uses' => 'BlogController@index'])->middleware('auth');
    Route::get('/blog/store', 'BlogController@add')->middleware('auth');
});


Route::post('/qr-login', [QRLoginController::class, 'processQRCodeLogin'])->name('qr.login');
Route::resource('blog', BlogController::class);
Route::get('/blog/{blog}', 'BlogController@show')->name('blog.read');


Route::group(['prefix' => 'filemanager'], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});



Route::get('/generate-qr-code', [QRLoginController::class, 'generateQRCode'])->name('generate.qrcode');
Route::post('/check-qr-jwt', 'QRLoginController@checkToken')->name('checkQrLoginJWT');


Auth::routes();
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
