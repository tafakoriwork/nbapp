<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarberController extends Controller
{
    public function store(Request $request) {
        if($request->user()->role === 'admin')
        {
            $validator = Validator::make($request->all(), [
                'fullname' => 'required|string|max:255',
                'phone'      => 'required|string|numeric|digits:11|unique:users',
            ], [
                'fullname.required' => "لطفا نام و نام خانوادگی را وارد کنید",
                'fullname.string' => "نام و نام خانوادگی باید به صورت حروفی باشد",
                'fullname.max' => "نام و نام خانوادگی حداقل ۲۵۵ کاراکتر باشد",
                'phone.required' => "لطفا شماره همراه را وارد کنید",
                'phone.unique' => " شماره همراه تکراری است",
                'phone.string' => "شماره همراه باید به صورت عددی باشد",
                'phone.digits' => "شماره همراه  ۱۱ رقم باشد",
            ]);
            if ($validator->fails()) {
                return $validator->messages();
            }
            $user = User::firstOrCreate([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'role' => 'barber',
            ]);
            return $user;
        }
        else return false; 
    }

}
