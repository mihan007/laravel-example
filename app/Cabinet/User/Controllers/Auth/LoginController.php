<?php

namespace App\Cabinet\User\Controllers\Auth;

use App\Domain\Company\Models\Company;
use App\Domain\User\Models\User;
use App\Domain\User\Services\ActivationService;
use App\Support\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * @var ActivationService
     */
    protected $activationService;

    /**
     * Create a new controller instance.
     *
     * @param ActivationService $activationService
     */
    public function __construct(ActivationService $activationService)
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->activationService = $activationService;
    }

    public function doRedirect()
    {
        $currentUser = current_user();
        if ($currentUser->isClient) {
            return redirect()->intended();
        }

        $previousCookieName = User::getPreviousAccountCookieName();
        $previousAccountId = request()->cookie($previousCookieName);

        if ($previousAccountId && $currentUser->hasAccessToAccount($previousAccountId)) {
            return redirect()->route('account.companies.index', ['accountId' => $previousAccountId]);
        }

        if ($currentUser->isSuperAdmin) {
            return redirect()->route('accounts.index');
        }

        $account = $currentUser->account;
        if (!$account) {
            abort(403, 'У вас нет аккаунта. Обратитесь к администратору.');
        }

        return redirect()->route('account.companies.index', ['accountId' => $account->id]);
    }

    /**
     * @param Request $request
     * @param $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authenticated(Request $request, $user)
    {
        if (!$user->activated) {
            $this->activationService->sendActivationMail($user);
            auth()->logout();

            return back()->with('warning', 'Вам нужно подтвердить свой аккаунт. Мы отправили вам письмо на почту.');
        }

        return $this->doRedirect();
    }
}
