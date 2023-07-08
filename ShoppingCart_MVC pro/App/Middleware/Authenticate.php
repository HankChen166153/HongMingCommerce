<?php

namespace App\Middleware;

use Core\Auth;
use Core\JWTCodec;
use Symfony\Component\HttpFoundation\Request;

class Authenticate
{
        
    public function handle(Request $request)
    {
        $codec = new JWTCodec($_ENV['SECRET_KEY']);
        $auth = new Auth($codec);

        if (!$auth->authenticateAccessToken()) {
            // header("Content-Type: application/json");
            // http_response_code(401);
            // $res = [
            //     'error' => "unauthorization"
            // ];
        
            // echo json_encode($res);
            return false;

        }
        $request->request->add(['user_id' => $auth->getUserId()]);
        // echo "(before) ";
        return true;
    }
}