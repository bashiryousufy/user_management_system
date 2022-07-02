<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UsersResource;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //get a single user by their id
    public function getSingleUserByID($id)
    {
        $user = User::find($id);

        if (!empty($user)) {
            return response()->json(new UsersResource($user));
        } else {
            return response()->json([
                'message' => 'User not found!'
            ], 404);
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

    //function to update a user by their ID
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

    //function to delete a user by their ID
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

    public function handleBulkAction(Request $request, $action)
    {
        $request->validate([
            'csv_import' => 'required|mimes:csv,txt'
        ]);

        $arrayOfUsers = $this->csvToArray($request->csv_import);


        for ($i=0; $i < count($arrayOfUsers); $i++) {
            if($action == 'create'){
                User::firstOrCreate($arrayOfUsers[$i]);
            }

            if($action == 'edit'){
                //edit
                User::where('email', $arrayOfUsers[$i]['email'])->update($arrayOfUsers[$i]);
            }

            if($action == 'delete'){
                //delete
                User::where('email', $arrayOfUsers[$i]['email'])->delete();
            }
        }

        return response()->json(
            ['message' => 'Users have been '.$action.'ed successfully'],201
        );
    }

    //function which converts csv file into php array
    public function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }
}
