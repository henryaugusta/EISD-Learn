<div class="container-fluid">
    <div class="main-content-container container-fluid px-4 mt-5">
        <section id="exam-information">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="">
                        <div class="pt-2">
                            <h1 class="card-title">{{ $currentSection->section_title }}</h1>


                            <div class="form-group d-none">
                                <label for="fullName">Nama Peserta Quiz :</label>
                                <input type="text" id="fullName" name="fullName" class="form-control"
                                    placeholder="Enter your full name" required
                                    @auth value="{{ Auth::user()->name }}" @endauth>
                            </div>

                            <h5 style="font-size: 22px; color: slategray;">
                                {{ 'Anda Sudah Mengambil Quiz ini : ' . count($examResults) . ' Kali' }}</h5>
                            <h5 style="font-size: 21px; margin-top: 20px">{!! $sectionDetail->section_content !!}</h5>

                            <!-- Time -->
                            <div class="form-group">
                                <h5>Batas Waktu : {{ $session->time_limit_minute }} Menit</h5>
                            </div>

                            <div class="form-group">
                                <h5>Multiple Attempt: {{ $session->allow_multiple === 'y' ? 'Ya' : 'Tidak' }}</h5>
                            </div>


                            <!-- Number of Questions -->
                            <div class="form-group">
                                <label>Jumlah Pertanyaan :</label>
                                <span>{{ $question_count }}</span>
                            </div>


                            <!-- Student Name -->
                            <div class="form-group d-none">
                                <label>Student Name:</label>
                                <span>John Doe</span>
                            </div>

                            <!-- Start Exam Button -->
                            <button id="confirmStartButton" type="button"
                                class="btn btn-primary btn-border btn-round mb-3 mt-3" data-toggle="modal"
                                data-target="#confirmationModal">
                                Mulai Ujian
                            </button>




                            <div class="alert alert-primary d-none" role="alert">
                                <h4 class="alert-heading"></h4>
                                <p>Klik <strong>tombol kirim</strong>, setelah ujian selesai, perhatikan sisa waktu yang
                                    tersedia<strong></strong></p>
                                <hr>
                            </div>

                            <!-- Confirmation Modal -->
                            <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog"
                                aria-labelledby="confirmationModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="confirmationModalLabel">Start Exam Confirmation
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            Are you sure you want to start the exam?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                Cancel
                                            </button>
                                            <button id="startExam" type="button" data-dismiss="modal"
                                                class="btn btn-primary">
                                                Start Exam
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add more additional fields as needed -->
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section id="sectionFocus" style="display: none">
            @if (isset($questions) && count($questions) > 0)
                @foreach ($questions as $index => $question)
                    <div class="" style="margin-bottom: 20px; ">
                        <div class="panel-body">
                            <p style="font-size: 18px"><strong>{{ $index + 1 }}.</strong> <span
                                    style="font-size: 18px">{{ $question->question }}</span></p>
                            @if (!empty($question->image))
                                <img src="{{ url('https://lms-modernland.s3.ap-southeast-3.amazonaws.com') . '/' . $question->image }}"
                                    alt="Question Image" style="max-width: 100%;">
                            @endif

                            @if (stripos($question->question_type, 'single') !== false)
                                <div class="radio">
                                    @php $letters = range('A', 'Z'); @endphp
                                    @foreach (json_decode($question->choices, true) as $index => $choice)
                                        <div>
                                            <label>
                                                <input type="radio" name="answers[{{ $question->id }}]"
                                                    value="{{ $choice['text'] }}"
                                                    style="background-color: #208DBB; font-size: 18px">
                                                {{ $letters[$index] }}
                                                . {{ $choice['text'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="checkbox">
                                    @php $letters = range('A', 'Z'); @endphp
                                    @foreach (json_decode($question->choices, true) as $index => $choice)
                                        <div>
                                            <label>
                                                <input type="checkbox" name="answers[{{ $question->id }}][]"
                                                    value="{{ $choice['text'] }}"
                                                    style="background-color: #208DBB; font-size: 18px;">
                                                {{ $letters[$index] }}
                                                . {{ $choice['text'] }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
                <button id="finishExam" type="submit" class="btn btn-primary">Submit</button>
            @else
                <p>No questions found.</p>
            @endif
        </section>

        @if ($examSession->show_result == 'y' || $examSession->show_result == '')
            <div class="mt-4">
                <hr>
                <h4>Riwayat Hasil Ujian : </h4>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Guest Name</th>
                                <th>Current Score</th>
                                <th>Finished At</th>
                                @if ($examSession->allow_review == 'y' || $examSession->allow_review == '')
                                    <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($examResults->reverse() as $result)
                                <tr>
                                    <td>{{ $result['guest_name'] }}</td>
                                    <td>{{ $result['current_score'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($result['finished_at'])->format('F j, Y g:i A') }}
                                    </td>
                                    @if ($examSession->allow_review == 'y' || $examSession->allow_review == '')
                                        <td>
                                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                                data-target="#exampleModal{{ $result['id'] }}">
                                                Show Answers
                                            </button>
                                        </td>

                                        <!-- Modal -->
                                        <div class="modal fade" id="exampleModal{{ $result['id'] }}" tabindex="-1"
                                            role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">User Answers</h5>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                            aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        @foreach (json_decode($result['user_answers'], true) as $answer)
                                                            <div style="margin-top: 20px">
                                                                <strong>Question:</strong><br>
                                                                {{ $answer['question_text'] ?? '' }}
                                                            </div>
                                                            <div>
                                                                <strong>Jawaban Pengguna:</strong><br>
                                                                @if (isset($answer['isMultipleSelect']) && $answer['isMultipleSelect'])
                                                                    Multiple Choice:
                                                                    @if (is_array($answer['values']))
                                                                        <ul>
                                                                            @foreach ($answer['values'] as $value)
                                                                                <li>{{ $value }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @else
                                                                        {{ $answer['values'] ?? '' }}
                                                                    @endif
                                                                @else
                                                                    Single Choice: {{ $answer['values'][0] ?? '' }}
                                                                @endif
                                                            </div>
                                                            <div>
                                                                <strong>Jawaban Benar:</strong><br>
                                                                @if (is_array($answer['correct_answer']))
                                                                    <ul>
                                                                        @foreach ($answer['correct_answer'] as $item)
                                                                            <li>{{ $item }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    {{ $answer['correct_answer'] ?? '' }}
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">No exam results found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        @endif
    </div>

</div>


<script>
    // Define DOM elements
    const startExamButton = document.getElementById("startExam");
    const confirmStartButton = document.getElementById("confirmStartButton");
    const finishExamButton = document.getElementById("finishExam");
    const sectionFocus = document.getElementById("sectionFocus");
    const timer = document.getElementById("timer");
    const timeLimit = {{ $session->time_limit_minute }} * 60; // Convert minutes to seconds
    let remainingTime = timeLimit;
    let timerInterval;

    // Function to start the timer
    function startTimer() {
        if (window.innerWidth < 50000) {
            document.getElementById('sidebar-wrapper').classList.add('d-none');
        }

        const payload = {
            userAnswers: {
                examId: {{ $examSession->exam_id }},
                sessionId: {{ $examSession->id }},
                courseId: {{ $courseId }},
                answers: []
            },
            examId: {{ $examSession->exam_id }},
            sessionId: {{ $examSession->id }},
            sectionId: {{ $currentSectionId }},
            courseId: {{ $courseId }},
            isFinished: false,
            fullName: "{{ Auth::user()->name }}"
        };

        fetch("/exam/save-user-answer", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.showError) {
                    Swal.fire({
                        title: "Error",
                        text: `${data.message}`,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                    clearInterval(timerInterval);
                    return;
                }
                confirmStartButton.style.display = "none";
                sectionFocus.style.display = "block";
                timerInterval = setInterval(updateTimer, 1000);
                activateRestrictions(); // Activate restrictions
            })
            .catch(error => console.error("Error starting timer:", error));
    }

    // Function to update the timer
    function updateTimer() {
        remainingTime--;

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            timer.textContent = "Time's up!";
            timer.style.color = "red";
            sendAllUserAnswers(true);
        } else {
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            timer.textContent = `${padNumber(minutes)}:${padNumber(seconds)}`;
            const ninetyPercent = Math.floor(timeLimit * 0.9);
            if (remainingTime <= ninetyPercent) {
                timer.style.color = timer.style.color === "red" ? "black" : "red";
            }
        }
    }

    function padNumber(number) {
        return number < 10 ? "0" + number : number;
    }

    // Function to send all user answers
    function sendAllUserAnswers(isFinished) {
        const payload = {
            userAnswers: {
                examId: {{ $examSession->exam_id }},
                sessionId: {{ $examSession->id }},
                courseId: {{ $courseId }},
                answers: []
            },
            examId: {{ $examSession->exam_id }},
            sessionId: {{ $examSession->id }},
            sectionId: {{ $currentSectionId }},
            courseId: {{ $courseId }},
            isFinished: isFinished,
            fullName: "{{ Auth::user()->name }}"
        };

        const radioInputs = document.querySelectorAll("input[type='radio']:checked");
        const checkboxInputs = document.querySelectorAll("input[type='checkbox']:checked");

        radioInputs.forEach(input => {
            const answerId = input.name.split("[")[1].split("]")[0];
            const answerValue = input.value;
            payload.userAnswers.answers.push({
                id: answerId,
                isMultipleSelect: false,
                values: [answerValue]
            });
        });

        checkboxInputs.forEach(input => {
            const answerId = input.name.split("[")[1].split("]")[0];
            const answerValue = input.value;
            const existingAnswer = payload.userAnswers.answers.find(answer => answer.id === answerId);
            if (existingAnswer) {
                existingAnswer.values.push(answerValue);
            } else {
                payload.userAnswers.answers.push({
                    id: answerId,
                    isMultipleSelect: true,
                    values: [answerValue]
                });
            }
        });

        fetch("/exam/save-user-answer", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.showError) {
                    Swal.fire({
                        title: "Error",
                        text: `${data.message}`,
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                }
                if (data.is_finished) {
                    Swal.fire({
                        title: 'Exam Finished!',
                        text: `Your score is ${data.scores}.`,
                        icon: 'success',
                        confirmButtonText: 'Next Page'
                    }).then(result => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    console.log("Answers submitted successfully");
                }
            })
            .catch(error => console.error("Error submitting answers:", error));
    }

    // Function to activate restrictions
    function activateRestrictions() {
        window.addEventListener("beforeunload", function(e) {
            if (!isFinished) {
                var confirmationMessage =
                    "Are you sure you want to leave the exam? Your progress will be lost.";
                (e || window.event).returnValue = confirmationMessage;
                return confirmationMessage;
            }
        });

        document.addEventListener("visibilitychange", function() {
            if (document.hidden && !isFinished) {
                Swal.fire({
                    title: "Warning",
                    text: "You are trying to leave the exam. Please return to the exam tab.",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
            }
        });

        // Array of IDs for buttons and links you want to enable
        const enabledButtonIds = ['statusBarHomeButton', 'statusBarBackButton',"finishExam"];
        const enabledLinkIds = ['statusBarHomeButton', 'statusBarBackButton',"finishExam"];

        // Select all buttons and links
        const buttons = document.querySelectorAll('button');
        const links = document.querySelectorAll('a');

        // Select the element with the ID 'wrapper'
        var element = document.getElementById('wrapper');
        // Check if the element contains the class 'toggled'
        if (element.classList.contains('toggled')) {
            console.log('The element contains the class "toggled".');
        } else {
            // Toggle the 'toggled' class on the element
            element.classList.toggle('toggled');
            console.log('The element does not contain the class "toggled".');
        }

        // Function to enable or disable buttons and links based on their IDs
        buttons.forEach(button => {
            if (enabledButtonIds.includes(button.id)) {
                // Enable specified buttons
                button.disabled = false;
            } else {
                // Disable other buttons
                button.disabled = true;
            }
        });

        links.forEach(link => {
            if (enabledLinkIds.includes(link.id)) {
                // Enable specified links
                link.style.pointerEvents = 'auto';
                link.style.color = 'initial'; // or set a specific color
            } else {
                // Disable other links
                link.style.pointerEvents = 'none';
                link.style.color = 'gray';
            }
        });
    }

    // Add event listeners
    startExamButton.addEventListener("click", startTimer);

    finishExamButton.addEventListener("click", function() {
        Swal.fire({
            title: 'Are you sure you want to finish the exam?',
            text: 'You won\'t be able to change your answers after submission!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) {
                sendAllUserAnswers(true);
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire('Cancelled', 'Your exam submission was cancelled.', 'info');
            }
        });
    });

    // Capture changes in checkbox and radio buttons
    document.querySelectorAll("input[type='checkbox'], input[type='radio']").forEach(input => {
        input.addEventListener("change", function() {
            console.log("Input changed:", this); // Log changed input for debugging
            sendAllUserAnswers(false); // Call function to send all user answers
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
    });
</script>
 