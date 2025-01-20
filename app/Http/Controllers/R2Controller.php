<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class R2Controller extends Controller
{
    public function index()
    {
        $uploadedFiles = [];
        $logFilePath = public_path('uploads_log.txt');

        if (file_exists($logFilePath)) {
            $uploadedFiles = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            // Skip the header line if it exists
            if (!empty($uploadedFiles) && $uploadedFiles[0] === "Uploaded Files Log") {
                array_shift($uploadedFiles);
            }
        }

        return view('r2', compact('uploadedFiles'));
    }

    public function upload(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return redirect()->back()->with('error', 'No file provided.')->with('status', 'error');
            }

            $file = $request->file('file');
            if (!$file->isValid()) {
                return redirect()->back()->with('error', 'Invalid file uploaded.')->with('status', 'error');
            }

            $fileName = $file->hashName();
            $path = Storage::disk('r2')->put('uploads/' . $fileName, file_get_contents($file));

            if (!$path) {
                return redirect()->back()->with('error', 'Failed to upload the file to Cloudflare R2.')->with('status', 'error');
            }

            $r2Url = env('CLOUDFLARE_R2_URL');
            $fullUrl = $r2Url . '/uploads/' . $fileName;

            // Ensure the uploads_log.txt file exists
            $logFilePath = public_path('uploads_log.txt');
            if (!file_exists($logFilePath)) {
                // Create the file and add a header for readability
                file_put_contents($logFilePath, "Uploaded Files Log\n", FILE_APPEND);
            }

            // Append the uploaded file information to uploads_log.txt
            $logEntry = $fileName . ' | ' . $fullUrl;
            file_put_contents($logFilePath, $logEntry . PHP_EOL, FILE_APPEND);

            return redirect()->back()->with('success', 'File uploaded successfully!')->with('status', 'success');

        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while uploading the file.')->with('status', 'error');
        }
    }

    public function download($filename)
    {
        try {
            // Generate the full path to the file in the R2 bucket
            $filePath = 'uploads/' . $filename;

            // Check if the file exists in the R2 bucket
            if (!Storage::disk('r2')->exists($filePath)) {
                return redirect()->back()->with('error', 'File not found.')->with('status', 'error');
            }

            // Get the file content
            $fileContent = Storage::disk('r2')->get($filePath);

            // Return the file as a downloadable response
            return response($fileContent, 200)
                ->header('Content-Type', Storage::disk('r2')->mimeType($filePath))
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('File download error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while downloading the file.')->with('status', 'error');
        }
    }

    public function delete($filename)
    {
        try {
            // Generate the full path to the file in the R2 bucket
            $filePath = 'uploads/' . $filename;

            // Check if the file exists in the R2 bucket
            if (!Storage::disk('r2')->exists($filePath)) {
                return redirect()->back()->with('error', 'File not found.')->with('status', 'error');
            }

            // Delete the file from the R2 bucket
            Storage::disk('r2')->delete($filePath);

            // Update the uploads_log.txt file
            $logFilePath = public_path('uploads_log.txt');
            if (file_exists($logFilePath)) {
                $logEntries = file($logFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                // Remove the header if present
                $header = "Uploaded Files Log";
                if (!empty($logEntries) && $logEntries[0] === $header) {
                    array_shift($logEntries);
                }

                // Filter out the entry for the deleted file
                $logEntries = array_filter($logEntries, function ($entry) use ($filename) {
                    return !str_contains($entry, $filename);
                });

                // Re-add the header if needed and save the log
                file_put_contents($logFilePath, $header . PHP_EOL . implode(PHP_EOL, $logEntries) . PHP_EOL);
            }

            return redirect()->back()->with('success', 'File deleted successfully.')->with('status', 'success');

        } catch (\Exception $e) {
            Log::error('File delete error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'An error occurred while deleting the file.')->with('status', 'error');
        }
    }
}
