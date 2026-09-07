<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //   $this->middleware('guest:web')->except('logout');
        // $this->middleware('auth:web')->only('logout');

          $this->middleware(
        'guest:admin,employee,deliveryboy,supplier'
    )->except('logout');

    $this->middleware(
        'auth:admin,employee,deliveryboy,supplier'
    )->only('logout');
    }

 
   
protected function authenticated(Request $request, $user)
{



  $role = $user->role->name;
 $this->guardName = match ($user?->role?->name) {
        'Admin'       => 'admin',
        'Employee'    => 'employee',
        'DeliveryBoy' => 'deliveryboy',
        'Supplier'    => 'supplier',
        default       => 'web',
    };
    Auth::guard('web')->logout();
    if ($role === 'Admin') {
        Auth::guard('admin')->login($user);
    } elseif ($role === 'Employee') {
        Auth::guard('employee')->login($user);
    } elseif ($role === 'DeliveryBoy') {
        Auth::guard('deliveryboy')->login($user);
    } elseif ($role === 'Supplier') {
        Auth::guard('supplier')->login($user);
    } else {
        return redirect('/login')
            ->withErrors(['email' => 'Unauthorized access.']);
    }
 

}
   protected $guardName = 'web';

protected function guard()
{


    return Auth::guard($this->guardName);
}

    public function logout(Request $request)
    {
     if (Auth::guard('admin')->check()) {
        Auth::guard('admin')->logout();

    } elseif (Auth::guard('employee')->check()) {
        Auth::guard('employee')->logout();

    } elseif (Auth::guard('deliveryboy')->check()) {
        Auth::guard('deliveryboy')->logout();

    } elseif (Auth::guard('supplier')->check()) {
        Auth::guard('supplier')->logout();
    }
     

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}