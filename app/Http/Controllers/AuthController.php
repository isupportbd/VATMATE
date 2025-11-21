<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Throwable;

class AuthController extends Controller
{
  public $role;
  public $user;
  public function __construct(Role $role, User $user)
  {
    $this->role = $role;
    $this->user = $user;
  }

  // view page
  public function login()
  {
    return Inertia('authentication/Login');
  }

  // view page
  public function forgetPassword()
  {
    return inertia('authentication/ForgetPassword');
  }

  // view page
  public function resetPassword()
  {
    return Inertia('authentication/ResetPassword');
  }

  // view page
  public function userProfile()
  {
    $user = $this->user->with(['role'])->find(Auth::user()->id);
    return inertia('authentication/UserProfile', ['loginuser' => $user]);
  }

  // function call from login
  public function userLogin(Request $request)
  {
    $email = $request->input('email');
    $password = $request->input('password');
    $remember = $request->input('remember');

    if (empty($email) || empty($password)) {
      return back()->withErrors(['error' => 'Invalid credential']);
    }

    if (Auth::attempt(['email' => $email, 'password' => $password, 'status' => '1'], $remember)) {
      $request->session()->regenerate();
      // return redirect()->route("dashboard");
      return Inertia::location(route('dashboard')); // need page reload for js assets load
    }

    return back()->withErrors(['error' => 'Invalid credential'])->onlyInput(['error']);
  }

  // function call from header layout logout
  public function userLogout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return Inertia::location(route('login'));
    // return Redirect::route('login');
  }

  // function call from forget password page
  public function userForgetPassword(Request $request)
  {
    $formField = $request->validate(
      [
        'email' => [
          'required',
          'email',
          function ($attribute, $value, $fail) {
            $result = $this->user->where('email', $value)->exists();
            (!$result) && $fail('Not found in system');
          }
        ]
      ],
      [
        'email.required' => 'Registered email required',
        'email.email' => 'Email not valid format'
      ]
    );

    $user = $this->user->where('email', $formField['email'])->first();
    $user->makeVisible(['forget_password']);

    $mailData = 'Your password is "' . $user['forget_password'] . '"';
    Mail::raw($mailData, function ($message) use ($user) {
      $message->to($user['email']);
      $message->subject('Forget password from VATMATE');
    });

    return Redirect::route('forget-password')->with('message', 'Check your email');
  }

  // api call for sending email
  public function mailSend(Request $request)
  {
    $formField = $request->validate(
      [
        'email' => 'required|email|max:255',
        'mailbody' => 'required',
      ],
      [
        'email.required' => 'Email required',
        'email.email' => 'Email not valid format',
        'email.max' => 'Max 255 char',
        'mailbody.required' => 'Body required',
      ]
    );

    $mailData = $formField['mailbody'];
    Mail::raw($mailData, function ($message) use ($formField) {
      $message->to($formField['email']);
      $message->subject('Username & Password');
    });

    return response()->json(['msg' => 'Sent']);
  }

  // function call from user
  public function index(Request $request)
  {
    $user = Auth::user();
    $role = optional($user->role)->name;
    $search = $request->input('search');

    $exclude = [];
    if ($role === 'superadmin') $exclude = ['superadmin', 'editor'];
    if ($role === 'admin')      $exclude = ['superadmin', 'admin'];

    $query = $this->user
      ->with('role')
      ->when($exclude, fn($q) => $q->whereHas('role', fn($r) => $r->whereNotIn('name', $exclude)))
      ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
      ->orderByDesc('id')
      ->paginate((int)request('size', 10))
      ->withQueryString();

    return Inertia('authentication/index', ['user' => $query, 'search' => $search]);
  }

  // function call from user page create user
  public function store(Request $request)
  {
    $user = Auth::user();
    $formField = $request->validate(
      [
        'name' => 'nullable|max:100',
        'email' => 'required|email|unique:users,email|max:255',
        'mobile' => 'required|max:14',
        'password' => 'required|max:255',
        'confirm_password' =>  'required|same:password',
        'role_id' => ['required', function ($attributes, $value, $fail) use ($request, $user) {
          $role = $this->role->find($value);
          if ($user->role->name == 'superadmin') {
            (!in_array($role->name, ['admin'])) &&  $fail("Can't create " . $role->name);
          }

          if ($user->role->name == 'admin') {
            (in_array($role->name, ['superadmin', 'admin'])) && $fail("Can't create " . $role->name);
          }
        }],
      ],
      [
        'name.max' => 'Max 100 char',
        'email.required' => 'Email required',
        'email.max' => 'Max 255 char',
        'email.unique' => 'Exists, change your input',
        'email.email' => 'Format not valid',
        'mobile.required' => 'Mobile required',
        'mobile.max' => 'Max 14 char',
        'password.required' => 'Password required',
        'password.max' => 'Max 255 char',
        'confirm_password.required' =>  'Confirm password required',
        'confirm_password.same' => 'Password not matched',
        'role_id.required' => 'Role required',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request) {
        unset($formField['confirm_password']);
        $formField['forget_password'] = $request->input('password');
        $formField['status'] = '1';

        $result = User::create($formField);

        if ($result) {
          $user = $this->user->where('email', $formField['email'])->first();
          $user->makeVisible(['forget_password']);
          $mailData = "Dear " . $formField['name'] . ",\n\nCongratulations! Your username and password were successfully created on " . Carbon::parse($user->created_at)->format('d/m/Y') . ".\n\nYou may now log in to our web application by clicking on the Login link using the following username and password.\n\n\nLogin Link: https://www.vatmate.org\n\nUsername: " . $formField['email'] . "\nPassword: " . $formField['forget_password'] . "\n\n\nYou can change your password by clicking your profile in the window's top right corner. Select My Profile, click the edit button in the top right corner, set a new password for it, and put it again to confirm. Then save it. You cannot change your mobile.\n\nIf you have any questions you may contact the Contact Center at 01719950891 and mention your email.\n\n\nBest Regards,\n\nvatmate.org";
          Mail::raw($mailData, function ($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Welcome to vatmate.org');
          });
        }
      });

      DB::commit();
      return back()->with('message', 'User created');
    } catch (Throwable $e) {
      DB::rollBack();
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from user page for single user
  public function show($id)
  {
    $user = Auth::user();
    $role = optional($user->role)->name;

    $query = $this->user->with('role');

    if ($role === 'superadmin') {
      $query->whereHas('role', fn($r) => $r->where('name', 'admin'));
    } elseif ($role === 'admin') {
      $query->whereHas('role', fn($r) => $r->whereNotIn('name', ['superadmin', 'admin']));
    } else {
      $query->where('id', $user->id);
    }

    $result = $query->where('id', $id)->first();

    return response()->json(['user' => $result]);
  }

  // function call from user page for update
  public function update(Request $request, $id)
  {
    $userSession = Auth::user();
    $formField = $request->validate(
      [
        'name' => 'nullable|max:100',
        'email' => ['required', 'email', 'max:255', function ($attribute, $value, $fail) use ($id) {
          $email = $this->user->where('id', $id)->value('email');
          if ($email !== $value) {
            $fail('Email not matched');
          }
        }],
        'mobile' => 'required|max:14',
        'password' => 'sometimes|required_with:confirm_password|max:255',
        'confirm_password' => 'sometimes|required_with:password|same:password',
        'role_id' => ['required', function ($attributes, $value, $fail) use ($request, $userSession) {
          $role = $this->role->find($value);
          if ($userSession->role->name == 'superadmin') {
            (!in_array($role->name, ['admin'])) && $fail("Can't assign " . $role->name);
          }

          if ($userSession->role->name == 'admin') {
            (in_array($role->name, ['superadmin', 'admin'])) && $fail("Can't assign " . $role->name);
          }
        }],
      ],
      [
        'name.max' => 'Max 100 char',
        'role_id.required' => 'Role required',
        'email.required' => 'Email required',
        'email.email' => 'Format not valid',
        'email.max' => 'Max 255 char',
        'mobile.required' => 'Mobile required',
        'mobile.max' => 'Max 14 char',
        'password.required_with' => 'Password required',
        'password.max' => 'Max 255 char',
        'confirm_password.required_with' =>  'Confirm password required',
        'confirm_password.same' => 'Password not matched',
        'role_id.required' => 'Role required',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $id) {
        if (empty($formField['password'])) {
          unset($formField['password']);
          unset($formField['confirm_password']);
          unset($formField['email']);
        } else {
          unset($formField['email']);
          unset($formField['confirm_password']);
        }

        $result = $this->user->where('id', $id)->update(
          empty($request->input('password')) ? $formField : array_merge($formField, [
            'password' => Hash::make($request->input('password')),
            'forget_password' => $request->input('password')
          ])
        );

        if ($result) {
          $user = $this->user->where('email', $request->input('email'))->first();
          $user->makeVisible(['forget_password']);
          $mailData = "Hi " . $user['name'] . "\n\nYour profile successfully updated on " . Carbon::parse($user->updated_at)->format('d/m/Y') . ". You may now log in to our web application by clicking on the Login link using the new password.\n\n\nLogin Link: https://www.vatmate.org\n\nUsername: " . $request->input('email') . "\nPassword: " . $user['forget_password'] . "\n\n\n\nYou can change your password by clicking your profile in the window's top right corner. Select My Profile, click the edit button in the top right corner, set a new password for it, and put it again to confirm. Then save it. You cannot change your mobile.\n\nIf you have any questions you may contact the Contact Center at 01719950891 and mention your email.\n\n\nBest Regards,\n\nvatmate.org";
          Mail::raw($mailData, function ($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Profile has been updated by vatmate');
          });
        }
      });

      DB::commit();
      return back()->with('message', 'User updated');
    } catch (Throwable $e) {
      DB::rollBack();
      return back()->with("error", $e->getMessage());
    }
  }

  // function call from user page for delete user
  public function destroy($id)
  {
    $user = Auth::user();
    $ids = explode(',', $id);
    $query = $this->user->with(['role'])->whereIn('id', $ids);

    if ($user->role->name == 'superadmin') {
      $query->whereHas('role', fn($query) => $query->whereIn('name', ['admin']));
    }

    if ($user->role->name == 'admin') {
      $query->whereHas('role', fn($query) => $query->whereNotIn('name', ['superadmin', 'admin']));
    }

    $result = $query->delete();
    return back()->with('message', "Deleted {$result} user(s)");
  }

  // function call from user page status click
  public function statusUpdate(Request $request, $id)
  {
    $user = Auth::user();
    $formField = $request->validate(
      [
        'status' => ['required', 'in:0,1', function ($attribute, $value, $fail) use ($id, $user) {
          $user = $this->user->with(['role'])->where('id', $id)->first();
          if (in_array($user->role->name, ['superadmin'])) {
            in_array($user->role->name, ['superadmin']) && $fail("Can't update status");
          }
          if (in_array($user->role->name, ['admin'])) {
            in_array($user->role->name, ['superadmin', 'admin']) && $fail("Can't update status");
          }
        }]
      ],
      ['status.required' => 'Status required', 'status.in' => 'Status will be 0 or 1']
    );

    $this->user->with(['role'])->where('id', $id)->update($formField);
    return back()->with('message', 'User status updated');
  }

  // function call from profile page
  public function profileUpdate(Request $request)
  {
    $userSession = Auth::user();
    $formField = $request->validate(
      [
        'name' => 'required|max:100',
        'mobile' => 'required|max:14',
        'email' => 'required|email|max:255',
        'password' => 'sometimes',
        'confirm_password' => 'sometimes|required_with:password|same:password',
      ],
      [
        'name.required' => 'Name required',
        'name.max' => 'Max 150 char',
        'mobile.required' => 'Mobile required',
        'mobile.max' => 'Max 14 char',
        'email.required' => 'Email required',
        'email.email' => 'Email not valid format',
        'email.max' => 'Max 255 char',
        'confirm_password.same' => 'Password not matched',
      ]
    );

    try {
      DB::transaction(function () use ($formField, $request, $userSession) {
        if (!$formField['password']) {
          unset($formField['password']);
          unset($formField['confirm_password']);
          unset($formField['email']);
        } else {
          unset($formField['confirm_password']);
          unset($formField['email']);
        }

        $result = $this->user->where('id', $userSession->id)->update(
          empty($request->input('password')) ? $formField : array_merge($formField, [
            'password' => Hash::make($request->input('password')),
            'forget_password' => $userSession->role->name == 'superadmin' ? NULL : $request->input('password'),
          ])
        );

        if ($result) {
          $user = $this->user->where('email', $request->input('email'))->first();
          $user->makeVisible(['forget_password']);
          $mailData = "Hi " . $user['name'] . "\n\nYour profile successfully updated on " . Carbon::parse($user->updated_at)->format('d/m/Y') . ". You may now log in to our web application by clicking on the Login link using the new password.\n\n\nLogin Link: https://www.vatmate.org\n\nUsername: " . "\nName: " . $user['name'] . "\nMobile: " . $user['mobile'] . "\nEmail: " . $request->input('email') . "\nPassword: " . $user['forget_password'] . "\n\n\n\nYou can change your password by clicking your profile in the window's top right corner. Select My Profile, click the edit button in the top right corner, set a new password for it, and put it again to confirm. Then save it. You cannot change your mobile.\n\nIf you have any questions you may contact the Contact Center at +8801719950891 and mention your email.\n\n\nBest Regards,\n\nvatmate.org";
          Mail::raw($mailData, function ($message) use ($user) {
            $message->to($user['email']);
            $message->subject('Profile has been updated');
          });
        }
      });

      DB::commit();
      return Redirect::route('profile-get')->with('message', 'Profile updated');
    } catch (Throwable $e) {
      DB::rollBack();
      return back()->with("error", $e->getMessage());
    }
  }

  // function call for dropdown. it needs SelectInfinite component. It is send json response
  public function userDropdown(Request $request)
  {
    $auth   = Auth::user();
    $role   = optional($auth->role)->name;
    $search = trim((string) $request->input('search', ''));

    // which roles to exclude for this viewer
    $exclude = match ($role) {
      'superadmin' => ['superadmin', 'editor'],
      'admin'      => ['superadmin', 'admin'],
      default      => [],
    };

    $query = $this->user->with(['role:id,name'])
      ->when($exclude, fn($q) => $q->whereHas('role', fn($r) => $r->whereNotIn('name', $exclude)))
      ->when($search, fn($q) => $q->where(
        fn($q) => $q->where('name',  'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
      ))->orderByDesc('id')
      ->paginate((int) $request->input('size', 10))
      ->withQueryString();

    return response()->json(['user' => $query]);
  }
}
