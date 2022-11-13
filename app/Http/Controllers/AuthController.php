<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\VerifyEmailToken;
use App\Services\Mail\SendVerifyEmail;
use App\Services\Mail\VerifyEmailAction;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Jobs\SendQueueVerifyEmail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(SendVerifyEmail $sendVerifyEmail, VerifyEmailAction $verifyEmailAction) {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'verifyEmail', 'resendVerifyEmail']]);
        $this->sendVerifyEmail = $sendVerifyEmail;
        $this->verifyEmailAction = $verifyEmailAction;
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|string|min:8|',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!User::where('email', $credentials['email'])->where('email_verified_at','!=', null)->exists()) {
            return response()->json([
              'status' => false,
              'message' => 'Email not verified.',
              'data' => null,
            ], Response::HTTP_UNAUTHORIZED);
        }
        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
            'email' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'phone_number' => 'required',
            'gender' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create($validator->validated());
        
        dispatch(new SendQueueVerifyEmail([
            'full_name' => $user->full_name,
            'email' => $user->email,
            'token' => $this->genVerifyEmailToken($user->id)
        ]));

        return response()->json([
            'message' => 'User successfully registered, please check your email!',
            'user' => $user
        ], 201);
    }


    public function verifyEmail(Request $request)
    {
      $token = $request->query('token');
      $verifyEmail = $this->verifyEmailAction->handle($token);
      return $verifyEmail['isValid'] ? 'Verify success' : 'Verify fail'; // redirect to frontend
    }
  
    private function genVerifyEmailToken(int $user_id)
    {
      $token = Str::random(60);
      VerifyEmailToken::create([
        'user_id' => $user_id,
        'token' => $token,
      ]);
      return $token;
    }
  
    public function resendVerifyEmail(Request $request)
    {
      $user = User::query()->where('email', $request->email)->first();
      if (is_null($user)) {
        return response()->json([
          'status' => false,
          'message' => 'Email does not exist',
          'data' => null,
        ], Response::HTTP_NOT_ACCEPTABLE);
      }
      if(!is_null($user->email_verified_at)) {
        return response()->json([
          'status' => false,
          'message' => 'Email verified',
          'data' => null,
        ], Response::HTTP_NOT_ACCEPTABLE);
      };
      VerifyEmailToken::query()->where('user_id', $user->id)->delete();
      $this->sendVerifyMail->handle([
        'full_name' => $user->full_name,
        'email' => $user->email,
        'token' => $this->genVerifyEmailToken($user->id)
      ]);
      return response()->json([
        'status' => true,
        'message' => 'Sent verify email',
        'data' => null,
      ]);
    }
  
    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60 *60,
            'csrf_token' => csrf_token()
        ]);
    }

    public function changePassWord(Request $request) {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
            ['password' => bcrypt($request->new_password)]
        );

        return response()->json([
            'message' => 'User successfully changed password',
            'user' => $user,
        ], 201);
    }
}
