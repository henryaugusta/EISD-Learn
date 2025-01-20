<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloudflare R2 File Upload and Download</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #preview {
            display: none;
            max-width: 100%;
            max-height: 300px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h2 class="mb-4">Cloudflare R2 File Upload and Download</h2>

        <!-- Success/Error Messages -->
        @if(session('status'))
            <div class="alert alert-{{ session('status') === 'success' ? 'success' : 'danger' }}">
                {{ session('error') ?? session('success') }}
            </div>
        @endif

        <!-- File Upload Form -->
        <form action="{{ route('r2.upload') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Upload a File</label>
                <input type="file" class="form-control" id="file" name="file" required onchange="previewImage(event)">
            </div>

            <!-- Image Preview -->
            <img id="preview" src="" alt="Image Preview" />

            <button type="submit" class="btn btn-primary mt-3">Upload</button>
        </form>

        <!-- Display Upload Result -->
        @if(session('file_url'))
            <div class="alert alert-success mt-3">
                File uploaded successfully! <a href="{{ session('file_url') }}" target="_blank">{{ session('file_url') }}</a>
                <div class="mt-3">
                    <h4>Preview:</h4>
                    <img src="{{ session('file_url') }}" class="img-fluid" alt="Uploaded File">
                </div>
            </div>
        @endif

        <hr>

        <!-- Uploaded Files Table -->
        <h3 class="mt-4">Uploaded Files</h3>
        @if(!empty($uploadedFiles))
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>URL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($uploadedFiles as $file)
                        @php
                            list($filename, $url) = explode(' | ', $file);
                        @endphp
                        <tr>
                            <td>{{ $filename }}</td>
                            <td><a href="{{ $url }}" target="_blank">{{ $url }}</a></td>
                            <td>
                                <!-- Download Button -->
                                <form action="{{ route('r2.download', ['filename' => $filename]) }}" method="GET" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">Download</button>
                                </form>

                                <!-- Delete Button -->
                                <form action="{{ route('r2.delete', ['filename' => $filename]) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No files uploaded yet.</p>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to preview image before upload
        function previewImage(event) {
            const preview = document.getElementById("preview");
            const file = event.target.files[0];
            const reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.style.display = "block";  // Show the image preview
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
