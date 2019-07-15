<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth:api");
    }

    public function index()
    {
        return User::orderBy("created_at","desc")->with("posts.category")->get();
    }

    public function paginate()
    {
        return User::orderBy("created_at","desc")->with("posts.category")->paginate(10);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            "name" => "required|string|min:3",
            "email" => "required|email|unique:users",
            "password" => "required|min:6|string|confirmed"
        ]);
        return User::create([
            "name" => $request["name"],
            "email" => $request["email"],
            "password" => Hash::make($request["password"])
        ]);
    }

    public function show(User $user)
    {
        return User::where("id",$user->id)->with("posts.category")->first();
    }

    public function update(Request $request, User $user)
    {
        $this->validate($request,[
            "name" => "required|string|min:3",
            "email" => "required|email|unique:users,email,".$user->id,
            "password" => "sometimes|min:6|string|confirmed"
        ]);
        $user->update([
            "name" => $request["name"],
            "email" => $request["email"],
            "password" => Hash::make($request["password"]),
            "updated_at" => now()
        ]);
        return $user;
    }

    public function destroy(User $user)
    {
        foreach ($user->posts as $post) {
            $post->delete();
        }
        if($user->delete()){
            return "true";
        }
        return "false";
    }
}
