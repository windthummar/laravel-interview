<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\sendOtpMail;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'user_name' => ['required', 'string', 'min:4','max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar' => ['required', 'file','image','dimensions:width=256,height=256'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'avatar' => $data['avatar'],
            'user_name' => $data['user_name'],
            'email_otp' => $data['email_otp'],
            'user_role' => $data['user_role'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }


    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $input = $request->all();
        $path = $request->file('avatar')->store('public/avatar');
        $input['avatar'] = $path;
        $input['user_role'] = 'user';
        $input['email_otp'] = rand(100000,999999);
        event(new Registered($user = $this->create($input)));

//        $this->guard()->login($user);

        if ($response = $this->registered($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }


    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        Mail::to($user)->send(new sendOtpMail($user));
       return array('response' => 'You are registered successfully, Please check your email.', 'success'=>true);
        //
    }

    protected function validatorOtpVerify(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users'],
            'email_otp' => ['required', 'integer', 'min:6', 'exists:users'],
        ]);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function registerOtpVerify(Request $request)
    {
        $this->validatorOtpVerify($request->all())->validate();
        $user = User::where('email', $request->get('email'))->where('email_otp', $request->get('email_otp'))->first();
        if (!$user){
            return array('response' => 'Email verification failed.', 'success'=>false);
        }
        $user->email_otp = null;
        $user->email_verified_at =Date::now();
        $user->save();
        return array('response' => 'Email verified successfully.', 'success'=>true);
    }
}
