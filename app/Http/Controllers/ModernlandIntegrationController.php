<?php

namespace App\Http\Controllers;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\Models\User;
use Google\Service\CloudSourceRepositories\Repo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ModernlandIntegrationController extends Controller
{

    public function proceedLoginFromIthub(Request $request)
    {
        // Step 1: Retrieve the JWT token from the request
        $token = $request->input('jwt'); // Use `input` to retrieve data from request body or query parameters

        if (!$token) {
            // If no token is provided, return an error response
            abort('401', "JWT Token is required')");
        }

        try {
            // Step 2: Verify and decode the JWT token
            $user = JWTAuth::setToken($token)->toUser();

            // Step 3: Check if the user exists and is valid
            if ($user) {
                // Log in the user
                Auth::login($user);
                Log::info('User successfully logged in', ['userId' => $user->id]);
                return redirect("/home");
            } else {
                // If user is not found or the token is invalid, return an error response
                abort('401', "Invalid JWT token or user not found");
            }
        } catch (JWTException $e) {
            // Handle any JWT related exceptions
            abort('401', 'Failed to authenticate with JWT, Unknown error occured');
            Log::error('JWT authentication failed', ['exception' => $e->getMessage()]);
        }
    }

    public function loginFromIthub(Request $request)
    {
        // Step 1: Retrieve the Authorization token from the request headers
        $token = $request->header('Authorization');
        Log::info('Received token', ['token' => $token]);


        // Create a new Guzzle client to make HTTP requests
        $client = new Client();

        try {
            $url = "https://api-ithub.modernland.co.id/api/v1/";
            // $url = "http://192.168.30.125/api/v1/";
            // $url = "https://github.modernland.co.id/api/v1/";
            // Step 2: Make a GET request to the Ithub API to fetch user profile data
            $response = $client->request('GET', "$url" . "profile", [
                'headers' => [
                    'Authorization' => "Bearer ".$token,
                ],
            ]);

            Log::info('Ithub API response', [
                'status' => $response->getStatusCode(),
                'body'   => $response->getBody()->getContents(),
            ]);

            // Step 3: Check if the response from Ithub API is successful
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                Log::info('Response data', ['data' => $data]);

                // Step 4: Verify if the user ID is present in the response
                if (!isset($data['result']['id'])) {
                    Log::error('User ID not found in response', ['data' => $data]);
                    return response()->json([
                        'meta' => [
                            'success' => false,
                            'status' => 400,
                            'message' => 'User ID not found in response from Ithub'
                        ],
                        'result' => []
                    ], 400);
                }

                $userId = $data['result']['id'];
                // Step 5: Look up the user in the local database
                $userAccount = User::where('mdln_username', $userId)->first();
                Log::info('User lookup', ['userId' => $userId, 'userAccount' => $userAccount]);

                // Step 6: If the user does not exist in the local database, return a 403 error response
                if ($userAccount == null) {
                    return response()->json([
                        'meta' => [
                            'success' => false,
                            'status' => 403,
                            'message' => 'Anda Belum Terdaftar Sebagai User di LMS, Hubungi Tim Training untuk Mendaftarkan Akun'
                        ],
                        'result' => []
                    ], 403);
                } else {
                    $userId = $userAccount->id;
                    // Step 7: Log in the user
                    Auth::loginUsingId($userId);
                    Log::info('User logged in', ['userId' => $userId]);

                    // Step 8: Generate a JWT token for the logged-in user
                    try {
                        $jwtToken = JWTAuth::fromUser($userAccount);
                        Log::info('JWT generated', ['token' => $jwtToken]);

                        // Generate the URL with the JWT token
                        $homeUrl = "https://learning.modernland.co.id/open-lms-from-ithub?jwt=" . $jwtToken;
                        // Step 9: Return a JSON response with the JWT token included in the URL
                        return response()->json([
                            'meta' => [
                                'success' => true,
                                'status' => 200,
                                'message' => 'Get data successfully'
                            ],
                            'result' => array_merge($data['result'], [
                                'redirect_url' => $homeUrl
                            ])
                        ]);
                    } catch (JWTException $e) {
                        Log::error('JWT generation failed', ['exception' => $e->getMessage()]);
                        return response()->json([
                            'meta' => [
                                'success' => false,
                                'status' => 500,
                                'message' => 'Failed to generate JWT'
                            ],
                            'result' => []
                        ], 500);
                    }
                }
            } else {
                // Step 10: Handle error responses from Ithub API
                Log::error('Ithub API request failed', [
                    'status' => $response->getStatusCode(),
                    'body'   => $response->getBody()->getContents(),
                ]);
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'status' => $response->getStatusCode(),
                        'message' => 'Failed to authenticate with Ithub'
                    ],
                    'result' => []
                ], $response->getStatusCode());
            }
        } catch (RequestException $e) {
            // Step 11: Handle any exceptions that occur during the request
            Log::error('Exception occurred', ['exception' => $e->getMessage()]);
            return response()->json([
                'meta' => [
                    'success' => false,
                    'status' => 500,
                    'message' => 'An error occurred while authenticating with Ithub. Please try again later.'.$e->getMessage()
                ],
                'result' => []
            ], 500);
        }
    }

    public function getLearningUsers(Request $request)
    {
        // Get the API credentials header
        $apiCredentials = $request->header('lms');
        if ($apiCredentials != "$2a$12$19qKjda8n9dqzygyzr3/sOvU/uhztedmcfyWOaLiqbzmidN.AI3K.") {
            $response = [
                'meta' => [
                    'success' => false,
                    'status' => 403,
                    'message' => 'Invalid API Key'
                ],
            ];

            return response()->json($response);
        }

        $query = User::query();
        // Optional filter for unclaimed=true
        if ($request->has('unclaimed') && $request->unclaimed == 'true') {
            $query->where(function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('mdln_username')
                        ->orWhere('mdln_username', '');
                });
            });
        }

        // Optional search filter
        if ($request->has('q')) {
            $search = $request->input('q');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get limit and page from request or set default values
        $limit = $request->input('limit', 10); // Default limit is 10
        $page = $request->input('page', 1); // Default page is 1

        // Paginate the results
        $users = $query->paginate($limit, ['*'], 'page', $page);

        // Prepare the response structure
        $response = [
            'meta' => [
                'success' => true,
                'status' => 200,
                'message' => 'Get data successfully'
            ],
            'result' => [
                'data' => $users->items(),
                'pagination' => [
                    'page' => $users->currentPage(),
                    'limit' => $users->perPage(),
                    'total_rows' => $users->total(),
                    'total_page' => $users->lastPage()
                ]
            ]
        ];

        return response()->json($response);
    }

    /*
     * this function will check if the MDLN userId is already registered on Learning
     */
    protected function isUserExist(Request $request, $mdln_username)
    {
        // Get the API credentials header
        $apiCredentials = $request->header('lms');
        if ($apiCredentials != "$2a$12$19qKjda8n9dqzygyzr3/sOvU/uhztedmcfyWOaLiqbzmidN.AI3K.") {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'status' => 403,
                    'message' => 'Invalid API Key'
                ]
            ], 403);
        }

        $user = User::where('mdln_username', $mdln_username)->first();
        if (!$user) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'status' => 403,
                    'message' => 'User doesnt exist'
                ]
            ], 403);
        }

        return response()->json([
            'meta' => [
                'success' => true,
                'status' => 200,
                'message' => 'User exists'
            ],
            'data' => $user
        ], 200);
    }


    public function createOrUpdateLMSUser(Request $request)
    {
        // return $request->all();
        // Get the API credentials header
        $apiCredentials = $request->header('lms');
        if ($apiCredentials != "$2a$12$19qKjda8n9dqzygyzr3/sOvU/uhztedmcfyWOaLiqbzmidN.AI3K.") {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'status' => 403,
                    'message' => 'Invalid API Key'
                ]
            ], 403);
        }

        if ($request->department_id)
            $departmentExists = DB::connection('ithub')
                ->table('m_departments')
                ->where('id', '=', $request->department_id)
                ->whereNull('deleted_at')
                // ->where('code', 'like', '%_NEW%')
                ->exists();

        if ($request->position_id)
            $positionExists = DB::connection('ithub')
                ->table('m_group_employees')
                ->where('id', '=', $request->position_id)
                ->exists();

        if ($request->department_id)
            $departments = DB::connection('ithub')
                ->table('m_departments')
                ->whereNull('deleted_at')
                // ->where('code', 'like', '%_NEW%')
                ->get();

        if ($request->position_id)
            $positions = DB::connection('ithub')
                ->table('m_group_employees')
                ->whereNull('deleted_at')
                // ->where('code', 'like', '%_NEW%')
                ->get();


        if ($request->department_id)
            if (!$departmentExists) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'status' => 403,
                        'message' => "Department Id $request->department_id doesnt exist"
                    ],
                    'data' => [
                        $departments
                    ]
                ], 403);
            }

        if ($request->position_id)
            if (!$positionExists) {
                return response()->json([
                    'meta' => [
                        'success' => false,
                        'status' => 403,
                        'message' => "Position Id $request->position_id doesnt exist"
                    ],
                    'data' => [
                        $positions
                    ]
                ], 403);
            }

        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mdln_username' => 'required|string|max:255',
            'contact' => 'nullable|string|max:20',
            'role' => 'nullable|string|max:20',
            'department_name' => 'nullable|string|max:255',
            'department_id' => 'nullable|string|max:255',
            'sub_department' => 'nullable|string|max:255',
            'position_id' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255', // Ensure email is validated
        ]);



        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'success' => false,
                    'status' => 422,
                    'message' => 'Validation errors',
                ],
                'errors' => $validator->errors(),
            ], 422);
        }

        // Find existing user or create a new instance
        $user = User::firstOrNew(['mdln_username' => $request->input('mdln_username')]);

        // Set user attributes
        $user->name = $request->input('name');
        $user->email = $request->input('email', $user->email);

        if($request->password!= null || $request->password != '' || $request->password){
            $user->password = bcrypt($request->password);
        }else{
            // cari password lama;
            $userIthub = DB::connection('ithub')->table('users') // assuming 'users' is your table name
            ->where('id', $request->input('mdln_username')) // replace 'something' with the actual ID or variable
            ->first();

            $user->password = $userIthub->password;
        }

        $user->role = $request->input('role', "student");
        $user->contact = $request->input('contact', $user->contact);
        $user->sub_department = $request->input('sub_department', $user->sub_department);
        $user->department_id = $request->input('department_id', $user->department_id);
        $user->position_id = $request->input('position_id', $user->position_id);
        $user->location = json_encode($request->input('location', $user->location));

        $location = json_encode($request->input('location', $user->location));
        //remove \ or / characters from location
        $location = str_replace(['\\', '/'], '', $location);


        if ($request->location == null || $request->location == 'null' || $request->location == '[]') {
            $location = "[]";
        }

        $user->location = $location;
        // Save user

        $user->save();

        if ($user->location != null && $user->location != '[]' && $user->location != 'null')
            $user->location = json_decode($user->location);

        // Specify the attributes you want to return
        $attributesToReturn = ['name', 'email', 'role', 'contact', 'department_id', 'position_id', 'location', 'mdln_username'];

        return response()->json([
            'meta' => [
                'success' => true,
                'status' => 200,
                'message' => $user->wasRecentlyCreated ? 'User created successfully' : 'User updated successfully',
            ],
            'result' => [
                'user' => $user->only($attributesToReturn),
            ],
        ], 200);
    }
}
