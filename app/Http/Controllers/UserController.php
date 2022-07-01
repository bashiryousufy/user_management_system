<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UsersResource;

class UserController extends Controller
{
    //function to get all the user's details
    public function getAllUsers(Request $request)
    {
        $users = [];
        $paginate = 10;

        if($request->has('perpage')){
            $paginate = $request->paginate;
        }

        if (!$request->has('name') || !$request->has('email')) {
            $users = User::paginate($paginate);
        }

        //check if email param exist
        if ($request->has('email')) {
            $users = User::where('email', $request->email)->get();
        }

        //check if name param exist
        if ($request->has('name')) {
            $users = User::where('name', 'LIKE', '%'.$request->name.'%')->get();
        }

        return UsersResource::collection($users);
    }
}