<?php

namespace App\Http\Controllers\Backend\AdminAuth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\Authenticate;
use App\Http\Requests;
use App\User;
use Illuminate\Contracts\Auth\Authenticable;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Validator;

use App\Http\Controllers\CommonController as Common;

class AuthController extends Controller
{
	//use AuthenticatesAndRegistersUsers, ThrottlesLogins;



    protected $redirectTo = '/admin';
    protected $guard = 'admin';

    // public function __construct()
    // {
    //     parent::__construct();
    // }


    public function showLoginForm()
    {
        if (view()->exists('auth.authenticate')) {
            return view('auth.authenticate');
        }

        return view('admin.auth.login');
    }
    public function showRegistrationForm()
    {
        return view('admin.auth.register');
    }  

    public function register(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
                        'name' => 'required|string|max:255',
                        'email'     => 'required|email',
                        'password'  => 'required',
                    ]);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors())->withInput(Input::all());
        }

        //echo "<pre>";print_r($request->input('name'));exit;

        //echo "call";exit;

        $admin_rs = User::create([
            'is_admin' => 1,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);


        if($admin_rs)
        {
            return redirect()->route("getAdminLoginPage")
                ->with(array('alert-class'=>'alert-success', 'message'=>'You are successfully logged in.'));
        }else{
            return redirect()->route('getAdminRegisterPage')
                ->with(array('alert-class'=>'alert-danger','message'=>'Something went wrong!'))->withInput(Input::all());
        }

        // $remember = ($request->has('remember')) ? true : false;
        // $data = array("email" => $request->email, "password" => $request->password, "is_admin" => '1');
        // if(Auth::attempt($data,true))
        // {
        //     return redirect()->route("dashboard")
        //         ->with(array('alert-class'=>'alert-success', 'message'=>'You are successfully logged in.'));
        // }else{
        //     return redirect()->route('getLoginPage')
        //         ->with(array('alert-class'=>'alert-danger','message'=>'Invalid email or password!'))->withInput(Input::all());
        // }
    }

	public function getLogin($value='')
    {
        return view("admin/auth/login");
    }

    public function postLogin(Request $request)
    {

        $validator = Validator::make($request->all(), [
                        'email'     => 'required|email',
                        'password'  => 'required',
                    ]);




        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors())->withInput(Input::all());
        }

        $remember = ($request->has('remember')) ? true : false;
        $data = array("email" => $request->email, "password" => $request->password, "is_admin" => '1');
        if(Auth::guard('admin')->attempt($data,true))
    	{
    		return redirect()->route("dashboard")
                ->with(array('alert-class'=>'alert-success', 'message'=>'You are successfully logged in.'));
    	}else{



    		return redirect()->route('getAdminLoginPage')
                ->with(array('alert-class'=>'alert-danger','message'=>'Invalid email or password!'))->withInput(Input::all());
    	}
    }

    public function getLogout()
    {
    	Auth::guard('admin')->logout();
    	return redirect()->route("getAdminLoginPage")
              ->with(array('alert-class'=>'alert-success', 'message'=>'Logged out successfully.'));
    }

    public function getForgotPassword()
    {
        return view("admin/auth/passwords/forgotPassword");
    }

    public function postForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'email'     => 'required|email'
                    ]);
        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors())->withInput(Input::all());
        }

        $email = $request->input('email');
        $adminUser = AdminUsers::where('email',$email)->first();

        if(!empty($adminUser))
        {
            $data = array();
            $newPassword = Common::generateCode(5);

            $data['name']           = $adminUser->name;
            $data['newPassword']    = $newPassword;
            $data['templateName']   = "emails.forgotPassword";
            $data['email']          = $email;
            $data['subject']        = "New Password Details";
            Common::sendMail($data);

            $adminUser->password = Hash::make($newPassword);
            $adminUser->save();

            return redirect()->back()->with(array('alert-class'=>'alert-success','message'=>'A mail is sent to your email address with your new password.'));
        }else{
            return redirect()->back()->with(array('alert-class'=>'alert-danger','message'=>'User with email does not exist, enter valid email!'))->withInput(Input::all());
        }
    }

    public function getChangePassword()
    {
        return view('admin/auth/passwords/changePassword');
    }

    public function postChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                        'email'                 => 'required',
                        'old_password'          => 'required',
                        'password'              => 'required|confirmed',
                        'password_confirmation' => 'required'
                    ]);
        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator->errors())->withInput(Input::all());
        }
        
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('old_password'),
        ];

        if(Auth::validate($credentials)) {
            $user = Auth::user();
            $user->password = bcrypt($request->input('password'));
            $user->save();

            return redirect()->back()->with(array('alert-class'=>'alert-success','message'=>'New Password has been changes successfully.'));

        }else{
            return redirect()->back()->with(array('alert-class'=>'alert-danger','message'=>'Invalid old password!'));
        }
    }
}
