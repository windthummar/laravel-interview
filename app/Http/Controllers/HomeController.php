<?php

namespace App\Http\Controllers;

use App\Mail\sendInvite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function sendInvite(Request $request){
        $response = array('response' => '', 'success'=>false);
        $validator = Validator::make($request->all(), ['email'=>'required|email']);

        if ($validator->fails()) {
            $response['response'] = $validator->messages();
        } else {
            //process the request
            Mail::to([$request->get('email')])->send(new sendInvite());
            $response['response'] = "Mail has been sent successfully.";
            $response['success'] = true;
        }

        return $response;
    }
}
