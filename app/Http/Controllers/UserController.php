<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index() {
        return User::get();
    }

    public function register() {

    }

    public function login() {
        
    }

    public function show(Request $request)  {
        return $request->user();
    }

   

    public function edit(Request $request) {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
        ], [
            'fullname.required' => "لطفا نام و نام خانوادگی را وارد کنید",
            'fullname.string' => "نام و نام خانوادگی باید به صورت حروفی باشد",
            'fullname.max' => "نام و نام خانوادگی حداقل ۲۵۵ کاراکتر باشد",
        ]);
        if ($validator->fails()) {
            return $validator->messages();
        }
        return User::where('id', $request->user()->id)->update([
            'fullname' => $request->fullname,
        ]);
    }
}
