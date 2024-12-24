@section('head-section')
    @include('main.home._styling_home_student')
    {{-- Charts.css --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
@endsection


@section('script')
    {{-- @include('main.home.script_student') --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    {{-- canvasJS for Pie Chart --}}
    {{-- <script type="text/javascript">
        window.onload = function() {
            // Data from controller
            var groupedByDepartment = @json($groupedByDepartment);

            // Convert data to format needed for CanvasJS
            var pieDataPoints = [];
            for (var key in groupedByDepartment) {
                pieDataPoints.push({
                    y: groupedByDepartment[key],
                    indexLabel: key,
                    color: getRandomColor()
                });
            }

            // Function to get a random color
            function getRandomColor() {
                var letters = '0123456789ABCDEF';
                var color = '#';
                for (var i = 0; i < 6; i++) {
                    color += letters[Math.floor(Math.random() * 16)];
                }
                return color;
            }

            // Script untuk Pie Chart
            var chart_pie = new CanvasJS.Chart("chartContainer_pieChart", {
                theme: "light3",
                // title: {
                //     text: "Percentage of Students in Departments"
                // },
                legend: {
                    horizontalAlign: "bottom",
                    verticalAlign: "center",
                    fontSize: 14
                },
                data: [{
                    type: "pie",
                    showInLegend: true,
                    toolTipContent: "{y} Students - #percent %",
                    legendText: "{indexLabel}",
                    dataPoints: pieDataPoints
                }]
            });
            chart_pie.render();

            // Script untuk Stacked Bar
            var chart_stackedBar = new CanvasJS.Chart("chartContainer_stackedBar", {
                title: {
                    text: "Division of products Sold in Quarter."
                },
                toolTip: {
                    shared: true
                },
                axisY: {
                    title: "percent"
                },

            });
            chart_stackedBar.render();
        }
    </script> --}}

    <script type="text/javascript" src="https://cdn.canvasjs.com/canvasjs.min.js"></script>
    <script>
        const DISPLAY = true;
        const BORDER = true;
        const CHART_AREA = true;
        const TICKS = true;
        const ctx = document.getElementById('myChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                        label: 'Blue Line',
                        data: [12, 19, 3, 5, 2, 3, 12, 19, 3, 5, 2, 3],
                        borderColor: 'blue',
                        borderWidth: 2,
                        fill: false
                    },
                    {
                        label: 'Red Line',
                        data: [5, 9, 8, 2, 6, 7, 5, 9, 8, 2, 6, 7],
                        borderColor: 'red',
                        borderWidth: 2,
                        fill: false
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
@endsection

@section('main')
    <div class="page-inner" style="margin-top: 50px; background-color:white!important;">
        <div class="row mt-2 border-primary">
            {{-- PROFILE USER --}}
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content mt-2 mb-5" id="pills-without-border-tabContent">
                            <div class="tab-pane fade show active" id="pills-home-nobd" role="tabpanel"
                                aria-labelledby="pills-home-tab-nobd">
                                <div class="d-flex align-items-center"> {{-- Use flexbox for layout --}}
                                    <div class="mr-3"> {{-- Margin right for spacing --}}
                                        <img style="width: 100%; max-width: 130px; height: auto;  max-height: 130px"
                                            src="{{ env('AWS_BASE_URL') . Auth::user()->profile_url }}" alt="Profile Image"
                                            class="avatar-img rounded-circle"
                                            onerror="this.onerror=null; this.src='{{ url('/default/default_profile.png') }}'; this.alt='Alternative Image';">
                                    </div>
                                    <div>
                                        <div class="card-head-row card-tools-still-right">
                                            <h3 style="color: black;"><b>{{ Auth::user()->name }}</b></h3>
                                            <p class="card-category d-none" style="color: red">Department Mentor</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ml-auto mt-5">
                            <div class="portfolio-container">
                                <img src="{{ url('/home_icons/Email.svg') }}" alt="Portfolio Icon">
                                <p>{{ Auth::user()->email }}</p>
                            </div>
                            <div class="social-icon">
                                <a href="https://facebook.com/.{{ Auth::user()->url_facebook }}" target="_blank"
                                    rel="noopener noreferrer" class="btn btnColor btn-icon">
                                    <img src="{{ url('/home_icons/Facebook.svg') }}" alt="Facebook Icon">
                                </a>
                                <a href="https://www.linkedin.com/in/{{ Auth::user()->url_linkedin }}" target="_blank"
                                    rel="noopener noreferrer" class="btn btnColor btn-icon">
                                    <img src="{{ url('/home_icons/linkedin.svg') }}" alt="Instagram Icon">
                                </a>
                                <a href="https://instagram.com/#" target="_blank" rel="noopener noreferrer"
                                    class="btn btnColor btn-icon">
                                    <img src="{{ url('/home_icons/Twitter.svg') }}" alt="Instagram Icon">
                                </a>
                                <a href="https://instagram.com/{{ Auth::user()->url_instagram }}" target="_blank"
                                    rel="noopener noreferrer" class="btn btnColor btn-icon">
                                    <img src="{{ url('/home_icons/Instagram.svg') }}" alt="Instagram Icon">
                                </a>
                                <a href="https://youtube.com/{{ Auth::user()->url_youtube }}" target="_blank"
                                    rel="noopener noreferrer" class="btn btnColor btn-icon">
                                    <img src="{{ url('/home_icons/Youtube.svg') }}" alt="Instagram Icon">
                                </a>
                                <a href="https://wa.me/{{ Auth::user()->url_whatsapp }}" target="_blank"
                                    rel="noopener noreferrer" class="btn btnColor btn-icon">
                                    <img src="{{ url('/home_icons/Whatsapp.svg') }}" alt="Instagram Icon">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PIE CHART --}}
            {{-- <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Overview</h5>
                            <div class="dropdown">
                                <select class="form-control" id="learnStatusSelect" style="border: none;"
                                    onchange="updateLearnStatus()">
                                    <option value="finished" {{ request('learn_status') == 'finished' ? 'selected' : '' }}>
                                        Finished</option>
                                    <option value="not_finished"
                                        {{ request('learn_status') == 'not_finished' ? 'selected' : '' }}>Not Finished
                                    </option>
                                </select>
                            </div>

                        </div>

                        <script>
                            function updateLearnStatus() {
                                const selectElement = document.getElementById('learnStatusSelect');
                                const hiddenInputElement = document.getElementById('learnStatusInput');
                                hiddenInputElement.value = selectElement.value;
                                document.getElementById('filterForm').submit();
                            }
                        </script>

                        <div class="tab-pane fade show active" id="pills-home-nobd" role="tabpanel"
                            aria-labelledby="pills-home-tab-nobd">
                            <div class="" style="position: relative; height: 100%;">
                                <div class="d-flex flex-column">
                                    <div style="text-align: center; flex-grow: 1;">
                                        <div id="chartContainer_pieChart" style="height: 275px; width: auto;"></div>
                                        @if (count($groupedByDepartment) == 0)
                                            <p style="margin-top: 20px; font-size: 1.2em;">No Data Available</p>
                                            <!-- Button placed at the bottom left, inline with content -->
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="openPieDetailsPage()"
                                                style="position: absolute; bottom: 10px; left: 10px;">See More
                                                Details</button>
                                        @else
                                            <!-- Button placed at the bottom left, inline with content -->
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                onclick="openPieDetailsPage()"
                                                style="position: absolute; bottom: 10px; left: 10px;">See More
                                                Details</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>


                        <script>
                            function openPieDetailsPage() {
                                const form = document.createElement('form');
                                form.method = 'GET';
                                form.action = '{{ url('/visualization/main-pie-chart-details') }}'; // Replace with the URL for the new page

                                // Create hidden inputs with the current form values
                                const inputs = [{
                                        name: 'learn_status',
                                        value: document.getElementById('learnStatusSelect').value
                                    },
                                    {
                                        name: 'location',
                                        value: document.getElementById('exampleFormControlSelect1').value
                                    },
                                    {
                                        name: 'department',
                                        value: document.getElementById('exampleFormControlSelect2').value
                                    },
                                    {
                                        name: 'class',
                                        value: "all"
                                    },
                                    {
                                        name: 'month',
                                        value: document.getElementById('exampleFormControlSelect3').value
                                    }
                                ];

                                inputs.forEach(input => {
                                    const hiddenInput = document.createElement('input');
                                    hiddenInput.type = 'hidden';
                                    hiddenInput.name = input.name;
                                    hiddenInput.value = input.value;
                                    form.appendChild(hiddenInput);
                                });

                                document.body.appendChild(form);
                                form.submit();
                            }
                        </script>
                    </div>
                </div>
            </div> --}}


            {{-- DASHBOARD --}}
            <div class="col-md-12">
                <h1><strong>Dashboard</strong></h1>
            </div>

            {{-- Total Class --}}
            <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round" style="background-color: #FFEEE8">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center bubble-shadow-small">
                                    <img src="{{ url('icons/dashboard_icon/enrolled.png') }}" alt="Portfolio Icon">
                                </div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <h4 class="card-title">0</h4>
                                    <p class="card-category">Total Mentor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Students --}}
            <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round" style="background-color: #E1F7E3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center bubble-shadow-small">
                                    <img src="{{ url('icons/dashboard_icon/students.png') }}" alt="Portfolio Icon">
                                </div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <h4 class="card-title">0</h4>
                                    <p class="card-category">Total Students of Company</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- On Progress Course --}}
            {{-- <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round" style="background-color: #E1F7E3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center bubble-shadow-small">
                                    <img src="{{ url('icons/dashboard_icon/active.png') }}" alt="Portfolio Icon">
                                </div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <h4 class="card-title">{{ $countCourseInProgress }}</h4>
                                    <p class="card-category">On Progress Course</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            {{-- Completed Course --}}
            {{-- <div class="col-sm-6 col-md-6">
                <div class="card card-stats card-round" style="background-color: #FFEEE8">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-icon">
                                <div class="icon-big text-center bubble-shadow-small">
                                    <img src="{{ url('icons/dashboard_icon/completed.png') }}" alt="Portfolio Icon">
                                </div>
                            </div>
                            <div class="col col-stats ml-3 ml-sm-0">
                                <div class="numbers">
                                    <h4 class="card-title">{{ $countCourseCompleted }}</h4>
                                    <p class="card-category">Completed Course</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}

            @if (session()->has('success'))
                <script>
                    toastr.success('{{ session('success ') }}', ' {{ Session::get('success') }}');
                </script>
            @elseif(session()->has('error'))
                <script>
                    toastr.error('{{ session('error ') }}', ' {{ Session::get('error') }}');
                </script>
            @endif
        </div>
    </div>
@endsection
