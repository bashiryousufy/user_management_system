<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UsersResource;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getSingleUserByID($id){
        $user = User::find($id);

        if(!empty($user)){
            return response()->json(new UsersResource($user));
        }else{
            return response()->json([
                'message' => 'User not found!'
            ],404);
        }
    }

    //function to get all the user's details
    public function getAllUsers(Request $request)
    {
        $users = [];
        $paginate = 10;

        if ($request->has('perpage')) {
            $paginate = $request->paginate;
        }

        if (!$request->has('name') || !$request->has('email')) {
            $users = User::paginate($paginate);
        }

        //check if email param exist
        if ($request->has('email')) {
            $users = User::where('email', 'LIKE', '%'.$request->email.'%')->get();
        }

        //check if name param exist
        if ($request->has('name')) {
            $users = User::where('name', 'LIKE', '%'.$request->name.'%')->get();
        }

        return UsersResource::collection($users);
    }

    //function to create a new user
    public function createUser(Request $request)
    {
        $userData = $request->validate([
            'name' => 'max:128|required',
            'email' => 'email|required|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password'])
        ]);

        return response()->json([
            'message' => 'User '.$user->name.' successfully created!',
            'data' => new UsersResource($user)
        ], 201);
    }

    public function updateUserByID(Request $request, $id)
    {
        $request->validate([
            'name'=> 'max:128',
            'email' => 'email',
        ]);



        //filter any empty
        $userData = array_filter($request->all());

        //check if password exist then hash it
        if ($request->has('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $rowEffected = User::where('id', $id)->update($userData);

        if ($rowEffected > 0) {
            return response()->json(['message' => 'User has been updated successfully!'], 200);
        } else {
            return response()->json([
                'message' => 'The user with userID '.$id.' does not exist!'
            ], 404);
        }
    }

    public function deleteUserByID($id)
    {
        $rowEffected = User::where('id', $id)->delete();

        if ($rowEffected > 0) {
            return response()->json(['message' => 'User has been deleted successfully!'], 202);
        } else {
            return response()->json([
                'message' => 'The user with userID '.$id.' does not exist!'
            ], 404);
        }
    }
}
