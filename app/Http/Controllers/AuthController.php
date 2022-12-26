<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Time;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use PhpParser\Node\Stmt\TryCatch;

class AuthController extends Controller
{

    private static function sendMessage($receptor, $template, $param1)
    {
        $curl = curl_init('https://api.ghasedak.me/v2/verification/send/simple');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, "receptor=$receptor&template=$template&type=1&param1=$param1");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "apikey: " . env('GHASEDAK_TOKEN'),
            "cache-control: no-cache",
            "content-type: application/x-www-form-urlencoded",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
    }

    private static function makeCode($n)
    {
        $generator = "1357902468";
        $result = "";
        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, rand() % strlen($generator), 1);
        }
        return $result;
    }

    public function login_or_register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'phone'      => 'required|string|numeric|digits:11',
        ], [
            'fullname.required' => "لطفا نام و نام خانوادگی را وارد کنید",
            'fullname.string' => "نام و نام خانوادگی باید به صورت حروفی باشد",
            'fullname.max' => "نام و نام خانوادگی حداقل ۲۵۵ کاراکتر باشد",
            'phone.required' => "لطفا شماره همراه را وارد کنید",
            'phone.string' => "شماره همراه باید به صورت عددی باشد",
            'phone.digits' => "شماره همراه  ۱۱ رقم باشد",
            'phone.unique' => " شماره همراه تکراری است",
        ]);
        if ($validator->fails()) {
            return $validator->messages();
        }

        try {
                $phonenumber = $request->phone;
                $fullname = $request->fullname;
                $code = AuthController::makeCode(env('LENGTH_OF_CODE'));
                AuthController::sendMessage($phonenumber, env('TEMPLATE1'), $code);
                Redis::set("$code", "$fullname", "EX", 60);
                Redis::set("$phonenumber", "$code", "EX", 60);
                return true;
        } catch (\Throwable $th) {
            return false;
        }

    }

    public function checkCode(Request $request)
    {
        $phonenumber = $request->phone;
        $code = Redis::get("$phonenumber");
        $fullname = Redis::get("$code");
        if(empty($code))
            return response()->json(['RetVal' => false, 'status' => 'code is expired']);
        if ($code == $request->code)
            {
                $req = new Request($request->all());
                $req->replace(['fullname' => $fullname, 'phone' => $phonenumber]);
                $result = AuthController::login($req);
                return response()->json($result);
            }
        else return response()->json(['RetVal' => false]);
    }
    
    public static function login(Request $request) {
        $user = User::firstOrCreate([
            'fullname' => $request->fullname,
            'phone' => $request->phone,
        ]);
        $token = $user->createToken($user->phone);
        return ['token' => $token->plainTextToken, 'Retval' => true, 'fullname' => $request->fullname];
    }
}
