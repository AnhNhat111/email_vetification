<?php

namespace App\Http\Controllers;

use App\Models\ActiveUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class ActiveUserController extends Controller
{
    public function ActiveUser(Request $request)
    {
        $code_active = $request->input('code_active');
        $email = $request->input('email');

        if ($code_active) {
            $check = ActiveUser::where([
                'email' => $email,
                'code' => $code_active,
            ])->first();

            $dateNow = Carbon::now();
        
            if ($check) {
                $dateCheck = Carbon::parse($check->created_at)->addHour();

                if($dateCheck >= $dateNow){
                    $check->delete();
                    return response()->json(['message' => 'success']);
                }else {
                    return response()->json(['message' => 'error']);
                }
            } else {
                return response()->json(['message' => 'error']);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => 'error', 'errors' => $validator->errors()]);
            } else {
                $delete_active = ActiveUser::where('email', $email)->delete();

                $TNew_Active = new ActiveUser();

                $code = rand(100000, 999999);

                $TNew_Active->email = $email;
                
                $TNew_Active->code = $code;

                $TNew_Active->save();

               
                
                Mail::send('auth.email.mailfb',[
                    'email' => $TNew_Active->email,
                    'code' => $TNew_Active->code,
                ], function ($message) use ($TNew_Active) {
                    $message->to($TNew_Active->email, 'User register')->subject('Code active user.');
                });

                return response()->json(['message' => 'success']);
            }
        }
    }
}
