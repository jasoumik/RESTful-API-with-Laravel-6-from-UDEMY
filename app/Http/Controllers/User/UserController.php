<?php

namespace App\Http\Controllers\User;

use App\User;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Transformers\UserTransformer;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        //parent::__construct();
        $this->middleware('client.credentials')->
        only(['store','resend']);
        $this->middleware('auth:api')->
        except(['store','resend','verify']);
        $this->middleware('auth:api')->
        except(['store','resend','verify']); 
        $this->middleware('scope:manage-account')->only(['show','update']);
        $this->middleware('can:view,user')->only(['show']);
        $this->middleware('can:update,user')->only(['update']);
        $this->middleware('can:delete,user')->only(['destroy']);
    }
    public function index()
    {
        $this->allowedAdminAction();
        $users=User::all();
        return $this->showAll($users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $rules=[
           'name'=>'required',
           'email'=>'required|email|unique:users',
           'password' => 'required|min:6|confirmed',
       ];
       $this->validate($request,$rules);
       $data = $request->all();
       $data['password']=bcrypt($request->password);
       $data['verified']= User::UNVERIFIED_USER;
       $data['verification_token']= User::generateVerificationCode();
       $data['admin']= User::REGULAR_USER;
        
       $user=User::create($data);
       //return response()->json(['data'=>$user],201);
       return $this->showOne($user,201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
       // $user = User::findOrFail($id);
        //return response()->json(['data'=>$user],200);
        return $this->showOne($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        
       // $user=User::findOrFail($id);

        $rules=[
            'email'=>'email|unique:users,email,' . $user->id,
            'password'=>'min:6|confirmed',
            'admin' => 'in:' . User::ADMIN_USER . ',' . User::REGULAR_USER,

        ];
        if($request->has('name')){
            $user->name= $request->name;
        }
        if($request->has('email') && $user->email != $request->email){
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email= $request->email;
        }
        if($request->has('password')){
            $user->password= bcrypt($request->password);
        }
        if($request->has('admin')){
            $this->allowedAdminAction();
            if(!$user->isVerified()){
                //return response()->json(['error'=>'You are not verified user',
                //'code'=>409],409);
                return $this->errorResponse('You are not verified user',409);
            }
            $user->admin= $request->admin;
        }
        if(!$user->isDirty()){
           // return response()->json(['error'=>'Use different value to update',
            //'code'=>422],422);
            return $this->errorResponse('Use different value to update',422);
        }
        $user->save();

        //return response()->json(['data'=>$user],200);
        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
       // $user=User::findOrFail($id);
        $user->delete();
        //return response()->json(['data'=>$user],200);
        return $this->showOne($user);
    }
    public function me(Request $request){

        $user=$request->user();
        return $this->showOne($user);
    }
    public function verify($token){
      $user = User::where('verification_token',$token)->firstOrFail();

      $user->verified = User::VERIFIED_USER;
      $user->verification_token = null;

      $user->save();
      return $this->showMessage('The account has been verified Successfully');
    }

    public function resend(User $user){
        if($user->isVerified()){
            return $this->errorResponse('The account already has been verified',409);
        }
        retry(5,function() use ($user){
            Mail::to($user)->send(new UserCreated($user));
        },100);
        return $this->showMessage('The verification email has been sent again');
    }
}
