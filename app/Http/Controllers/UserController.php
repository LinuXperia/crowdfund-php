<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use validator, hash, auth
use Symfony\Component\HttpFoundation\Session\Session;
use Validator;
use Hash;
use Auth;
use File;
use Mail;
use Image;

//use base controller
use App\Http\Controllers\Controller;

//user model
use App\User;
use App\Role;
use App\UserSocial;
use App\Payment;

//settings model
use App\Setting;

use Response;
use Socialite;
use Illuminate\Support\Facades\Route;

class UserController extends Controller {
	
	public function appendScriptStyle(){
		//for upload
		array_push($this->scripts['footer'],'upload/jquery.ui.widget.js');
		array_push($this->scripts['footer'],'upload/load-image.all.min.js');
		array_push($this->scripts['footer'],'upload/canvas-to-blob.min.js');
		array_push($this->scripts['footer'],'upload/jquery.iframe-transport.js');
		array_push($this->scripts['footer'],'upload/jquery.fileupload.js');
		array_push($this->scripts['footer'],'upload/jquery.fileupload-process.js');
		array_push($this->scripts['footer'],'upload/jquery.fileupload-image.js');
		array_push($this->styles,'jquery.fileupload.css');
		//for cke
		array_push($this->scripts['header'],'../libraries/ckeditor/ckeditor.js');
	}
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index()
  {
      $this->layout = 'user.list';
      $this->metas['title'] = "User List";
      $this->view = $this->BuildLayout();
      $users = User::all();
      return $this->view->withUsers($users);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return Response
   */
  public function create()
  {
		//if already logged in
		if ($this->user)
        {
			return redirect('/user/edit/profile');
		}
		$this->layout = 'user.register';
		$this->metas['title'] = "User Registration | PoloniaGo";
		$this->view = $this->BuildLayout();
		return $this->view;
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
	public function store(Request $request)
    {
		$v = Validator::make($request->all(), [
			'firstname' => 'required|alpha_num',
			'lastname' => 'required|alpha_num',
			'username' => 'required|unique:users|alpha_num',
			'email' => 'required|unique:users|email',
			'emailConfirmation' => 'required|same:email',
			'password' => 'required',
			'passwordConfirmation' => 'required|same:password',
			'tos' => 'required',
		]);
		
		//recaptcha implementation 
		$recaptcha = new \ReCaptcha\ReCaptcha(Setting::getSetting('recaptchasecret'));
		$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
		    
		if ($v->fails() || $resp->isSuccess()==false)
        {
			if ($resp->isSuccess()==false)
            {
				$v->errors()->add('g-recaptcha', 'I am not a Robot');
			}
			$errors = $v->errors();
			$return['status'] = false;
			$return['errors'] = $errors;
			//return redirect('/user/register')->back()->withErrors($v->errors())->withInput($request->except('password'));
		} else {
			$user = new User;
			$user->firstname = $request->input('firstname');
			$user->lastname = $request->input('lastname');
			$user->username = $request->input('username');
			$user->email = $request->input('email');
			$user->password = Hash::make($request->input('password'));
			$user->register_ip = $_SERVER['REMOTE_ADDR'];
			$user->registered_with = 'local';
			$user->public = 0;
			$user->status = 1;
			$user->role = 2;
			$user->save();
			$this->sendThankYouEmail($user);
			Auth::login($user,true);
			$return['status'] = true;
			$return['url'] = url('/user/edit/profile/'.$user->usr_id);
			
		}
		return $return;
	}

	public function sendThankYouEmail($user)
    {
		Mail::send('email.thankyou', ['user' => $user], function ($m) use ($user) {
			$m->from('noreply@poloniago.com', 'No-Reply');
			$m->to($user->email, $user->fullname)->subject('[PoloniaGo] Thank You for signing up!');
		});
	}
	
	public function loginPost(Request $request)
    {
		//validate input
		$v = Validator::make($request->all(), [
			'email' => 'required|email',
			'password' => 'required'
		]);

		//recaptcha implementation 
		//TODO after 5 attempt show recaptcha
		//$recaptcha = new \ReCaptcha\ReCaptcha(Setting::getSetting('recaptchasecret'));
		//$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

		if ($v->fails())
        {
			$errors = $v->errors();
			$return['status'] = false;
			$return['errors'] = $errors;
		}

		$userdata = array(
			'email' => $request->get('email'),
			'password' => $request->get('password'),
		);

		$remember = false;
		if ($request->has('remember_me') && $request->get('remember_me')==1){
			$remember = true;
		}

		if (Auth::attempt($userdata,$remember))
        {
			$currentPath = Route::getFacadeRoot()->current()->uri();
			$return['status'] = true;
			$return['url'] = url($currentPath);
		} else {
			$return['status'] = false;
			$return['errors'] = ['general'=>['<p style="color:red">Please check your email and/or password or register</p>']];
		}
		return $return;
	}
  
	public function login(Request $request,$provider=null)
    {

        $this->layout = 'user.login';
		$this->metas['title'] = "User Login | PoloniaGo";
		$this->view = $this->BuildLayout();
		$user = $socialUser = '';


		switch ($provider)
        {
			case 'facebook':
			case 'twitter':
			case 'google':
				if ($request->has('code') || $request->has('oauth_token') || $request->has('state')){
					$socialUser = Socialite::with($provider)->user();
				} else {
					$socialite = Socialite::with($provider);
					if ($provider == 'facebook'){
						$socialite->scopes(['user_friends','public_profile','email']);
					}
					return $socialite->redirect();
					
				}
				break;
			
			default:
				# code...
				break;
		}

		if ($socialUser)
        {
			$email = $socialUser->email;
			$name = $socialUser->name;
			$nameArray = explode(' ',$name);
			$firstname = reset($nameArray);
			$lastname = str_replace($firstname,'',trim($name));
			$fb_id = $tw_id = $gp_id = $photo_url = $local_photo_url = '';
			$avatarSavePath = public_path('images/avatar');
			switch ($provider)
            {
				case 'facebook':
					$fb_id = $socialUser->id;
					$gender = $socialUser->user['gender'];
					$photo_url = $socialUser->avatar_original;
				break;
				case 'twitter':
					$tw_id = $socialUser->id;
					$photo_url = $socialUser->avatar_original;
				break;
				case 'google':
					$gp_id = $socialUser->id;
					$gender = $socialUser->user['gender'];
					$photo_url = $socialUser->avatar;
				break;
				default:
				break;
			}
			$userSocial = UserSocial::where('social',$provider)->where('socialname',$socialUser->id);
			if (!empty($photo_url))
            {
				$photo_urlArray = parse_url($photo_url);
				unset($photo_urlArray['query']);
				$photo_url = $photo_urlArray['scheme'].'://'.$photo_urlArray['host'].$photo_urlArray['path'];
			}
			
			if ($userSocial->exists())
            {
				$userId = $userSocial->get()->first()->user_id;
				$user = User::find($userId);
			} else {
				//if not existing then just register
				
				$user = User::create([
					'email'=>$email,
					'firstname'=>$firstname,
					'lastname'=>$lastname,
					'registered_with'=>$provider,
					'register_ip'=>$request->getClientIp(),
					'public'=>0,
					'status'=>1,
					'role'=>2,
				]);
				
				if (!empty($photo_url)){
					$thumbnail = Image::make($photo_url);
					$thumbnail->resize(80, 80);
					$thumbnail->save($avatarSavePath.'/thumbnail/'.$user->id.'.jpg');
					
					$medium = Image::make($photo_url);
					$medium->resize(160, 160);
					$medium->save($avatarSavePath.'/medium/'.$user->id.'.jpg');
					
					$large = Image::make($photo_url);
					$large->resize(360, 360);
					$large->save($avatarSavePath.'/large/'.$user->id.'.jpg');
					
					//$ext = pathinfo($photo_url,PATHINFO_EXTENSION);
					$ext = 'jpg';
					//file_put_contents($avatarSavePath.'/'.$user->id.'.'.$ext, fopen($photo_url, 'r'));
					$local_photo_url=$user->id.'.'.$ext;
					$user->avatar = $local_photo_url;
					$user->save();
				}
				$usersocial = new UserSocial;
				$usersocial->user_id = $user->id;
				$usersocial->social = $provider;
				$usersocial->socialname = $socialUser->id;
				$user->usersocial()->save($usersocial);
				
				$this->sendThankYouEmail($user);
			}
			Auth::login($user,true);
		}

		//if already logged in
		if (Auth::check() || Auth::viaRemember())
        {
            return redirect('/user/edit/profile');
		}
		
		return $this->view;
	}
	
	//logout
	public function logout()
    {
		Auth::logout(); // log the user out of our application
		return redirect('/user/login'); // redirect the user to the login screen
	}

	public function profile($id=null)
    {

		$this->layout = 'user.profile';
		if($id!=null)
        {
			$user = User::getUserbyid($id);
			if ($user)
            {
                if($user->role == '1')
                {
                    // Admin user
                    // Redirect to Projects Lists
                    return redirect('/admin/projects');
                }
				$this->layout = 'user.profile';
				$this->metas['title'] = $user->fullname." 's record";
				$this->view = $this->BuildLayout();

				return $this->view->withUser($user);

			} else {
				$this->metas['title'] = "Could not be found!";
			}
		} else if($this->user)
        {
            if($this->user->role == '1')
            {
                // Admin user
                // Redirect to Projects Lists
                return redirect('/admin/projects');
            }
			$this->metas['title'] = "My Account";
		} else {
			return redirect('/user/login');
		}
		$this->view = $this->BuildLayout();
		return $this->view;
	}

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return Response
   */
	public function edit()
    {
		$this->appendScriptStyle();
		$this->layout = 'user.edit';
		$this->metas['title'] = "Edit Account";
		$this->view = $this->BuildLayout();

		return $this->view
			->withUser($this->user)
		;
	}

  /**
   * Update the specified resource in storage.
   *
   * @param  int  $id
   * @return Response
   */
	public function update(Request $request)
    {
		$rules = [
			'lastname' => 'required',
			'firstname' => 'required',
			'username' => 'required',
			'email' => 'required',
			'public' => 'required'
		];
		$v = Validator::make($request->all(), $rules);
		if ($v->fails()){
			$return['errors'] = $v->errors();
		} else {
			$this->user->lastname = $request->get('lastname');
			$this->user->firstname = $request->get('firstname');
			$this->user->username = $request->get('username');
			$this->user->email = $request->get('email');
			$this->user->public = $request->get('public');
			$this->user->avatar = $request->get('avatar');
			$this->user->bio = $request->get('bio');
			$this->user->save();
			
			$return['errors'] = ['Your Information is Updated!'];
		}
		return redirect()->back()->withErrors($return['errors']);
	}

	public function editPassword(Request $request){
        $this->layout = 'user.editpassword';
		$this->metas['title'] = "Change Password";
		$this->view = $this->BuildLayout();
		$return = '';
		if (Auth::check() || Auth::viaRemember()){
			$user = Auth::user();
			return $this->view
				->withUser($user)
			;
		}
		return redirect('/user/login');
    }

	public function updatePassword(Request $request){
		$v = Validator::make($request->all(), [
			'password_new' => 'required|min:5|max:16|confirmed',
			'password_new_confirmation' => ''
		]);
		if ($v->fails()){
			$errors = $v->errors();
			return redirect()->back()->withErrors($errors)->withInput();
		}
		$user = Auth::user();
		if (!empty($user->password)){
			$v = Validator::make($request->all(), [
				'password_old' => 'required|min:5|max:16'
			]);
			if ($v->fails()){
				$errors = $v->errors();
				return redirect()->back()->withErrors($errors)->withInput();
			}
			
			if(Hash::check($request->get('password_old'), $user->password)){
				$user->password = Hash::make($request->get('password_new'));
				$user->save();
			} else {
				return redirect()->back()->withErrors('Your current password is wrong');
				//Auth::logout();
			}
		} else {
			$user->password = Hash::make($request->get('password_new'));
			$user->save();
		}
		
		return redirect()->back()->withStatus('Password Changed');
    }

	public function searchUserModal(){
		$searchUserModal = view('modules.modal', ['id'=>'searchusermodal','title' => 'User Search','modalbody'=>'modules.user.search'])
			->render()
		;
		$return['status'] = true;
		$return['view'] = $searchUserModal;
		return $return;
	}

	public function contactUserModal(Request $request){
		$user_id = $request->get('user_id');
		$user = User::find($user_id);
		$contactUserModal = view('modules.modal', ['id'=>'contactusermodal'.$user_id,'title' => $user->fullname.' Send E-Mail','modalbody'=>'modules.user.contact'])
			->withUser($user)
			->render()
		;
		$return['status'] = true;
		$return['view'] = $contactUserModal;
		return $return;
	}

	public function contactUser(Request $request){
		$v = Validator::make($request->all(), [
			'email' => 'required|email',
			'fullname' => 'required',
			'message' => 'required|min:20|max:1000',
		]);
		
		if ($v->fails()){
			$errors = $v->errors();
			$return['status'] = false;
			$return['errors'] = $errors;
		} else {
			$from = $request->get('email');
			$fullname = $request->get('fullname');
			$mailmessage = $request->get('message');
			$user_id = $request->get('id');
			$user = User::find($user_id);
			if($user){
				Mail::send('email.contactuser', ['user' => $user,'mailmessage'=>$mailmessage,'fullname'=>$fullname], function ($m) use ($user,$from,$fullname) {
					$m->from($from, $fullname);
					$m->to($user->email, $user->fullname)->subject('Contact You from PoloniaGo!');
				});
				$return['status'] = true;
				$return['view'] = 'Your message has been sent!';
			} else {
				$return['status'] = false;
				$return['errors'] = ['Your message has not been sent!'];
			}
		}
		
		return $return;
	}

	public function searchUserList(Request $request){
		$return['status'] = false;
		$v = Validator::make($request->all(), [
			'searchuserfield' => 'required'
		]);
		if ($v->fails()){
			$errors = $v->errors();
			$return['status'] = false;
			$return['errors'] = $errors;
		} else {
			//check if number
			$f = $request->get('searchuserfield');
			$userlist = [];
			$v = Validator::make($request->all(), ['searchuserfield' => 'integer']);
			if ($v->fails()){
				// check if email
				$v = Validator::make($request->all(), ['searchuserfield' => 'email']);
				if ($v->fails()){
					$return['status'] = true;
					$return['query'] = $f;
					$userlist = User::where('username','like','%'.$f.'%')
						->orWhere('firstname','like','%'.$f.'%')
						->orWhere('lastname','like','%'.$f.'%')
						->get();
				} else {
					$return['status'] = true;
					$userlist = User::where('email',$f)->get();
				}
			} else {
				$return['status'] = true;
				$userlist = User::where('id',$f)->get();
			}
			if($userlist->isEmpty()){
				$return['status'] = false;
				$return['errors'] = ['searchuserfield'=>['Search found the appropriate users']];
				$return['userlist'] = $userlist;
			} else {
				$return['view'] = view('modules.user.list',['users'=>$userlist,'add'=>true])->render();
				$return['userlist'] = $userlist;
			}
		}
		return $return;
	}

	public function support(){
		$this->layout = 'user.support';
		$this->metas['title'] = "I supported project";
		$this->view = $this->BuildLayout();
		return $this->view
			->withUser($this->user)
			->withPayments($this->user->payments()->paginate(5))
		;
	}

    public function delete($id=null)
    {
        //TODO: Check for Projects associated with USER

        User::find($id)->delete();
        return redirect()->back()->withErrors(['error'=>['User Deleted']]);
    }

    public function lock($id=null)
    {
        $user = User::find($id);
        $user->status = '0';
        $user->save();
        return redirect()->back()->withErrors(['error'=>['User De-Activated']]);
    }

    public function unlock($id=null)
    {
        $user = User::find($id);
        $user->status = '1';
        $user->save();
        return redirect()->back()->withErrors(['error'=>['User Activated']]);
    }
}

?>