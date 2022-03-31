<?php

namespace App\Http\Controllers;

use App\Exports\VacanciesExport;
use App\Models\Vacancies;
use App\Models\Websites;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class UserController extends Controller
{
    public $ob;
    public $user;
    public function __construct() {
/*        if(Auth::user()->role != 'admin') {
            return redirect()->route('dashboard.list');
        }*/
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $users = User::all();
        return view('users.index',compact('users'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $users
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::where('id',$id)->first();
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Websites  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
                               'name' => 'required',
                               'email' => 'required',
                               'password' => 'required|min:6',
                           ]);
        User::where(['id'=>$request->id])->update([
                                                'name'=>$request->name,
                                                'email'=>$request->email,
                                                'password'=>bcrypt(request()->password),
                                                'role'=>$request->role
                                                ]);
        return redirect()->route('user.edit',['id' => $request->id])
            ->with('success', 'User updated successfully');
    }

    /**
     * Create the specified resource in storage.
     *
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Websites  $user
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
                               'name' => 'required',
                               'email' => 'required|email|unique:users',
                               'password' => 'required|confirmed|min:6',
                           ]);

        $user = User::create([
                                 'name' => $request->input('name'),
                                 'email' =>  $request->input('email'),
                                 'password' => bcrypt($request->input('password')),
                                 'role' => $request->input('role')
                                 ]);

        session()->flash('message', 'Account is created');

        return redirect()->route('user.edit',['id' => $user->id])
            ->with('success', 'User updated successfully');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $users
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        $user = User::where('id',$id)-> first();
        $user->delete();
        return redirect()->route('users.list');
    }

    public function check_user_admin() {
        $role = Auth::user()->role;
        if($role == 'admin') {
            return true;
        }
        return false;
    }

    private function middleare( \Closure $param ) {
    }
}
