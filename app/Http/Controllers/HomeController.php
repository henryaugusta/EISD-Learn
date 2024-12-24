<?php

namespace App\Http\Controllers;

use App\Helper\MyHelper;
use App\Models\ExamSession;
use App\Models\ExamTaker;
use App\Models\StudentSection;
use App\Models\Exam;
use App\Models\StudentLesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

        if (!Auth::check()) {
            return Redirect::away("/");
        }

        $departments = DB::connection('ithub')
            ->table('m_departments')
            ->select('id', 'code', 'name')
            ->where('code', 'like', '%_NEW%')
            ->get();
        // return response()->json($departments);
        // return response()->json($departments);

        $averageScoreSubquery = DB::table('student_section')
            ->select('student_id', DB::raw('AVG(score) as average_score'))
            ->groupBy('student_id');


        $leaderboardQuery = DB::table(DB::raw('student_section ss'))
            ->select(
                'u.name as student_name',
                'u.profile_url',
                'u.id',
                DB::raw('SUM(ss.score) as total_score'),
                'avg_subquery.average_score as average_score',
                DB::raw('(SELECT MAX(score) FROM student_section WHERE student_id = u.id) as highest_score'),
                DB::raw('(SELECT MIN(score) FROM student_section WHERE student_id = u.id) as lowest_score')
            )
            ->join('users as u', 'ss.student_id', '=', 'u.id')
            ->joinSub($averageScoreSubquery, 'avg_subquery', 'u.id', '=', 'avg_subquery.student_id')
            ->groupBy('ss.student_id', 'u.name', 'avg_subquery.average_score')
            ->orderByDesc('total_score');


        $topThreeQuery = array();
        $studentSectionCount = StudentSection::all()->count();
        if ($studentSectionCount > 0) {
            $topThreeQuery = DB::table(DB::raw('student_section ss'))
                ->select(
                    'u.name as student_name',
                    'u.profile_url',
                    DB::raw('SUM(ss.score) as total_score'),
                    DB::raw('(SELECT MAX(score) FROM student_section WHERE student_id = u.id) as lowest_score'),
                    DB::raw('(SELECT MIN(score) FROM student_section WHERE student_id = u.id) as highest_score')
                )
                ->join('users as u', 'ss.student_id', '=', 'u.id')
                ->groupBy('ss.student_id', 'u.name')
                ->orderByDesc('total_score')->get();
        }

        // Check if the authenticated user's role is "mentor"
        if (Auth::user()->role != "mentor") {
            $leaderboardQuery->limit(5);
        }

        $topThree = $topThreeQuery;

        $leaderboard = array();
        if ($studentSectionCount > 0) {
            $leaderboard = $leaderboardQuery->get();
        }

        if (Auth::check() && Auth::user()->role == 'mentor') {
            $userId = Auth::user()->id;
            $user_name = Auth::user()->name;
            $classes = DB::select("select * from view_course where mentor_id = $userId");
            $classRegistered = DB::select("SELECT * from view_student_lesson where student_id=$userId");
            $projectCreatedCount = DB::table('view_project')
                ->where('user_id', $userId)
                ->count();

            $classRegisteredCount = DB::table('lessons')
                ->where('mentor_id', $userId)
                ->whereNull('deleted_at')
                ->count();


            // $studentCount = DB::table('view_student_lesson')
            //     ->where('mentor_name', $user_name)
            //     ->count();

            $studentLessonsWithMentor = DB::table('student_lesson')
                ->leftJoin('lessons', 'student_lesson.lesson_id', '=', 'lessons.id')
                ->select('student_lesson.*', 'lessons.mentor_id')
                ->where('lessons.mentor_id', $userId)
                ->whereNull('lessons.deleted_at')
                ->count();
            // return $studentLessonsWithMentor;


            $data_studentProgress = [];

            $tabel_lesson = DB::table('lessons')
                ->whereNull('lessons.deleted_at')
                ->where('lessons.mentor_id', $userId)
                ->get();


            foreach ($tabel_lesson as $data_lesson) {
                $obj_class = new \stdClass();;

                $obj_class->naxme = $data_lesson->course_title;
                $obj_class->completedCount = 0;
                $obj_class->incompleteCount = 0;

                $studentCountInClass = StudentLesson::where('lesson_id', '=', $data_lesson->id)->count();
                $obj_class->studentCount = $studentCountInClass;

                $completedCount = StudentLesson::where('learn_status', '=', '1')
                    ->where('lesson_id', '=', $data_lesson->id)->count();

                $obj_class->completedCount = $completedCount;
                $obj_class->unfinishedCount = $studentCountInClass - $completedCount;
                array_push($data_studentProgress, $obj_class);
            }

            $countCourseInProgress = 0;
            $countCourseCompleted = 0;

            foreach ($data_studentProgress as $studentProgress) {
                if ($studentProgress->studentCount == $studentProgress->completedCount && $studentProgress->studentCount > 0) {
                    $countCourseCompleted++;
                } else {
                    $countCourseInProgress++;
                }
            }

            $obj_dataProgress = new \stdClass();
            $obj_dataProgress->completedClass = $countCourseCompleted;
            $obj_dataProgress->inprogress = $countCourseInProgress;


            // Retrieve all records of exams taken by students
            $takenExamCourseSection = ExamTaker::all();


            $locationId = $request->input('location') ?? "all";
            $departmentId = $request->input('department') ?? "all";
            $month = $request->input('month') ?? "all";

            $results = ExamTaker::selectRaw('
                    e.title as exam_title,
                    es.id AS exam_session_id,
                    AVG(et.current_score) AS avg_score
                ')
                ->from('exam_takers as et')
                ->leftJoin('exam_sessions as es', 'et.session_id', '=', 'es.id')
                ->leftJoin('exams as e', 'e.id', '=', 'es.exam_id')
                ->leftJoin('users as u', 'et.user_id', '=', 'u.id')
                ->where('e.created_by', '=', $userId)
                ->where(function ($query) {
                    $query->where('e.is_deleted', '!=', 'y')
                        ->orWhereNull('e.is_deleted')
                        ->orWhere('e.is_deleted', '');
                })
                ->where(function ($query) use ($month) {
                    if ($month !== 'all') {
                        $query->whereRaw('MONTH(es.created_at) = ?', [$month]);
                    }
                })
                ->where(function ($query) use ($departmentId) {
                    if (!empty($departmentId)) {
                        if ($departmentId != "all") {
                            $query->where('u.department_id', '=', $departmentId);
                        }
                    }
                })
                ->where(function ($query) use ($locationId) {
                    if (!empty($locationId)) {
                        if ($locationId !== 'all') {
                            $query->whereJsonContains('u.location', ['site_id' => $locationId]);
                        } else {
                            return $query;
                        }
                    }
                })
                ->groupBy('es.id')
                ->orderByDesc('es.created_at')
                // ->limit(3)
                ->get();

            $averageScoreArray = [];

            foreach ($results as $score) {
                $averageScoreArray[] = [
                    'exam_session_id' => $score->exam_session_id,
                    'title_exam' => $score->exam_title,
                    'average_score' => $score->avg_score
                ];
            }

            // return $averageScoreArray;


            $learnStatus = request('learn_status');

            if($learnStatus == null || $learnStatus == ''){
                $learnStatus = 'finished';
            }



            $userLMS = DB::connection('mysql')
                ->table('users')
                ->select('mdln_username', 'name', 'users.department_id')
                ->where('role', '=', 'student')
                ->where(function ($query) {
                    $query->where('users.is_testing', '=', 'n')
                          ->orWhere('users.is_testing', '=', '')
                          ->orWhere('users.is_testing', '!=', 'y')
                          ->orWhereNull('users.is_testing');
                })
                ->where(function ($query) use ($locationId) {
                    if (!empty($locationId)) {
                        if ($locationId !== 'all') {
                            $query->whereJsonContains('location', ['site_id' => $locationId]);
                        }
                    }
                })
                ->where(function ($query) use ($departmentId) {
                    if (!empty($departmentId)) {
                        if ($departmentId != "all") {
                            $query->where('users.department_id', '=', $departmentId);
                        }
                    }
                })
                ->leftJoin('student_lesson', 'users.id', '=', 'student_lesson.student_id') // Join with student_lesson table
                ->leftJoin('lessons', 'lessons.id', '=', 'student_lesson.lesson_id') // Join with student_lesson table
                ->where(function ($query) use ($learnStatus) {
                    if (!empty($learnStatus) && $learnStatus !== 'all') {
                        if ($learnStatus === 'finished') {
                            $query->where('student_lesson.learn_status', '=', 1);
                        } elseif ($learnStatus === 'not_finished') {
                            $query->where('student_lesson.learn_status', '=', 0);
                        }
                    }
                })
                ->where(function ($query) {
                    // Check that the lesson is not deleted
                    $query->whereNull('lessons.deleted_at');
                })
                ->get();


            // department for pie chart
            $departments = DB::connection('ithub')
                ->table('m_departments')
                ->select('id', 'code', 'name')
                // ->where('code', 'like', '%_NEW%')
                ->get();

            //Department for filter latest post test
            $departmentsForFilter = DB::connection('ithub')
                ->table('m_departments')
                ->select('id', 'code', 'name')
                ->where('code', 'like', '%_NEW%')
                ->get();

            $locations = DB::connection('ithub')
                ->table('m_unit_businesses')
                ->select('id', 'code', 'name')
                ->get();

            $users_departments = $userLMS->map(function ($userLMS) use ($departments) {
                $userLMS->department = $departments->firstWhere('id', $userLMS->department_id);
                return $userLMS;
            });

            // Kelompokkan berdasarkan nama departemen
            $groupedByDepartment = $users_departments->groupBy(function ($userLMS) {
                return $userLMS->department->name ?? 'Undefined Department';
            })->map(function ($group) {
                return $group->count();
            });


            $myStudent = DB::select("select * from view_student_lesson where mentor_name = '$user_name' ");

            if (!Auth::check()) {
                return Redirect::away('/'); // Replace '/login' with the URL of your login page
            }

            MyHelper::addAnalyticEvent(
                "Buka Dashboard Mentor",
                "Dashboard"
            );


            $compact = compact(
                'groupedByDepartment',
                'classes',
                'classRegistered',
                'locations',
                'departments',
                'departmentsForFilter',
                'leaderboard',
                'studentLessonsWithMentor',
                'myStudent',
                'topThree',
                'countCourseInProgress',
                'countCourseCompleted',
                'projectCreatedCount',
                'classRegisteredCount',
                'averageScoreArray'
            );

            if ($request->dump == true) {
                return $compact;
            };

            return view('main.dashboard')
                ->with($compact);
        } 
        else if (Auth::check() && Auth::user()->role == 'student') {

            $currentMonth = date('n'); // Numeric representation of the current month (1-12)
            $currentYear = date('Y');  // Full numeric representation of the current year (e.g., 2024)



            $userId = Auth::id();
            $blog = DB::select("select * from view_blog where user_id = $userId ");
            $month = $request->input('month') ?? $currentMonth;
            $year = $request->input('year') ?? $currentYear;


            // Read Department
            $id_dept = Auth::user()->department_id;
            $query_department = DB::connection('ithub')
                ->table('m_departments')
                ->select('name')
                ->where('id', '=', $id_dept)
                ->get();
            $value_dept = "";
            if ($query_department && count($query_department) > 0) {
                $department = json_decode($query_department, true);
                // Periksa apakah hasil decode adalah array dan memiliki key 'name'
                if (is_array($department) && isset($department)) {
                    $value_dept = $department[0]['name'] ?? null; // Mengembalikan nilai dari key 'name'
                } else {
                    $value_dept =  "-";
                }
            } else {
                $value_dept = "-";
            }
            $name_dept = $value_dept;


            $userScores = DB::table('student_section')
                ->join('course_section', 'student_section.section_id', '=', 'course_section.id')
                ->select('course_section.section_title', DB::raw('COALESCE(student_section.score, 0) as score'))
                ->where('student_section.student_id', $userId)
                ->orderBy('course_section.section_order')
                ->get();


            $classes = DB::select("select * from view_course");
            $classRegistered = DB::select("SELECT * from view_student_lesson where student_id=$userId");
            $projectCreatedCount = DB::table('view_project')
                ->where('user_id', $userId)
                ->count();

            $classRegisteredCount = DB::table('view_student_lesson')
                ->leftJoin('lessons', 'view_student_lesson.id', '=', 'lessons.id')
                ->where('view_student_lesson.student_id', $userId)
                ->whereNull('lessons.deleted_at')
                ->count();

            $activeCourse = DB::table('student_lesson')
                ->leftJoin('lessons', 'student_lesson.lesson_id', '=', 'lessons.id')
                ->leftJoin('users', 'student_lesson.student_id', '=', 'users.id')
                ->select('student_lesson.student_id', 'users.name', 'student_lesson.lesson_id', 'student_lesson.learn_status', 'lessons.course_title')
                ->where('student_id', $userId)
                ->where('learn_status', 0)
                ->whereNull('lessons.deleted_at')
                // ->where('users.is_testing', '=', 'n')
                ->count();

            $completedCourse = DB::table('student_lesson')
                ->leftJoin('lessons', 'student_lesson.lesson_id', '=', 'lessons.id')
                ->leftJoin('users', 'student_lesson.student_id', '=', 'users.id')
                ->select('student_lesson.student_id', 'users.name', 'student_lesson.lesson_id', 'student_lesson.learn_status', 'lessons.course_title')
                ->where('student_id', $userId)
                ->where('learn_status', 1)
                ->whereNull('lessons.deleted_at')
                // ->where('users.is_testing', '=', 'n')
                ->count();

            $monthly_CompletedCourse = DB::table('student_lesson')
                ->leftJoin('lessons', 'student_lesson.lesson_id', '=', 'lessons.id')
                ->leftJoin('users', 'student_lesson.student_id', '=', 'users.id')
                ->select(DB::raw('YEAR(student_lesson.finished_at) as year, MONTH(student_lesson.finished_at) as month, COUNT(*) as completed_count'))
                ->where('student_lesson.student_id', $userId)
                ->where('student_lesson.learn_status', 1)
                ->where('lessons.is_visible', 'y')
                // ->where('users.is_testing', '=', 'n')
                ->whereNull('lessons.deleted_at')
                ->groupBy(DB::raw('YEAR(student_lesson.finished_at), MONTH(student_lesson.finished_at)'))
                ->get();

            // return $completedCourse;


            $completedCourse_monthly = [];

            // Loop untuk mengisi data per bulan
            for ($i = 1; $i <= 12; $i++) {
                $found = false;
                foreach ($monthly_CompletedCourse as $course) {
                    if ($course->month == $i) {
                        // Simpan data per bulan sebagai objek
                        $completedCourse_monthly[$i] = (object) [
                            "year" => $course->year,
                            "month" => $course->month,
                            "completed_count" => $course->completed_count
                        ];
                        $found = true;
                        break;
                    }
                }
                // Jika tidak ada data untuk bulan tersebut, definisikan sebagai 0
                if (!$found) {
                    $completedCourse_monthly[$i] = (object) [
                        "year" => date("Y"),
                        "month" => $i,
                        "completed_count" => 0
                    ];
                }
            }

            // Sortir array berdasarkan bulan
            ksort($completedCourse_monthly);

            // Setelah data disiapkan, Anda dapat mengakses properti month dengan benar
            $jan = $completedCourse_monthly[1];
            $feb = $completedCourse_monthly[2];
            $mar = $completedCourse_monthly[3];
            $apr = $completedCourse_monthly[4];
            $mei = $completedCourse_monthly[5];
            $jun = $completedCourse_monthly[6];
            $jul = $completedCourse_monthly[7];
            $agt = $completedCourse_monthly[8];
            $sep = $completedCourse_monthly[9];
            $okt = $completedCourse_monthly[10];
            $nov = $completedCourse_monthly[11];
            $des = $completedCourse_monthly[12];



            $blogCreatedCount = DB::table('view_blog')
                ->where('user_id', $userId)
                ->count();


            $userID = Auth::id();
            $myClasses = DB::select("
                SELECT *,
                       'new_attribute_value' AS new_attribute  -- Add your new attribute here
                FROM (
                    SELECT
                        RANK() OVER (PARTITION BY a.id ORDER BY cs.id ASC) AS ranking,
                        a.*,
                        lc.color_of_categories as course_category_color,
                        lc.name as course_category_name,
                        b.name AS mentor_name,
                        b.profile_url,
                        COUNT(c.student_id) AS num_students_registered,
                        CASE WHEN COUNT(c.student_id) > 0 THEN 1 ELSE 0 END AS is_registered
                    FROM
                        lessons a
                    LEFT JOIN
                        users b ON a.mentor_id = b.id
                    LEFT JOIN
                        student_lesson c ON a.id = c.lesson_id
                    LEFT JOIN
                        course_section cs ON a.id = cs.course_id
                    LEFT JOIN
                        lesson_categories lc on a.category_id = lc.id
                    WHERE EXISTS (
                            SELECT 1
                            FROM student_lesson sl
                            WHERE sl.lesson_id = a.id
                            AND sl.student_id = $userID
                        )
                    AND  a.deleted_at IS  NULL
                    GROUP BY
                        a.id, b.name, b.profile_url, cs.id
                ) AS main_table
                WHERE main_table.ranking = 1;
            ");


            // Append new attribute to each row
            foreach ($myClasses as &$class) {
                $firstSection = DB::table('course_section')
                    ->where('course_id', '=', $class->id)
                    ->orderByRaw("CAST(section_order AS UNSIGNED), section_order ASC")
                    ->first();
                // Tambahkan pengecekan untuk memastikan $firstSection tidak null
                if ($firstSection !== null) {
                    $class->first_section = $firstSection->id;
                } else {
                    // Handle jika $firstSection null
                    $class->first_section = null; // Atau nilai default lainnya sesuai kebutuhan
                }
            }

            // $lessonCategories = DB::table('lesson_categories')->get()->keyBy('name');
            $lessonCategories = DB::table('lesson_categories')
                ->select('id', 'name', 'color_of_categories')
                ->get();


            //============== FOR THE SCORE CHART =========================
            // Fetch all the exam taker data with scores
            $examTakerData = DB::table('exam_takers as et')
                ->select(
                    'et.user_id as userID',
                    'et.finished_at as time_finish',
                    'et.session_id as exam_SessionId',
                    'es.exam_type as examType',
                    'exm.title as title_exam',
                    'cs.section_title as materiExam',
                    'et.course_flag as courseID',
                    'et.course_section_flag as courseSectionID',
                    'et.current_score' // Include the score column
                )
                ->leftJoin('exam_sessions as es', 'et.session_id', '=', 'es.id')
                ->leftJoin('exams as exm', 'es.exam_id', '=', 'exm.id')
                ->leftJoin('course_section as cs', 'et.course_section_flag', '=', 'cs.id')
                ->where('et.user_id', $userID)
                ->whereNotNull('et.finished_at')
                ->where('et.is_finished', "y")
                ->where('es.exam_type', 'Post Test');

            // Add month and year filters if needed
            if ($month !== 'all') {
                $examTakerData->whereRaw('MONTH(et.created_at) = ?', [$month]);
            }
            if ($year !== 'all') {
                $examTakerData->whereRaw('YEAR(et.created_at) = ?', [$year]);
            }

            // Get the exam taker data
            $examTakerRecords = $examTakerData->get();


            // Group by courseSectionID and determine the highest score for each section
            $highestScores = [];

            foreach ($examTakerRecords as $item) {
                $courseSectionID = $item->courseSectionID;

                // Check if this courseSectionID is already processed
                if (!isset($highestScores[$courseSectionID]) || $item->current_score > $highestScores[$courseSectionID]->current_score) {
                    // Update the highest score for this courseSectionID
                    $highestScores[$courseSectionID] = $item;
                }
            }

            // Convert the associative array to a simple array with unique courseSectionID
            $postTestScore = array_values($highestScores);



            MyHelper::addAnalyticEvent(
                "Buka Dashboard Student",
                "Dashboard"
            );


            return view('main.dashboard')
                ->with(
                    compact(
                        'classes',
                        'myClasses',
                        'userID', 'name_dept',
                        'classRegistered',
                        'blog',
                        'userScores',
                        'topThree',
                        'leaderboard',
                        'blogCreatedCount',
                        'projectCreatedCount',
                        'classRegisteredCount',
                        'activeCourse',
                        'completedCourse',
                        'lessonCategories',
                        'jan',
                        'feb',
                        'mar',
                        'apr',
                        'mei',
                        'jun',
                        'jul',
                        'agt',
                        'sep',
                        'okt',
                        'nov',
                        'des',
                        'postTestScore'
                    )
                );
        }
        else if(Auth::check() && Auth::user()->role == 'superadmin'){
            $userId = Auth::user()->id;
            $user_name = Auth::user()->name;
            if (!Auth::check()) {
                return Redirect::away('/'); // Replace '/login' with the URL of your login page
            }

            MyHelper::addAnalyticEvent(
                "Buka Dashboard Mentor",
                "Dashboard"
            );


            $compact = compact(
                'user_name'
            );

            if ($request->dump == true) {
                return $compact;
            };

            return view('main.dashboard')
                ->with($compact);
        }
    }
}
