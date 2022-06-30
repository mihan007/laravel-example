<?php

namespace App\Cabinet\User\Controllers\Auth;

use App\Domain\User\Models\User;
use App\Domain\User\Services\ActivationService;
use App\Support\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
    protected $redirectTo = '/';

    protected $activationService;

    public function activateUser($token)
    {
        if ($user = $this->activationService->activateUser($token)) {
            auth()->login($user);

            return redirect($this->redirectPath());
        }
        abort(404);
    }

    public function __construct(ActivationService $activationService)
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->activationService = $activationService;
    }

    public function register(Request $request)
    {
        return '';

        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->activationService->sendActivationMail($user);

        return redirect('/confirmation');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showRegistrationForm()
    {
        return back();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\Domain\User\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
