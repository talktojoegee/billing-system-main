<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Objection;
use App\Models\ObjectionAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{




    public function showAllUsers(Request $request){
        $type = $request->type;
        $skip = $request->skip;
        $limit = $request->limit;
        $records = User::fetchAllAdminUsers($type, $limit, $skip);
        return response()->json([
            'data'=>UserResource::collection($records),
            'total'=>User::where('type', $type)->count()
        ]);
    }


    public function storeUser(Request $request){

        $validator = Validator::make($request->all(),
            [
                "username"=>"required",
                "name"=>"required",
                "email"=>"required|email",
                "mobileNo"=>"required",
                "idNo"=>"required",
                "sector"=>"required",
            ],
            [
                "username.required"=>"Username is required",
                "name.required"=>"Name is required",
                "email.required"=>"Email is required",
                "mobileNo.required"=>"Mobile number is required",
                "idNo.required"=>"ID number is required",
                "sector.required"=>"Sector is required",
            ]
        );
        if($validator->fails() ){
            return response()->json([
                "errors"=>$validator->messages()
            ],422);
        }
        $uniqueNumber = time();
        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'username'=>$request->username,
            'type'=>1,
            'password'=>bcrypt("password123"),
            'id_no'=>$request->idNo,
            'mobile_no'=>$request->mobileNo,
            'sector'=>$request->sector,
        ]);

        return response()->json(['message' => 'Success! Action successful.'], 201);
    }
}
