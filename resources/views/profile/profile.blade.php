@extends('main.template')
@section('main')

    <div class="page-inner" style="background-color: white !important">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb bg-white">
                <li class="breadcrumb-item"><a href={{url('/home')}}>Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">My Profile</li>
            </ol>
        </nav>

        <h4 class="page-title">Akun Saya</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    <div class="row">
                        {{-- SECTION PROFILE FORM --}}
                        <div class="col-md-8">
                            <div class="">
                                @csrf
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Nama Lengkap</label>
                                            <input id="inputTitle" type="text" class="form-control @error('first_name') is-invalid has-error @enderror"
                                                name="first_name" value="{{ $fullName }}" placeholder="Nama Anda"  @if(is_null($reg_code)) readonly @endif>
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nama Belakang</label>
                                            <input type="text" class="form-control-file form-control"
                                                   name="end_name" value="{{ $end_ofName }}"
                                                   id="" placeholder="" aria-describedby="fileHelpId" readonly>
                                        </div>
                                    </div> --}}
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Username</label>
                                            <input type="text"
                                                   class="form-control @error('username') is-invalid has-error @enderror"
                                                   name="username" 
                                                   value="{{ $username }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email"
                                                   class="form-control @error('email') is-invalid has-error @enderror"
                                                   name="email" placeholder="Email"
                                                   value="{{ old('email', Auth::user()->email) }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Jabatan</label>
                                            <input type="text"
                                                   class="form-control @error('email') is-invalid has-error @enderror"
                                                   name="jabatan" placeholder="Jabatan"
                                                   value="{{ $name_jbtn }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Department</label>
                                            <input type="text"
                                                   class="form-control @error('email') is-invalid has-error @enderror"
                                                   name="department" placeholder="Department"
                                                   value="{{ $name_dept }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Unit Business</label>
                                            <input type="text"
                                                   class="form-control @error('email') is-invalid has-error @enderror"
                                                   name="unit_business" placeholder="Unit Business"
                                                   value="{{ $name_sites }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-12 d-none">
                                        <div class="form-group">
                                            <label>No Telepon</label>
                                            <input type="text"
                                                   class="form-control  @error('phone') is-invalid has-error @enderror"
                                                   value="{{ old('phone', Auth::user()->contact) }}" name="phone"
                                                   placeholder="Phone">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- SECTION PROFILE PICTURE --}}
                        <div class="col-md-4">
                            <div class="card mt-5">
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="card">
                                            <img id="profileImage"
                                                 src="{{ Auth::user()->full_img_path }}"
                                                 onerror="this.onerror=null; this.src='{{ url('/default/default_profile.png') }}'; this.alt='Alternative Image';"
                                                 class="rounded"
                                                 alt="...">
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="file" name="profile_image" class="form-control"
                                                   id="inputGroupFile02" accept="image/*" onchange="previewImage()">
                                        </div>
                                        <small width="100%">Image size should be under 1 MB and image ratio needs to be 1:1</small>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex justify-content-center mt-3 mb-3">
                                        <button class="btn btn-custom w-100">Save Changes</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        

                        <script>
                            function previewImage() {
                                var input = document.getElementById('inputGroupFile02');
                                var image = document.getElementById('profileImage');
                                var reader = new FileReader();

                                reader.onload = function (e) {
                                    var img = new Image();
                                    img.src = e.target.result;

                                    img.onload = function () {
                                        // Cek apakah gambar memiliki rasio 1:1
                                        if (img.width === img.height) {
                                            image.src = e.target.result;
                                        } else {
                                            alert("Image size should be under 1 MB and image ratio needs to be 1:1");
                                            // Reset input
                                            input.value = "";
                                        }
                                    };
                                };

                                if (input.files[0]) {
                                    reader.readAsDataURL(input.files[0]);
                                }
                            }
                        </script>

                        
                    </div>
                </form>
            </div>
        </div>

        <h4 class="page-title">Media Sosial</h4>
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('profile.updateSocMed') }}" enctype="multipart/form-data">
                    <div class="row">
                        {{-- SECTION PROFILE FORM --}}
                        <div class="col-md-12">
                            <div class="">
                                @csrf
                                <div class="row mt-3">
                                    <div class="col-md-12 ">
                                        <div class="form-group">
                                            <label>Personal Website</label>
                                            <input type="text"
                                                   class="form-control @error('website') is-invalid has-error @enderror"
                                                   name="website"
                                                   value="{{ old('url_personal_website', Auth::user()->url_personal_website) }}"
                                                   placeholder="Personal website or portfolio URL"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Facebook</label>
                                            <input type="text"
                                                   class="form-control @error('facebook') is-invalid has-error @enderror"
                                                   name="facebook" placeholder="Username"
                                                   value="{{ old('url_facebook', Auth::user()->url_facebook) }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Instagram</label>
                                            <input type="text"
                                                   class="form-control @error('email') is-invalid has-error @enderror"
                                                   name="instagram" placeholder="Username"
                                                   value="{{ old('url_instagram', Auth::user()->url_instagram) }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>LinkedIn</label>
                                            <input type="text"
                                                   class="form-control @error('linkedin') is-invalid has-error @enderror"
                                                   name="linkedin" placeholder="Username"
                                                   value="{{ old('url_linkedin', Auth::user()->url_linkedin) }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Twitter</label>
                                            <input type="text"
                                                   class="form-control  @error('twitter') is-invalid has-error @enderror"
                                                   name="twitter" placeholder="Username"
                                                   value="{{ old('url_twitter', Auth::user()->url_twitter) }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Whatsapp</label>
                                            <input type="text"
                                                   class="form-control  @error('phone') is-invalid has-error @enderror"
                                                   name="whatsapp" placeholder="+62XXXXXXXXXX"
                                                   value="{{ old('url_whatsapp', Auth::user()->url_whatsapp) }}"
                                            >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Youtube</label>
                                            <input type="text"
                                                   class="form-control @error('youtube') is-invalid has-error @enderror"
                                                   name="youtube" placeholder="Username"
                                                   value="{{ old('url_youtube', Auth::user()->url_youtube) }}"
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-right mt-3 mb-3">
                                <button class="btn btn-custom">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <h4 class="page-title">Ubah Password</h4>
        <div class="card">
            <div class="card-body">
                <form method="get" action="{{ route('password.change') }}" enctype="multipart/form-data">
                    <div class="row">
                        @csrf
                        <div class="col-md-2">
                            <div class="mt-3 mb-3">
                                <button class="btn btn-custom">Change Password</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
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

    {{-- @if (session()->has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Password berhasil diubah!',
                confirmButtonText: 'OK'
            }).then(function() {
                window.location.href = '{{ route("profile") }}';
            });
        </script>
    @endif --}}

    @if (session('status'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Success!',
                text: "{{ session('status') }}",
                icon: 'success',
                confirmButtonText: 'OK',
                onClose: () => {
                    window.location.href = "{{ route('profile') }}";
                }
            });
        });
    </script>
@endif
@endsection
