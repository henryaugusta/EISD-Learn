<?php

namespace App\Helper;


use App\Models\MyAnalyticEvent;
use App\Models\User;
use App\Models\UserMNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class MyHelper
{

    public static function IndonesianDateTimeline()
    {
        $date = Carbon::now()->locale('id');

        $date->settings(['formatFunction' => 'translatedFormat']);

        return $date->format('l, j F Y');
    }

    public static function hello()
    {
        return "hello";
    }

    public static function isAPI(Request $request)
    {
        if ($request->is('api/*')) {
            return true;
        } else {
            return false;
        }
    }


    public static function responseSuccessWithData(
        $http_code,
        $status_code,
        $api_code,
        $message_id,
        $message_en,
        $res_data
    ) {
        $response = [
            'http_response' => $http_code,
            'status_code' => $status_code,
            'api_code' => $api_code,
            'message' => $message_id,
            'message_id' => $message_id,
            'message_en' => $message_en,
            'success' => true,
            'data' => $res_data,
        ];

        return response($response, $http_code);
    }

    public static function responseSuccessWithData_2(
        $http_code,
        $status_code,
        $api_code,
        $message_id,
        $message_en,
        $res_data_param,
        $res_data
    ) {
        $response = [
            'http_response' => $http_code,
            'status_code' => $status_code,
            'api_code' => $api_code,
            'message_id' => $message_id,
            'message_en' => $message_en,
            $res_data_param => $res_data,
        ];

        return response($response, $http_code);
    }


    public static function generateJwtToken($user_id)
    {
        $token = JWTAuth::fromUser(User::find($user_id), ['exp' => Carbon::now()->addDays(168)->timestamp]);
        return $token;
    }


    public static function responseErrorWithData(
        $http_code,
        $status_code,
        $api_code,
        $message_id,
        $message_en,
        $res_data
    ) {
        $response = [
            'http_response' => $http_code,
            'status_code' => $status_code,
            'api_code' => $api_code,
            'message' => $message_id,
            'message_id' => $message_id,
            'message_en' => $message_en,
            'res_data' => $res_data,
        ];

        return response($response, $http_code);
    }

    public static function removeFile($file_path)
    {
        // remove photo first
        if (!Str::contains($file_path, 'razky_samples'))
            File::delete($file_path);

        if (Str::contains($file_path, "http")) {
            try {
                if (file_exists($file_path)) {
                    $path = parse_url($file_path, PHP_URL_PATH);
                    $absolute_path = $_SERVER['DOCUMENT_ROOT'] . $path;
                    unlink($absolute_path);
                }
            } catch (Exception $e) {
            }
        }
    }

    public static function saveFile($file, $destinationPath)
    {
        $filename = Str::random(40); // Generate a unique filename
        $file->move($destinationPath, $filename);

        return $filename;
    }

    public static function logout()
    {
        return response()->json(['error' => 'Unauthenticated.'], 401);
    }

    public static function addAnalyticEventMobile(
        $event,
        $page,
        $userId
    ) {
        $data = new MyAnalyticEvent();
        $is_logged_in = false;
        $data->user_id = $userId;
        $data->event = $event;
        $data->is_logged_in = $is_logged_in;
        $data->page = $page;

        $data->save();
    }

    public static function addAnalyticEvent(
        $event,
        $page
    ) {
        $data = new MyAnalyticEvent();
        $userId = "";
        $is_logged_in = false;
        if (Auth::check()) {
            $is_logged_in = true;
            $userId = Auth::id();
        }
        $data->user_id = $userId;
        $data->event = $event;
        $data->is_logged_in = $is_logged_in;
        $data->page = $page;

        $data->save();
    }

    public static function error($code, $message)
    {
        return response()->json(["code" => $code, 'message' => "$message"], $code);
    }

    public static function error3($code, $statusCode, $message)
    {
        return response()->json(['message' => "$message", "status_code" => $statusCode], $code);
    }

    public static function success($code, $message)
    {
        return response()->json(["code" => $code, 'message' => "$message"], $code);
    }

    public static function checkApiKey($key)
    {
        $check = DB::table('api_key')
            ->where('key', '=', "$key")
            ->count();

        if ($check == 0) {
            return response()->json([
                'message' => "Unauthorized, Api Key Mismatch",
                'http_response' => 401,
                'status_code' => 0,
            ], 401);
        }
        return null;
    }

    public static function insertNotification(
        $userId,
        $title,
        $message,
        $desc,
        $type
    ) {
        $object = new UserMNotification();
        $object->user_id = $userId;
        $object->message = $message;
        $object->title = $title;
        $object->desc = $desc;
        $object->type = $type;
        $object->save();
    }

    public static function profileImgPath()
    {
        return "photo/profile";
    }

    public static function newsImgPath()
    {
        return "photo/news";
    }

    public static function reqSellImgPath()
    {
        return "photo/profile";
    }

    public static function mySuccess($data = null, $message = 'Success', $status = 200)
    {
        return self::generateResponse(true, $data, $message, $status);
    }

    public static function myError($message = 'Error', $status = 500)
    {
        return self::generateResponse(false, null, $message, $status);
    }

    private static function generateResponse($success, $data, $message, $status)
    {
        $statusCode = 0;
        if ($success) {
            $statusCode = 1;
        } else {
            $statusCode = 0;
        }
        return response()->json([
            'http_response' => $status,
            'status_code' => $statusCode,
            'meta' => [
                'success' => $success,
                'status' => $status,
                'message' => $message,
            ],
            'result' => $data,
        ], $status);
    }
}
