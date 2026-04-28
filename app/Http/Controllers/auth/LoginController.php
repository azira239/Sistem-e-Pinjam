<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
  public function showLogin()
  {
    $pageConfigs = ['myLayout' => 'blank'];

    return view('content.authentications.login-personel', compact('pageConfigs'));
  }

  public function login(Request $request)
  {
    if ($request->login_type === 'admin') {
      $request->validate([
        'nokp' => ['required'],
        'password' => ['required'],
      ]);

      if (
        Auth::attempt([
          'nokp' => $request->nokp,
          'password' => $request->password,
        ])
      ) {
        $request->session()->regenerate();

        return redirect()->route('dashboard.admin');
      }

      return back()->withErrors([
        'login' => 'No KP atau password pentadbir tidak sah.',
      ]);
    }

    if ($request->login_type === 'staff') {
      $request->validate([
        'mykad' => ['required'],
      ]);

      $staff = Staff::where('mykad', $request->mykad)
        ->where('id_status_pegawai', 1)
        ->first();

      if ($staff) {
        Auth::guard('staff')->login($staff);
        $request->session()->regenerate();

        return redirect()->route('staff.profile');
      }

      return back()->withErrors([
        'login' => 'MyKad tidak wujud atau pegawai tidak aktif.',
      ]);
    }

    return back()->withErrors([
      'login' => 'Jenis login tidak sah.',
    ]);
  }

  public function logout(Request $request)
  {
    Auth::logout();
    Auth::guard('staff')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
  }
}
