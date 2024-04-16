@extends('main.template')

@section('main')

    <div class="container">
        <div class="page-inner">
            <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                <ol class="breadcrumb bg-white">
                    <li class="breadcrumb-item"><a href={{url('/home')}}>Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    <li class="breadcrumb-item active" aria-current="page">Course - </li>
                </ol>
            </nav>
    
    
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content mt-2 mb-5" id="pills-without-border-tabContent">
                                <div class="tab-pane fade show active" id="pills-home-nobd" role="tabpanel"
                                    aria-labelledby="pills-home-tab-nobd">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <img style="width: 100%; max-width: 130px; height: auto;"
                                                src="{{ Storage::url('public/profile/') . Auth::user()->profile_url }}"
                                                alt="Profile Image" class="avatar-img rounded-circle"
                                                onerror="this.onerror=null; this.src='{{ url('/default/default_profile.png') }}'; this.alt='Alternative Image';">
                                        </div>
                                        <div>
                                            <div class="card-head-row card-tools-still-right">
                                                <h3 style="color: black;"><b>POST TEST</b></h3>
                                                <p class="card-category">Label Kategori</p>
                                            </div>
                                        </div>
                                        <div class="ml-auto d-flex" style="margin-bottom: -190px; position: relative">
                                            <img style="width: 20px; height: auto;" src="{{ url('/icons/UserStudent_mentor.svg') }}" alt="User Icon">
                                            <a href="#" style="text-decoration: none; color: black;">
                                                <p style="font-size: 15px; margin-left: 15px; margin-top: 18px">
                                                    <b>0</b><span style="color: #8C94A3;"> students</span>
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Post Test Progress
                        </div>
                        <div class="card-body">
                            
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            Detail Not Finished
                        </div>
                        <div class="card-body">
                            
                        </div>
                    </div>
                </div>
            </div>


            <h4 class="page-title">Leaderboard Score</h4>
            <div class="col-md-12" >
                <div class="row">
                    <div class="col-11" >
                        <button class="btn btn-custom"><strong>Download</strong></button>
                    </div>
                    <div class="col-1 ms-auto mt-4">
                        <a href="#"  id="seeAllLink" style="text-decoration: none; color:#8C94A3">See All</a>
                    </div>
                    <script>
                        document.getElementById("seeAllLink").addEventListener("click", function(event){
                            event.preventDefault(); // Prevent default link behavior
                    
                            var tableContainer = document.getElementById("tableContainer");
                    
                            // Toggle the display style between "block" and "none"
                            if(tableContainer.style.display === "none") {
                                tableContainer.style.display = "block";
                            } else {
                                tableContainer.style.display = "none";
                            }
                        });
                    
                    </script>
                </div>
            </div>
            <div class="row">
                {{-- Card I --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header" style="padding: 10px;">
                            <div class="d-flex align-items-center justify-content-center">
                                <img style="width: 12%; height: auto; margin-right: 10px;" src="{{ url('/icons/rank_1.svg') }}" alt="User Icon">
                                <a href="#" style="text-decoration: none; color: black;">
                                    <p style="font-size: 18px; margin: 0;"><b>Students Name</b></p>
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <div class="d-flex flex-column align-items-center"> <!-- Container untuk gambar dan scoring -->
                                <img style="width: 100%; max-width: 130px; height: auto;"
                                     src="{{ Storage::url('public/profile/') . Auth::user()->profile_url }}"
                                     alt="Profile Image" class="avatar-img rounded-circle"
                                     onerror="this.onerror=null; this.src='{{ url('/default/default_profile.png') }}'; this.alt='Alternative Image';">
                                <div id="scoring" class="d-flex align-items-center"> <!-- Container untuk scoring -->
                                    <img style="width: 35%; height: auto; margin-right: 10px;" src="{{ url('/icons/tropy.svg') }}" alt="User Icon">
                                    <a href="#" style="text-decoration: none; color: black;">
                                        <p style="font-size: 18px; margin: 0; white-space: nowrap;"><b>$100</b></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                {{-- Card II --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header" style="padding: 10px;">
                            <div class="d-flex align-items-center justify-content-center">
                                <img style="width: 12%; height: auto; margin-right: 10px;" src="{{ url('/icons/rank_2.svg') }}" alt="User Icon">
                                <a href="#" style="text-decoration: none; color: black;">
                                    <p style="font-size: 18px; margin: 0;"><b>Students Name</b></p>
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <div class="d-flex flex-column align-items-center"> <!-- Container untuk gambar dan scoring -->
                                <img style="width: 100%; max-width: 130px; height: auto;"
                                     src="{{ Storage::url('public/profile/') . Auth::user()->profile_url }}"
                                     alt="Profile Image" class="avatar-img rounded-circle"
                                     onerror="this.onerror=null; this.src='{{ url('/default/default_profile.png') }}'; this.alt='Alternative Image';">
                                <div id="scoring" class="d-flex align-items-center"> <!-- Container untuk scoring -->
                                    <img style="width: 35%; height: auto; margin-right: 10px;" src="{{ url('/icons/tropy.svg') }}" alt="User Icon">
                                    <a href="#" style="text-decoration: none; color: black;">
                                        <p style="font-size: 18px; margin: 0; white-space: nowrap;"><b>$100</b></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>

                {{-- Card III --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header" style="padding: 10px;">
                            <div class="d-flex align-items-center justify-content-center">
                                <img style="width: 12%; height: auto; margin-right: 10px;" src="{{ url('/icons/rank_3.svg') }}" alt="User Icon">
                                <a href="#" style="text-decoration: none; color: black;">
                                    <p style="font-size: 18px; margin: 0;"><b>Students Name</b></p>
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <div class="d-flex flex-column align-items-center"> <!-- Container untuk gambar dan scoring -->
                                <img style="width: 100%; max-width: 130px; height: auto;"
                                     src="{{ Storage::url('public/profile/') . Auth::user()->profile_url }}"
                                     alt="Profile Image" class="avatar-img rounded-circle"
                                     onerror="this.onerror=null; this.src='{{ url('/default/default_profile.png') }}'; this.alt='Alternative Image';">
                                <div id="scoring" class="d-flex align-items-center"> <!-- Container untuk scoring -->
                                    <img style="width: 35%; height: auto; margin-right: 10px;" src="{{ url('/icons/tropy.svg') }}" alt="User Icon">
                                    <a href="#" style="text-decoration: none; color: black;">
                                        <p style="font-size: 18px; margin: 0; white-space: nowrap;"><b>$100</b></p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lakukan Looping Untuk Table Ini! --}}
            <div class="table-responsive" id="tableContainer">
                <table class="table" style="border-collapse: collapse;">
                    <thead style="background-color: #ebebeb;">
                        <tr class="text-center" style="font-size: 12px">
                            <th><h3><b>Rank</b></h3></th>
                            <th><h3><b></b></h3></th>
                            <th><h3><b>Name</b></h3></th>
                            <th><h3><b>Division</b></h3></th>
                            <th><h3><b>Persentage</b></h3></th>
                            <th><h3><b>Pre Test</b></h3></th>
                            <th><h3><b>Post Test</b></h3></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td>1</td>
                            <td>Img</td>
                            <td>Kevin Valerian</td>
                            <td>Digital Management</td>
                            <td>100%</td>
                            <td>95</td>
                            <td>100</td>
                        </tr>
                        <tr class="text-center">
                            <td>2</td>
                            <td>Img</td>
                            <td>Kevin Valerian</td>
                            <td>Digital Management</td>
                            <td>100%</td>
                            <td>95</td>
                            <td>100</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    
        <div class="container-fluid">
            <div class="container mt-5">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <script>
                                    $(function () { //ready
                                        toastr.error('{{ session('
                                                $error ') }}', '{{ $error }}');
                                    });
    
                                </script>
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
    
    
            </div>
        </div>
    </div>


    @if (session()->has('success'))
        <script>
            toastr.success('{{ session('
                success ') }}', '{{ session('success') }}');

        </script>
    @elseif(session()-> has('error'))
        <script>
            toastr.error('{{ session('
                error ') }}', '{{ session('
                error ') }}');

        </script>

    @endif

@endsection
