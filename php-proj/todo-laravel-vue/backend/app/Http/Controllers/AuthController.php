<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    /**
    * @OA\Post(
    * path="/api/register",
    * operationId="Register",
    * tags={"Authentication"},
    * summary="User Register",
    * description="User Register here",
    *     @OA\RequestBody(
    *         @OA\JsonContent(
    *              @OA\Property(
    *                  property="name",
    *                  description="User's name",
    *                  type="text",
    *                  nullable="false",
    *                  example="John Doe"
    *              ),
    *              @OA\Property(
    *                  property="email",
    *                  description="User's email",
    *                  type="text",
    *                  nullable="false",
    *                  example="john.doe@todo.com"
    *              ),
    *              @OA\Property(
    *                  property="password_confirm",
    *                  description="Confirm password",
    *                  type="text",
    *                  nullable="false",
    *                  example="john.doe@todo.com"
    *              )
    *         ),
    *    ),
    *    @OA\Response(
    *          response=200,
    *          description="Register Successfully",
    *          @OA\JsonContent(
    *               @OA\Property(
    *                   property="status",
    *                   description="An error message",
    *                   type="boolean",
    *                   nullable="true",
    *                   example="true"
    *               ),
    *               @OA\Property(
    *                   property="message",
    *                   description="Message result when registerting a user",
    *                   type="string",
    *                   nullable="true",
    *                   example="User Created Successfully"
    *               ),
    *               @OA\Property(
    *                   property="token",
    *                   description="Access token for the newly registered user",
    *                   type="string",
    *                   nullable="true",
    *                   example="4|laravel_sanctum_xabYxNyN6P77sTNv7cIW43eWZSBFxOKfYbnTHg9R0e5d5563"
    *               ),
    *          ),
    *
    *       ),
    *      @OA\Response(
    *          response=422,
    *          description="Unprocessable Entity",
    *          @OA\JsonContent()
    *       ),
    *      @OA\Response(response=401, description="Login failed"),
    * )
    **/
    public function createUser(LoginRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    /**
    * @OA\Post(
    * path="/api/auth/login",
    * operationId="Login",
    * tags={"Authentication"},
    * summary="User Login",
    * description="User login",
    *     @OA\RequestBody(
    *         @OA\JsonContent(
    *              @OA\Property(
    *                  property="email",
    *                  description="User's email",
    *                  type="text",
    *                  nullable="false",
    *                  example="john.doe@todo.com"
    *              ),
    *              @OA\Property(
    *                  property="password",
    *                  description="password",
    *                  type="text",
    *                  nullable="false",
    *                  example="@veryHardPW!^"
    *              )
    *         ),
    *    ),
    *    @OA\Response(
    *          response=200,
    *          description="Login success",
    *          @OA\JsonContent(
    *               @OA\Property(
    *                   property="token",
    *                   description="Access token for the newly registered user",
    *                   type="string",
    *                   nullable="true",
    *                   example="4|laravel_sanctum_xabYxNyN6P77sTNv7cIW43eWZSBFxOKfYbnTHg9R0e5d5563"
    *               ),
    *          ),
    *
    *       ),
    *      @OA\Response(response=401, description="Login failed"),
    * )
    **/
    public function login(LoginRequest $request)
    {
        $check = Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password]);
        if($check) {
            $user = User::where('email', $request->email)->first();
            return $this->createUserToken($user);
        }

        abort(401, 'Login failed');
    }

    public function logout(Request $request)
    {
        Auth::user()->tokens()->delete();
        $request->session()->invalidate();
        //$request->session()->regenerateToken();
    }

    private function createUserToken(User $user)
    {
        $token = $user->createToken('token');
        return ['token' => $token->plainTextToken];
    }

}
