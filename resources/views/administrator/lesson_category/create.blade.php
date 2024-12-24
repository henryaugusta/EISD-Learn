@extends('main.template')

@section('head-section')
    <script src="{{ asset('library/ckeditor/ckeditor.js') }}"></script>
    <style>
        #previewCover {
            object-fit: cover;
            height: 200px;
            width: 100%;
        }
    </style>
@endsection

@section('script')
    {{-- JS-SECTION-B --}}
    <script>
        $('#tagsinput').tagsinput({
            tagClass: 'badge-info'
        });
    </script>
    <script>
        CKEDITOR.replace('content', {
            filebrowserImageBrowseUrl: '/filemanager?type=Images',
            filebrowserImageUploadUrl: '/filemanager/upload?type=Images&_token=',
            filebrowserBrowseUrl: '/filemanager?type=Files',
            filebrowserUploadUrl: '/filemanager/upload?type=Files&_token='
        });
    </script>
@endsection

@section('main')
    <script>
        window.onload = function() {
            // jQuery and everything else is loaded


            var el = document.getElementById('input-image');
            el.onchange = function() {
                var fileReader = new FileReader();
                fileReader.readAsDataURL(document.getElementById("input-image").files[0])
                fileReader.onload = function(oFREvent) {
                    document.getElementById("imgPreview").src = oFREvent.target.result;
                };
            }

            $(document).ready(function() {
                $.myfunction = function() {
                    $("#previewName").text($("#inputTitle").val());
                    var title = $.trim($("#inputTitle").val())
                    if (title == "") {
                        $("#previewName").text("Judul")
                    }
                };

                $("#inputTitle").keyup(function() {
                    $.myfunction();
                });

            });
        }
    </script>



    <div class="page-inner"  style="background-color: white !important">
        <form action="{{ route('lesson_category.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="container-fluid mt-3">
                <div class="main-content-container container-fluid px-4">

                    <!-- Page Header -->
                    <div class="page-header row no-gutters mb-4">
                        <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
                            <span class="text-uppercase page-subtitle">Lesson Category</span>
                            <h3 class="page-title">Tambah Kategori Baru</h3>
                        </div>
                    </div>

                    <!-- End Page Header -->
                    <div class="row">
                        <div class="col-lg-8 col-md-12">
                            <!-- Add New Post Form -->
                            <div class="card card-small mb-3">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Nama Kategori</label>
                                        <input id="inputTitle" type="text"
                                            class="form-control @error('title') is-invalid @enderror" name="title"
                                            value="{{ old('title') }}" placeholder="Masukkan Nama Kategori">

                                        <!-- error message untuk title -->
                                        @error('title')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Add New Post Form -->
                            <div class="card card-small mb-3">
                                <div class="card-body">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Pilih Warna</label>
                                        <input type="color" name="color_ofCategory"
                                            class="form-control @error('title') is-invalid @enderror"
                                            placeholder="Masukan Kode Hexacolor">

                                        <!-- error message untuk title -->
                                        @error('title')
                                            <div class="alert alert-danger mt-2">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- / Add New Post Form -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card border-0 shadow rounded">
                                        <div class="card-body">

                                            <div class="form-group">
                                                <span style="color: red"><label class="font-weight-bold">GAMBAR</label>*</span>
                                                <input id="input-image" accept="image/png, image/gif, image/jpeg"
                                                    type="file" onchange="previewPhoto()"
                                                    class="form-control @error('image') is-invalid @enderror"
                                                    name="image">

                                                <!-- error message untuk title -->
                                                @error('image')
                                                    <div class="alert alert-danger mt-2">
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>


                                            <button type="submit" class="btn btn-md btn-primary">SIMPAN</button>
                                            <button type="reset" class="btn btn-md btn-warning">RESET</button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- Side Bar --}}
                        <div class="col-lg-4 col-md-12">
                            {{-- Card Preview --}}
                            <div class="card card-post card-round">
                                <img class="card-img-top" id="imgPreview"
                                    src="{{ asset('atlantis/examples') }}/assets/img/blogpost.jpg" alt="Card image cap">
                                <div class="card-body">

                                </div>
                            </div>

                            <!-- Post Overview -->
                            <div class='card card-small mb-3 d-none'>
                                <div class="card-header border-bottom">
                                    <h6 class="m-0">Preview</h6>
                                </div>
                                <div class='card-body'>
                                    <p class="card-text">Ketik tag dan enter untuk memasukkan tag blog</p>
                                    <div class="form-group">
                                        <input type="text" id="tagsinput" class="form-control" value="Blog"
                                            data-role="tagsinput">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
