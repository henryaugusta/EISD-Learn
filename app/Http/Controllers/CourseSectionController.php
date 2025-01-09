<?php

namespace App\Http\Controllers;

use App\Helper\MyHelper;
use App\Models\CourseSection;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamTaker;
use App\Models\Lesson;
use App\Models\LessonCategory;
use App\Models\StudentLesson;
use App\Models\StudentSection;
use App\Models\User;
use File;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;
use stdClass;

class CourseSectionController extends Controller
{




    public function manage_section(Request $request, Lesson $lesson)
    {

        $user_id = Auth::id();
        $lesson_id = $lesson->id;
        $examSessions = ExamSession::where(function ($query) {
            $query->whereNull('is_deleted')
                ->orWhere('is_deleted', '<>', 'y');
        })->get();
        // return dd($examSessions->all());
        if ($user_id != $lesson->mentor_id) {
            abort(401, 'Unauthorized');
        }
        $dayta = DB::table('course_section as c')
            ->select(
                'a.id as lesson_id',
                'a.course_title as lessons_title',
                'a.mentor_id',
                'b.name as mentor_name',
                'c.id as section_id',
                'c.quiz_session_id',
                'c.duration_take',
                'c.section_order',
                'c.section_title',
                'c.section_content',
                'c.section_video',
                'c.created_at',
                'c.updated_at',
                'c.can_be_accessed'
            )
            ->leftJoin('lessons as a', 'a.id', '=', 'c.course_id')
            ->leftJoin('users as b', 'a.mentor_id', '=', 'b.id')
            ->where('a.id', $lesson_id)
            ->orderBy('c.section_order', 'ASC')
            ->get();
        $compact = compact('dayta', 'examSessions', 'lesson');
        // dd($compact) ;
        // return dd($examSessions);
        if ($request->dump == true) {
            return $compact;
        }
        // return dd($dayta);
        return view('lessons.section.manage_section', $compact);
    }

    public function manage_section_v2(Request $request, Lesson $lesson)
    {
        $lesson_id = $lesson->id;
        $dayta = DB::table('course_section as c')
            ->select(
                'a.id as lesson_id',
                'a.course_title as lessons_title',
                'a.mentor_id',
                'b.name as mentor_name',
                'c.id as section_id',
                'c.quiz_session_id',
                'c.duration_take',
                'c.section_order',
                'c.section_title',
                'c.section_content',
                'c.section_video',
                'c.created_at',
                'c.updated_at',
                'c.can_be_accessed'
            )
            ->leftJoin('lessons as a', 'a.id', '=', 'c.course_id')
            ->leftJoin('users as b', 'a.mentor_id', '=', 'b.id')
            ->where('a.id', $lesson_id)
            ->orderBy(DB::raw('CAST(c.section_order AS UNSIGNED)'), 'ASC')
            ->get();

        $studentsInfo = DB::table('student_section as ss')
            ->select('ss.student_id', 'cs.id')
            ->leftJoin('course_section as cs', 'ss.section_id', '=', 'cs.id')
            ->where('cs.course_id', $lesson_id)
            ->get();


        if (count($studentsInfo) != 0) {
            $student_info = 'Ready-on-Student-Section';
        } else {
            $student_info = null;
        }

        // return $student_info;

        $examSessions = ExamSession::select('exam_sessions.*', 'exams.title as title')
            ->leftJoin('exams', 'exam_sessions.exam_id', '=', 'exams.id')
            ->where("exams.created_by", Auth::id())
            ->where(function ($query) {
                $query->where('exams.is_deleted', '!=', 'y')
                    ->orWhereNull('exams.is_deleted');
            })
            ->get();

        // return $dayta;

        $compact = compact('dayta', 'lesson_id', 'examSessions', 'student_info');
        return view('lessons.manage_materials', $compact);
    }

    public function store_materials(Request $request, Lesson $lesson)
    {
        $this->validate($request, [
            'content_area' => 'required|string',
        ], [
            'content_area.required' => 'Deskripsi Kelas wajib diisi.',
        ]);



        $lessonId = $request->lessonId;

        $insert_to_CourseSection = new CourseSection();

        ini_set('upload_max_filesize', '500M');
        ini_set('post_max_size', '500M');
        $materials = $request->file('question_images');

        if ($materials) {
            // Upload new video
            if ($materials != null) {
                $image = $request->file('question_images');
                $imagePath = "course-s3/$lessonId" . $image->hashName();
                Storage::disk('s3')->put($imagePath, file_get_contents($image));
                $insert_to_CourseSection->section_video = $imagePath;
            } else {
                $insert_to_CourseSection->section_video = "";
            }
        } else {
            $insert_to_CourseSection->section_video = "";
        }

        $lastSectionOrder = $insert_to_CourseSection ->where('course_id', $lessonId) ->max('section_order');

        $insert_to_CourseSection->section_title = $request->title;
        if($lastSectionOrder != ''){
            $insert_to_CourseSection->section_order = $lastSectionOrder + 1;
        }else{
            $insert_to_CourseSection->section_order = '';
        }
        
        $insert_to_CourseSection->section_content = $request->content_area;
        $insert_to_CourseSection->course_id = $request->lessonId;
        $insert_to_CourseSection->can_be_accessed = $request->is_access;
        $insert_to_CourseSection->quiz_session_id = $request->is_examId;
        $insert_to_CourseSection->embedded_file = $request->embeded_file;
        // dd($insert_to_CourseSection);
        $insert_to_CourseSection->save();


        if ($insert_to_CourseSection) {
            //redirect dengan pesan sukses
            return redirect("/lesson/manage-materials/$request->lessonId")->with(['success' => 'Materi Berhasil Ditambahkan!']);
        } else {
            //redirect dengan pesan error
            return redirect("/lesson/manage-materials/$request->lessonId")->with(['error' => 'Materi Gagal Ditambahkan!']);
        }
    }

    public function edit_material_v2($lesson_id, $section_id)
    {
        $examSessions = ExamSession::select('exam_sessions.*', 'exams.title as title')
            ->leftJoin('exams', 'exam_sessions.exam_id', '=', 'exams.id')
            ->where(function ($query) {
                $query->where('exams.is_deleted', '!=', 'y')
                    ->orWhereNull('exams.is_deleted');
            })
            ->get();

        $data_course_section_to_edit = CourseSection::findOrFail($section_id);
        $compact = compact('lesson_id', 'examSessions', 'section_id', 'data_course_section_to_edit');
        return view('lessons.edit_materials', $compact);
    }

    public function update_materials(Request $request)
    {
        $section_id = $request->sectionId;
        $lesson_id = $request->lessonId;
        $lessonId = $lesson_id;
        $update_to_CourseSection = CourseSection::findOrFail($section_id);
        $materials = $request->file('question_images');

        if ($materials != null) {
            Storage::disk('s3')->delete("profile-s3/$lessonId");
            $image = $request->file('question_images');
            $imagePath = "course-s3/$lessonId" . $image->hashName();
            Storage::disk('s3')->put($imagePath, file_get_contents($image));
            $update_to_CourseSection->section_video = $imagePath;
        }

        $update_to_CourseSection->section_title = $request->update_title;
        $update_to_CourseSection->section_content = $request->update_content;
        $update_to_CourseSection->course_id = $request->lessonId;
        $update_to_CourseSection->can_be_accessed = $request->update_is_access;
        $update_to_CourseSection->quiz_session_id = $request->update_is_examId;
        $update_to_CourseSection->embedded_file = $request->embeded_file;

        if ($request->update_content == "" || $request->update_content == null) {
            $update_to_CourseSection->section_content = "";
        }
        // dd($update_to_CourseSection);
        $update_to_CourseSection->save();


        if ($update_to_CourseSection) {
            //redirect dengan pesan sukses
            return redirect("/lesson/manage-materials/$lesson_id")->with(['success' => 'Materi Berhasil DiPerbaharui!']);
        } else {
            //redirect dengan pesan error
            return redirect("/lesson/manage-materials/$lesson_id")->with(['error' => 'Materi Gagal DiPerbaharui!']);
        }
    }

    public function delete_materials($lessonId, $sectionId)
    {
        CourseSection::where('id', $sectionId)->where('course_id', $lessonId)->delete();
        return back()->with(['success' => 'Materials Deleted Successfully']);
    }

    public function rearrange_materials(Request $request, Lesson $lesson, $lesson_id)
    {
        $dayta = DB::table('course_section as c')
            ->select(
                'a.id as lesson_id',
                'a.course_title as lessons_title',
                'a.mentor_id',
                'b.name as mentor_name',
                'c.id as section_id',
                'c.quiz_session_id',
                'c.duration_take',
                DB::raw('CAST(c.section_order AS UNSIGNED) AS section_order'), // Cast section_order as integer
                'c.section_title',
                'c.section_content',
                'c.section_video',
                'c.created_at',
                'c.updated_at',
                'c.can_be_accessed'
            )
            ->leftJoin('lessons as a', 'a.id', '=', 'c.course_id')
            ->leftJoin('users as b', 'a.mentor_id', '=', 'b.id')
            ->where('a.id', $lesson_id)
            ->orderBy(DB::raw('CAST(c.section_order AS UNSIGNED)'), 'ASC') // Order by the casted integer value
            ->get();

        // dd($dayta);
        $compact = compact('dayta', 'lesson_id');
        return view('lessons.manage_materials_order', $compact);
    }

    public function updateScores(Request $request)
    {
        $scoresData = $request->input('scores');

        foreach ($scoresData as $studentSectionId => $score) {
            // Assuming you have a StudentSection model
            $studentSection = StudentSection::find($studentSectionId);
            if ($studentSection) {
                $studentSection->score = $score;
                $studentSection->save();
            }
        }

        return response()->json(['message' => 'Scores updated successfully']);
    }

    public function viewInputScore(Request $request, $lessonId, $sectionId)
    {
        $lesson = Lesson::findOrFail($lessonId);
        $section = CourseSection::findOrFail($sectionId);


        $students = DB::table('student_section')
            ->select([
                'student_section.id as student_section_id',
                'student_section.student_id',
                'student_section.score',
                'lessons.course_title',
                'lessons.id as course_id',
                'users.name as student_name',
                'users.email as student_email',
                'users.profile_url as student_profile_url',
                'course_section.section_title',
                'course_section.section_order',
                'course_section.id as section_id',
                'student_section.created_at as taken_at',
            ])
            ->leftJoin('users', 'student_section.student_id', '=', 'users.id')
            // ->where('users.is_testing', '=', 'n')
            ->leftJoin('course_section', 'student_section.section_id', '=', 'course_section.id')
            ->leftJoin('lessons', 'course_section.course_id', '=', 'lessons.id')
            ->where('course_section.id', $sectionId)
            ->get();


        $userAttempts = DB::table('exam_takers')
            ->select('users.name as user_name', 'users.email as user_email', 'exam_takers.user_id', DB::raw('COUNT(*) as attempt'))
            ->selectRaw('MAX(exam_takers.current_score) as last_score')
            ->selectRaw('MAX(exam_takers.finished_at) as finished_at')
            ->join('users', 'exam_takers.user_id', '=', 'users.id')
            ->where('exam_takers.course_section_flag', $sectionId)
            ->groupBy('exam_takers.user_id', 'users.name')
            ->get();


        $isHaveExam = false;
        $isHaveSession = false;
        $examSessionId = $section->quiz_session_id;
        $examSessionTitle = "";
        $examTitle = "";
        $examSession = null;
        $exam = null;
        if ($examSessionId != null) {
            $examSession = ExamSession::find($examSessionId);
            if ($examSession != null) {
                $examSessionTitle = $examSession->title;
                $exam = Exam::find($examSession->exam_id);

                if ($exam != null) {
                    $examTitle = $exam->title;
                    $isHaveExam = true;
                }
                $isHaveSession = true;
                $examSessionTitle = $examSession->title;
            }
        }

        $compact = compact(
            'isHaveExam',
            'isHaveSession',
            'exam',
            'examTitle',
            'examSession',
            'examSessionTitle',
            'students',
            'lesson',
            'userAttempts'
        );
        if ($request->dump == true) {
            return $compact;
        }
        return view('lessons.section.see_score', $compact);
    }


    public function goToNextSection(Lesson $lesson, CourseSection $lesson_id) {}

    public function publicExam($examId, Request $request)
    {
        $questions = [];
        $isExam = false;
        $title = "";


        $examSession = null;
        $exam = null;
        $question_count = 0;
        $totalScore = 0;
        $session = null;

        $isExam = true;
        $exam = Exam::find($examId);
        $examSession = ExamSession::where('exam_id', '=', $examId)->first();
        $session = $examSession;
        $sessionId = $session->id;
        $questions = json_decode($session->questions_answers);
        $totalScore = 0;
        $title = $exam->title;
        if ($questions != null) {
            foreach ($questions as $question) {
                if (isset($question->choices)) {
                    $choices = json_decode($question->choices, true);

                    foreach ($choices as $choice) {
                        if (isset($choice['score']) && $choice['score'] !== null && $choice['score'] >= 0) {
                            $totalScore += (int) $choice['score'];
                        }
                    }
                }
            }
        }

        if ($questions != null) {
            $question_count = count($questions);
        }
        // Check if student has taken any exam on this session
        $hasTakenAnyExam = false;
        $examResults = ExamTaker::where("session_id", "=", $sessionId)->get();

        if (count($examResults) > 0) {
            $hasTakenAnyExam = true;
        }

        $examTokenKey = 'exam_token_' . $examId;

        if (!session()->has($examTokenKey)) {
            session([$examTokenKey => Str::uuid()->toString()]);
        }
        $examToken = session($examTokenKey);

        // return $examSession;

        $compact = compact(
            'hasTakenAnyExam',
            'examResults',
            'examToken',
            'isExam',
            'title',
            'questions',
            'examSession',
            'exam',
            'session',
            'question_count',
            'totalScore',
        );

        if ($request->dump == true) {
            return $compact;
        }

        MyHelper::addAnalyticEvent(
            "Mengerjakan Public Exam",
            "Exam"
        );


        return view('lessons.play.course_play_public_exam', $compact);
    }


    // SEE SECTION
    public function seeSection(Request $request, $lessonId, $sectionId)
    {
        // Find the next and previous sections
        $nextSectionId = null;
        $prevSectionId = null;

        $section = CourseSection::findOrFail($sectionId);
        // $lesson = Lesson::findOrFail($lessonId);
        $lesson = Lesson::leftJoin('users', 'lessons.mentor_id', '=', 'users.id')
        ->select('lessons.*', 'users.name as mentor_name')
        ->where('lessons.id', $lessonId)
        // ->where('users.is_testing', '=', 'n')
        ->firstOrFail();


        $currentSectionId = $section->id;
        $questions = [];
        $currentSection = CourseSection::findOrFail($currentSectionId);
        $isExam = false;
        $title = "";
        if (!Auth::check()) {
            MyHelper::addAnalyticEvent(
                "Belum Login Buka Section",
                "Course Section"
            );
            abort(401, "Anda Harus Login Untuk Melanjutkan " . $lesson->name);
        }

        $user_id = Auth::user()->id;
        $lessonId = $lesson->id;
        $isRegistered = false;
        if (Auth::user()->role == "student") {
            $student_lesson = DB::table('student_lesson')
                ->where('student-lesson', "$user_id-$lessonId")
                ->get()
                ->toArray();

            $isRegistered = false;
            if ($student_lesson == null) {
                abort(401, "Anda Belum Mendaftar ke Kelas " . $lesson->name);
            } else {
                $isRegistered = true;
            }
        }


        $lessonObject = Lesson::findOrFail($lessonId);
        if (Auth::user()->role == "student") {
            if ($lessonObject->can_be_accessed == "n") {
                MyHelper::addAnalyticEvent(
                    "Reject Section Diluar Jadwal",
                    "Course Section"
                );
                abort(401, "Kelas ini hanya bisa diakses pada jadwal yang telah ditentukan ");
            }
        }

        // Get the preceding sections
        $precedingSections = DB::table('course_section')
            ->where('course_id', $lessonId)
            ->orderByRaw("CAST(section_order AS UNSIGNED)")
            ->get()
            ->toArray();

        $precedingSectionIds = array_map(function ($section) {
            return $section->id;
        }, $precedingSections);


        $studentTakenSections = DB::select("
            SELECT
                ss.student_id,
                users.name,
                lessons.course_title,
                lessons.id AS lessons_id,
                ss.section_id,
                ss.`student-section`
            FROM
                student_section AS ss
            LEFT JOIN users ON users.id = ss.student_id
            LEFT JOIN course_section ON ss.section_id = course_section.id
            LEFT JOIN lessons ON course_section.course_id = lessons.id
            WHERE users.id = :user_id AND lessons.id = :lessons_id
        ", [
                'user_id' => Auth::id(),
                'lessons_id' => $lessonId,
            ]);
        $studentTakenSectionIds = array_map(function ($section) {
            return $section->section_id;
        }, $studentTakenSections);


        // Variable to check is student already take the section by checking studentTakenSectionId
        $isSectionTaken = in_array($sectionId, $studentTakenSectionIds);

        $currentSectionIndex = array_search($sectionId, $precedingSectionIds);
        if ($currentSectionIndex !== false) {
            if ($currentSectionIndex < count($precedingSectionIds) - 1) {
                $nextSectionId = $precedingSectionIds[$currentSectionIndex + 1];
            }

            if ($currentSectionIndex > 0) {
                $prevSectionId = $precedingSectionIds[$currentSectionIndex - 1];
            }
        }

        $sectionTakenByStudent = null;
        $lastSectionTaken = null;

        if (Auth::check()) {
            if (Auth::user()->role == "student") {

                if ($section->can_be_accessed == "n") {
                    abort(401, "Materi baru dapat diakses pada jadwal yang telah ditentukan");
                }
                $sectionTakenByStudent = DB::table('student_section as ss')
                    ->select('section_id')
                    ->leftJoin('users', 'users.id', '=', 'ss.student_id')
                    ->leftJoin('course_section', 'ss.section_id', '=', 'course_section.id')
                    ->leftJoin('lessons', 'course_section.course_id', '=', 'lessons.id')
                    ->where('ss.student_id', \Illuminate\Support\Facades\Auth::id())
                    ->where('lessons.id', $lessonId) // Add the condition lessons.id = 5
                    // ->where('users.is_testing', '=', 'n')
                    ->pluck('ss.section_id')
                    ->toArray();

                $lastSectionTaken = DB::table('student_section as ss')
                    ->leftJoin('users', 'users.id', '=', 'ss.student_id')
                    ->leftJoin('course_section', 'ss.section_id', '=', 'course_section.id')
                    ->leftJoin('lessons', 'course_section.course_id', '=', 'lessons.id')
                    ->where('ss.student_id', \Illuminate\Support\Facades\Auth::id())
                    ->where('lessons.id', $lessonId)
                    // ->where('users.is_testing', '=', 'n')
                    ->orderBy('ss.id', 'desc') // Assuming 'id' is the primary key column in 'student_section' table
                    ->first();
            }
        }

        //return $precedingSectionIds;
        // Check if the student has taken all the preceding sections
        $isPrecedingTaken = StudentSection::whereIn('section_id', $precedingSectionIds)
            ->where('student_id', $user_id)
            ->exists();

        // Total Section dalam sebuah course
        $total_section = DB::table('course_section AS cs')
            ->where('cs.course_id', $lessonId)
            ->count();

        // Total Section yang telah diambil/dikerjakan student
        $sectionTakenOnCourseCount = DB::table('student_section as ss')
            ->leftJoin('users', 'users.id', '=', 'ss.student_id')
            ->leftJoin('course_section', 'ss.section_id', '=', 'course_section.id')
            ->leftJoin('lessons', 'course_section.course_id', '=', 'lessons.id')
            ->where('ss.student_id', Auth::id())
            // ->where('users.is_testing', '=', 'n')
            ->where('lessons.id', $lessonId)
            ->count();


        // return $sectionTakenOnCourseCount;
        // $section = DB::select("select * from view_course_section where lesson_id = $lesson_id ORDER BY section_order ASC");
        // Fetch all sections for the lesson
        $student_sections = DB::select("select * from student_section ");

        $timezone = config('app.timezone'); // Misalnya 'Asia/Jakarta'
        $currentDateTime = Carbon::now($timezone)->toDateTimeString();
        $sections = CourseSection::select(
            'lessons.id as lesson_id',
            'lessons.course_title as lessons_title',
            'lessons.mentor_id',
            'users.name as mentor_name',
            'course_section.id as section_id',
            'course_section.section_order',
            'course_section.section_title',
            'course_section.quiz_session_id',
            'exam_sessions.time_limit_minute', // Include quiz duration
            'course_section.section_content',
            'course_section.section_video',
            'course_section.created_at',
            'course_section.updated_at',
            'course_section.can_be_accessed',
            'exams.is_deleted as is_exam_deleted',
            DB::raw('CASE
                        WHEN exam_sessions.start_date > "' . $currentDateTime . '" THEN "Waiting to Start"
                        WHEN exam_sessions.start_date <= "' . $currentDateTime . '" AND exam_sessions.end_date >= "' . $currentDateTime . '" THEN "Ongoing"
                        ELSE "Finish"
                    END as status')
        )
            ->leftJoin('lessons', 'lessons.id', '=', 'course_section.course_id')
            ->leftJoin('users', 'users.id', '=', 'lessons.mentor_id')
            ->leftJoin('exam_sessions', 'exam_sessions.id', '=', 'course_section.quiz_session_id') // Left join to quiz_session
            ->leftJoin('exams', 'exam_sessions.exam_id', '=', 'exams.id')
            ->where('course_section.course_id', $lessonId)
            // ->where('users.is_testing', '=', 'n')
            ->orderBy(DB::raw('CAST(course_section.section_order AS UNSIGNED)'), 'ASC')
            ->get();

        // return $sections;

        $sectionDetail = CourseSection::findOrFail($sectionId);
        // Iterate over the sections and check if each one is already added to the student-section
        foreach ($sections as $key => $section) {
            // Check if the section is already added to the student-section
            $isTaken = StudentSection::where('section_id', $section->section_id)
                ->where('student_id', Auth::id())
                ->exists();

            // Add the 'isTaken' attribute to the section object
            $section->isTaken = $isTaken;
            $section->user_id = Auth::id();
            $section->isCurrent = $sectionId;

            if ($section->section_id == $sectionId) {
                $section->isCurrent = true;
            } else {
                $section->isCurrent = false;
            }
        }

        $section = $sections;
        $firstSectionId = null;
        $lastSectionId = null;

        $next_section = $nextSectionId;
        $prev_section = $prevSectionId;
        $sectionOrder = $precedingSectionIds;

        if (!empty($sectionOrder)) {
            $firstSectionId = $sectionOrder[0];
            $lastSectionId = end($sectionOrder);
        }

        $isFirstSection = false;
        if ($firstSectionId == $sectionId) {
            $isFirstSection = true;
        }

        $courseId = $lessonId;
        $isStudent = false;


        $alreadyTakeNeededExam = true; // if student has taken the needed exam
        $isEligibleStudent = true; //eligible to open the section
        if (Auth::user()->role == "student") {
            $isStudent = true;
            $completedSections = $sectionTakenByStudent;

            // Get the index of the current section in the sectionOrder array
            $currentSectionIndex = array_search($currentSectionId, $sectionOrder);

            // Loop through the sectionOrder array from the beginning until the current section index
            for ($i = 0; $i < $currentSectionIndex; $i++) {

                //active section within the loop
                $currentIndexedSection = CourseSection::find($sectionOrder[$i]);

                if ($currentIndexedSection!=null && $currentIndexedSection->quiz_session_id!=null) {
                    $zquizSession = ExamSession::find($currentIndexedSection->quiz_session_id);

                    if ($zquizSession) {
                        $now = Carbon::now($timezone)->toDateTimeString();

                        $zcheckIfStudentAlreadyTake = ExamTaker::where('user_id', Auth::id())
                        ->where('course_section_flag', $sectionOrder[$i])
                        ->where('is_finished', 'y')
                        ->whereNotNull('finished_at')
                        ->count();

                        $zquizResults = ExamTaker::where('user_id', Auth::id())
                        ->where('course_section_flag', $sectionOrder[$i])
                        ->where('is_finished', 'y')
                        ->whereNotNull('finished_at')
                        ->get();


                        $zexam = Exam::find("$zquizSession->exam_id");
                        $zsectionTitle = $currentIndexedSection->section_title;
                        $zsectionId = $currentIndexedSection->id;
                        $examTitle = "";
                        if ($zexam != null) {
                            $examTitle = $zexam->title;
                        }

                        // Abort if the student has not taken the quiz and it's not the first section
                        if ($zcheckIfStudentAlreadyTake == 0) {
                            $alreadyTakeNeededExam = false;
                            $zlink = url()->to("/course/$lessonId/section/$zsectionId");
                            $additional = "<a href='$zlink'>$examTitle</a>";
                            $message = "Terdapat Quiz pada Bagian $zsectionTitle yang Belum Anda Kerjakan.\n";
                            return response()->view('errors.sesval', [
                                'sectionTitle' => $zsectionTitle,
                                'message' => $message,
                                'link' => $zlink
                            ], 401);
                        }
                    }
                }


                // Check if the section from sectionOrder exists in completedSections
                if (!in_array($sectionOrder[$i], $completedSections)) {
                    if ($sectionTakenOnCourseCount != 0) {
                        $zsectionTitle = $currentIndexedSection->section_title;
                        $zsectionId = $currentIndexedSection->id;
                        $zlink = url()->to("/course/$lessonId/section/$zsectionId");
                        return response()->view('errors.sesval', [
                            'sectionTitle' => $zsectionTitle,
                            'message' => "Anda Harus Menyelesaikan Bagian-bagian Sebelumnya Untuk Mengakses Bagian Ini",
                            'link' => $zlink
                        ], 401);
                    } else {
                        $isEligibleStudent = false;
                    }
                }
            }

            // return $wantToKnowEachExamPassed;
        }


        $examSession = null;
        $exam = null;
        $question_count = 0;
        $totalScore = 0;
        $session = null;

        if (
            $currentSection->quiz_session_id != null &&
            $currentSection->quiz_session_id != "" &&
            $currentSection->quiz_session_id != "null" &&
            $currentSection->quiz_session_id != "-" &&
            $currentSection->quiz_session_id != "Tidak Ada Quiz"
        ) {
            $isExam = true;
            $examSession = ExamSession::find($currentSection->quiz_session_id);
            $exam = Exam::find($examSession->exam_id);
            $session = $examSession;
            $questions = json_decode($session->questions_answers);
            
            // Acak Urutan Soal
            shuffle($questions);

            $totalScore = 0;
            $title = $exam->title;
            if ($questions != null) {
                foreach ($questions as $question) {
                    if (isset($question->choices)) {
                        $choices = json_decode($question->choices, true);

                        foreach ($choices as $choice) {
                            if (isset($choice['score']) && $choice['score'] !== null && $choice['score'] >= 0) {
                                $totalScore += (int) $choice['score'];
                            }
                        }
                    }
                }
            }
            if ($questions != null) {
                $question_count = count($questions);
            }
        }



        // ================CHECK IF EXAM IS IN TIME =========================
        $isExamInTime = true;
        // Checking is Exam
        if ($isExam == true) {
            if ($examSession != null) {
                $startDate_exam = $examSession->start_date;
                $endDate_exam   = $examSession->end_date;
                $now = Carbon::now();
                if ($now->between($startDate_exam, $endDate_exam)) {
                    // Jika waktu sekarang berada di antara start_date dan end_date
                    // Tambahkan logika di sini
                    $isExamInTime = true;
                } else {
                    // Jika waktu sekarang berada di luar rentang start_date dan end_date
                    // Tambahkan logika di sini
                    $isExamInTime = false;
                }
            }
        }



        // ========== CHECK IF EXAM ON FIRST SECTION IS ALREADY FINISHED =========================

        if (Auth::user()->role == "student") {
            $isFirstExamTaken = true;
            $quizSession = ExamSession::find($currentSection->quiz_session_id);
            if ($quizSession != null) {
                $now = Carbon::now($timezone)->toDateTimeString();

                $checkIfStudentAlreadyTake = ExamTaker::where('user_id', Auth::id())
                    ->where('course_section_flag', $sectionOrder[$i])
                    ->where('is_finished', 'y')
                    ->count();

                if ($checkIfStudentAlreadyTake != 0) {
                    $isFirstExamTaken = true;
                } else {
                    $isFirstExamTaken = false;
                }
            } else {
                $isFirstExamTaken = true;
            }
        }



        if (Auth::user()->role == "student") {

            if ($isEligibleStudent && $alreadyTakeNeededExam && $isFirstExamTaken) {

                if ($isExamInTime) {
                    $this->startSection($currentSectionId); //168
                }


                $u_student_lesson = StudentLesson::where('student_id', '=', $user_id)->where('lesson_id', '=', $lessonId)->first();

                $sectionTakenOnCourseCount = DB::table('student_section as ss')
                    ->leftJoin('users', 'users.id', '=', 'ss.student_id')
                    ->leftJoin('course_section', 'ss.section_id', '=', 'course_section.id')
                    ->leftJoin('lessons', 'course_section.course_id', '=', 'lessons.id')
                    ->where('ss.student_id', Auth::id())
                    ->where('lessons.id', $lessonId)
                    // ->where('users.is_testing', '=', 'n')
                    ->count();

                if ($sectionTakenOnCourseCount == $total_section) {
                    if ($u_student_lesson->learn_status != 1) {
                        $u_student_lesson->finished_at = Carbon::now();
                        $u_student_lesson->learn_status = 1;
                        $u_student_lesson->save();
                    }
                }
            }
        }


        //check if student has taken any exam on this session
        $hasTakenAnyExam = false;
        $examResults = ExamTaker::where('user_id', Auth::id())         // AND user_id = Auth::id()
        ->where('course_section_flag', $currentSectionId)                // AND course_section_flag = $sectionId
        ->where('is_finished', 'y')                               // AND is_finished = 'y'
        ->whereNotNull('finished_at')                             // AND finished_at IS NOT NULL
        ->leftJoin('exam_sessions as es', 'es.id', '=', 'exam_takers.session_id')
        ->leftJoin('exams as e', 'e.id', '=', 'es.exam_id')
        ->select('exam_takers.*', 'e.title as exam_title')
        ->get();



        if (count($examResults) > 0) {
            $hasTakenAnyExam = true;
        }

        $classInfo = DB::select("SELECT
                        a.*,
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
                        WHERE
                            EXISTS (
                                SELECT 1
                                FROM student_lesson sl
                                WHERE a.id = $lessonId

                            )
                        GROUP BY
                            a.id, b.name, b.profile_url;
                        ");
        $courseCategory = "";
        $courseCategoryColor = "#000000";

        if ($lesson != null) {
            $category = LessonCategory::where('id', $lesson->category_id)->first();
            if ($category) {
                $courseCategory = $category->name;
                $courseCategoryColor = $category->color_of_categories;
            }
        }

        // return $classInfo;

        $compact = compact(
            'isEligibleStudent',
            'hasTakenAnyExam',
            'courseCategory',
            'courseCategoryColor',
            'examResults',
            'currentSectionId',
            'courseId',
            'next_section',
            'prev_section',
            'isStudent',
            'sectionTakenByStudent',
            'sectionTakenOnCourseCount',
            'isFirstSection',
            'isExam',
            'title',
            'isSectionTaken',
            'sectionDetail',
            'sections',
            'questions',
            'firstSectionId',
            'lastSectionId',
            'isPrecedingTaken',
            'examSession',
            'exam',
            'session',
            'question_count',
            'totalScore',
            'sectionOrder',
            'lesson',
            'section',
            'isRegistered',
            'classInfo',
            'currentSection'
        );

        if ($request->dump == true) {
            return $compact;
        }

        MyHelper::addAnalyticEvent(
            "Buka Section",
            "Course Section"
        );

        // return $currentSection;
        return view('lessons.play.course_play_new', $compact);
    }

    public function prewiew_course()
    {
        return view('lessons.play.course_play_preview');
    }

    function startSection($sectionId)
    {
        $section = $sectionId;
        $student = Auth::id();

        $studentSectionValue = "$student" . "-" . "$section";

        // Check if the student-section already exists
        $existingRecord = StudentSection::where('student-section', $studentSectionValue)->first();

        if ($existingRecord) {
            // Handle the case when the record already exists
            // For example, you can return an error message or redirect back with an error
            // return back()->with('error', 'Student-section already exists.');
        } else {
            // Create a new instance of StudentSection
            $data = new StudentSection();
            $data->student_id = $student;
            $data->section_id = $section;
            $data->setAttribute('student-section', $studentSectionValue);
            // Save the data
            $data->save();

            // Perform any additional actions after saving
            // Redirect or return a success message
            // return redirect()->route('success')->with('success', 'Student-section saved successfully.');
        }
    }

    public function create_section(Lesson $lesson)
    {
        MyHelper::addAnalyticEvent(
            "Buka Halaman Buat Section",
            "Create Section"
        );
        $user_id = Auth::id();
        $dayta = DB::select("select * from view_course where mentor_id = $user_id");
        return view('lessons.section.create_section', compact('lesson'), compact('dayta'));
    }

    /**
     * store
     *
     * @param mixed $request
     * @return void
     */
    public function store(Request $request)
    {
        // return dd($request->all());
        ini_set('memory_limit', '1024000M');
        $rules = [
            'title' => 'required',
            //            'video' => 'required',
            //            'content' => 'required',
            'section_order' => 'required|unique:course_section',
            'course_id' => 'required',
            //            'course_name' => 'required',
        ];
        $customMessages = [
            'required' => 'Mohon Isi Kolom :attribute terlebih dahulu'
        ];

        $this->validate($request, $rules, $customMessages);
        $lesson_id = $request->course_id;

        if ($request->video != null) {
            $video = $request->file('video');
            $video->storeAs("public/class/content/$lesson_id/", $video->hashName());
        }

        $section_order = $lesson_id . "-" . $request->section_order;
        // abort(404,$section_order);


        // Check for duplicate entry
        $existingSection = CourseSection::where('section_order', $section_order)
            ->where('course_id', $lesson_id)
            ->first();

        if ($existingSection) {
            $errorMessage = 'Urutan kelas sudah pernah digunakan, harap pilih nomor urutan yang lain.';
            return redirect("lesson/$lesson_id/section")->withErrors([$errorMessage])->withInput();
        }

        // Create an instance of CourseSection
        $inputDeyta = new CourseSection();

        // Set common attributes
        $inputDeyta->course_id = $lesson_id ?? '';
        $inputDeyta->section_content = $request->{'content'} ?? '';
        $inputDeyta->section_order = $section_order ?? '';
        $inputDeyta->can_be_accessed = $request->access ?? '';
        $inputDeyta->quiz_session_id = $request->quiz_session_id ?? '';
        $inputDeyta->duration_take = $request->durationTake ?? '';
        $inputDeyta->section_title = $request->title ?? '';
        $inputDeyta->section_video = " ";

        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            // Handle the video file if it's present and valid
            $video = $request->file('video');
            $video->storeAs("public/class/content/$lesson_id/", $video->hashName());
            $inputDeyta->section_video = $video->hashName();
        }

        // Save the CourseSection object
        $inputDeyta->save();


        if ($inputDeyta) {
            //redirect dengan pesan sukses
            return redirect("lesson/$lesson_id/section")->with(['success' => 'Kelas Berhasil Disimpan!']);
        } else {
            //redirect dengan pesan error
            return redirect("lesson/$lesson_id/section")->with(['error' => 'Kelas Gagal Disimpan!']);
        }
    }

    public function destroy($id)
    {
        try {
            // Find the CourseSection by ID or throw an exception if not found
            $section = CourseSection::findOrFail($id);

            // Define the path to the section's video file
            $section_video_file = "public/class/content/" . $section->course_id . "/" . $section->section_video;

            // Delete the video file if it exists
            if (Storage::disk('local')->exists($section_video_file)) {
                Storage::disk('local')->delete($section_video_file);
            }

            // Delete related records in ExamTaker
            ExamTaker::where('course_section_flag', $id)->delete();

            // Delete the CourseSection
            $section->delete();

            $lesson_id = $section->course_id;

            // Redirect with a success message
            return redirect("lesson/$lesson_id/section")->with(['success' => 'Materi Berhasil Dihapus!']);
        } catch (\Exception $e) {
            // Handle exceptions (e.g., section not found)
            $lesson_id = $section->course_id ?? null;

            // Redirect with an error message
            return redirect("lesson/$lesson_id/section")->with(['error' => 'Materi Gagal Dihapus!']);
        }
    }

    /**
     * update
     * @return void
     */
    public function update(Request $request, CourseSection $section)
    {
        //dd($request->all());
        // dd($request->all());
        CourseSection::findOrFail($section->id);


        $lesson_id = $section->course_id;
        $section_order = $lesson_id . "-" . $request->section_u_order;
        if ($request->file('section_u_video') == "") {
            $section->update([
                'section_content' => $request->section_u_content ?? "",
                'section_order' => $section_order,
                'course_id' => $lesson_id,
                'can_be_accessed' => $request->access,
                'quiz_session_id' => $request->quiz_session_id,
                'duration_take' => $request->duration_u_Take,
                'section_title' => $request->section_u_title ?? "",
            ]);
        } else if ($request->file('section_u_video') != "") {
            //hapus old video
            Storage::disk('local')->delete("public/class/content/" . $section->course_id . "/" . $section->section_video);
            //upload new video
            $video = $request->file('section_u_video');
            $cat = $request->input('category');
            $video->storeAs('public/class/content/' . $section->course_id . "/", $video->hashName());
            $section->update([
                'section_video' => $video->hashName(),
                'section_content' => $request->section_u_content ?? "",
                'section_order' => $section_order,
                'course_id' => $lesson_id,
                'can_be_accessed' => $request->access,
                'quiz_session_id' => $request->quiz_session_id,
                'duration_take' => $request->duration_u_Take,
                'section_title' => $request->section_u_title,
            ]);
        }
        if ($section) {
            //redirect dengan pesan sukses
            return redirect("lesson/$lesson_id/section")->with(['success' => 'Materi Berhasil Diupdate!']);
        } else {
            //redirect dengan pesan error
            return redirect("lesson/$lesson_id/section")->with(['error' => 'Kelas Gagal Diupdate!']);
        }
    }

    public function updateOrder(Request $request)
    {
        $data = $request->all();
        $orders = $data['orders']; // Retrieve the array of orders
        $lesson_id = $data['lesson']; // Retrieve the lesson ID

        // Loop through each order and update the corresponding row
        foreach ($orders as $order) {
            $newPosition = $order['newPosition'];
            $code = $lesson_id . '-' . $newPosition;
            $code = $newPosition;
            $itemID = $order['id'];

            // Update the row with the new section order
            CourseSection::where('id', $itemID)->update(['section_order' => $code]);
        }

        // Kirim respons kembali ke klien
        return response()->json(['message' => 'Urutan berhasil diperbarui'], 200);
    }

    public function viewStudents(Request $request, $lessonId)
    {
        Paginator::useBootstrap();
        $sortBy = $request->sortBy ?? 'asc';
        $lessonId = $request->lessonId;
        $all_students = User::all();
        // Lakukan pengelompokan data berdasarkan departemen dan simpan dalam daftar unik

        $uniqueDepartments = DB::connection('ithub')
            ->table('m_departments')
            // ->where('code', 'LIKE', '%_NEW%')
            ->orderBy('name')
            ->get();

        $studentsInLesson = User::join('student_lesson', 'users.id', '=', 'student_lesson.student_id')
            ->where('student_lesson.lesson_id', $lessonId)
            ->select('users.name', 'users.department_id', 'users.id', 'student_lesson.lesson_id') // Pilih kolom yang ingin Anda ambil dari tabel users
            ->orderBy('users.name', $sortBy)
            ->paginate(10);

        foreach ($studentsInLesson as $item) {
            $departmentId = $item->department_id;
            $department = DB::connection('ithub')->selectOne("SELECT * FROM m_departments where id = '$departmentId'");

            $departmentName = "";
            if ($department != null) {
                $departmentName = $department->name;
            } else {
                $departmentName = "Tidak Ada Department";
            }
            $item->department = $departmentName;
        }


        return view("lessons.view_students")->with(compact("lessonId", "sortBy", "uniqueDepartments", "studentsInLesson"));
    }

    public function delete_Students($id, $lessonId)
    {
        StudentLesson::where('student_id', $id)->where('lesson_id', $lessonId)->delete();

        StudentSection::leftJoin('course_section as cs', 'student_section.section_id', '=', 'cs.id')
                        ->where('student_id', $id)
                        ->where('cs.course_id', $lessonId)
                        ->delete();
        
        ExamTaker::where('user_id', $id)->where('exam_takers.course_flag', $lessonId)->delete();

        return back()->with(['success' => 'Students Deleted Successfully']);
    }

    public function add_Students(Request $request, $lessonId)
    {
        $department = $request->name_of_department;
        $username_id = $request->student_id;

        // Check if the student_lesson record already exists
        $existingRecord = StudentLesson::where('student_id', $username_id)
            ->where('lesson_id', $lessonId)
            ->first();

        if ($existingRecord) {
            // Redirect back with a message indicating duplicate entry
            return back()->with(['error' => 'Peserta sudah ada dalam kelas ini!']);
        }

        // Proceed to insert if no duplicate found
        $user_to_insert = User::findOrFail($username_id);

        // Insert into StudentLesson table
        $insert_to_StuLess = new StudentLesson();
        $insert_to_StuLess->student_id = $user_to_insert->id;
        $insert_to_StuLess->lesson_id = $lessonId;
        $insert_to_StuLess->{"student-lesson"} = $user_to_insert->id . '-' . $lessonId;
        $insert_to_StuLess->learn_status = 0;
        $insert_to_StuLess->certificate_file = '';
        $insert_to_StuLess->save();

        if ($insert_to_StuLess) {
            // Redirect back with success message
            return back()->with(['success' => 'Peserta Baru Berhasil Ditambahkan!']);
        } else {
            // Redirect back with error message if insertion fails
            return back()->with(['error' => 'Peserta Baru Gagal Ditambahkan!']);
        }
    }
    public function find_student_by_dept(Request $request)
    {
        $department = $request->name_of_department;

        $student = User::where('department_id', $department)->get();
        return $student;
    }
}
