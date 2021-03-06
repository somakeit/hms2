<?php

namespace App\Http\Controllers\Auth;

use App\Events\Users\UserPasswordChanged;
use App\Http\Controllers\Controller;
use HMS\Auth\PasswordStore;
use HMS\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangePasswordController extends Controller
{
    /**
     * @var PasswordStore
     */
    protected $passwordStore;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PasswordStore $passwordStore)
    {
        $this->passwordStore = $passwordStore;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        return view('user.changePassword')->with('user', Auth::user());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $valideCurrentPassword = Auth::guard()->validate([
            'username' => $user->getUsername(),
            'password' => $request->currentPassword,
        ]);

        if (! $valideCurrentPassword) {
            flash('Your current password does not matches with the password you provided. Please try again.')->error();

            return redirect()->back();
        }

        if (strcmp($request->get('currentPassword'), $request->get('password')) == 0) {
            //Current password and new password are same
            flash('New Password cannot be same as your current password. Please choose a different password.')->error();

            return redirect()->back();
        }

        $this->validate($request, [
            'currentPassword' => 'required',
            'password' => 'required|min:' . User::MIN_PASSWORD_LENGTH . '|confirmed',
        ]);

        $this->setUserPassword($user, $request->password);

        flash('Your password has been updated.')->success();

        event(new UserPasswordChanged($user));

        Auth::guard()->login($user);

        return redirect()->route('home');
    }

    /**
     * Set the user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param string  $password
     *
     * @return void
     */
    protected function setUserPassword($user, $password)
    {
        $this->passwordStore->setPassword($user->getUsername(), $password);
    }
}
