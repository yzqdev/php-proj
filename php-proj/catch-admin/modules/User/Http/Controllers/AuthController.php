<?php

namespace Modules\User\Http\Controllers;

use Catch\Base\CatchController as Controller;
use Catch\Exceptions\FailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Modules\User\Events\Login;
use Modules\User\Models\User;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function login(Request $request): array
    {



// create a log channel
        $log = new Logger('name');
//        $log ->pushHandler(new ErrorLogHandler());
        $log->pushHandler(new StreamHandler('php://stderr'));
        $log->warning('Foo');
        $log->error('Bar');
//这样不会中断程序的运行
        /* @var User $user */
        $user = User::query()->where('email', $request->get('email'))->first();

        Event::dispatch(new Login($request, $user));

        if ($user && Hash::check($request->get('password'), $user->password)) {

            $token = $user->createToken('token')->plainTextToken;
            return compact('token');
        }

        throw new FailedException('登录失败！请检查邮箱或者密码');
    }


    /**
     * logout
     *
     * @return array
     */
    public function logout(): array
    {
        /* @var  User $user */
        $user = Auth::guard(getGuardName())->user();

        $user->currentAccessToken()->delete();

        return [];
    }
}
