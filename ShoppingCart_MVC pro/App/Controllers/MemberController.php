<?php

namespace App\Controllers;

use App\Models\User;
use App\Util;
use Core\JWTCodec;
use Core\RefreshTokenGateway;
use Core\Token;

class MemberController extends \Core\Controller
{
    /**
     * login
     *
     * @return void
     */
    public function login()
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null; //專門給response用


        $input = (array) json_decode(file_get_contents("php://input"), true);
        if (!isset($input['ac'])) {
            header("Content-Type: application/json");
            $message = "param require: ac";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        if (!isset($input['pw'])) {
            header("Content-Type: application/json");
            $message = "param require: pw";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        $ac = $input['ac'];
        $pw = $input['pw'];

        $user = new User();
        $user = $user->verifyUser($ac, $pw);
        // echo "user: " . json_encode($user) . "\n";
        // $result = User::verifyUser($ac, $pw);

        if (empty($user)) {
            header("Content-Type: application/json");
            http_response_code(401);

            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        // echo "user: " . json_encode($user);

        // JWT token
        $codec = new JWTCodec($_ENV['SECRET_KEY']);
        $token = Token::getToken($codec, $user['user_id'], $user['user_name']);

        $refreshTokenGateway = new RefreshTokenGateway($_ENV['SECRET_KEY']);
        $refreshTokenGateway->create($token['refresh_token'], $token['refresh_token_expiry']);

        $data = $user;
        $data['access_token'] = $token['access_token'];
        $data['refresh_token'] = $token['refresh_token'];
        $success = true;
        header("Content-Type: application/json");
        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }

    public function register()
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;

        $input = (array) json_decode(file_get_contents("php://input"), true);
        // 檢查必要參數
        //isset
        if (!isset($input['account'])) {
            header("Content-Type: application/json");
            $message = "param require: ac";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        if (!isset($input['pw'])) {
            header("Content-Type: application/json");
            $message = "param require: pw";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        $account = $input['account'];
        $pw = $input['pw'];
        // 檢查資料型態
        //is_string
        if (!is_string($account)) {
            header("Content-Type: application/json");
            $message = "param type error:ac";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }


        if(!is_string($pw)) {
            header("Content-Type: application/json");
            $message = "param type error:pw";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        // 檢查字串長度
        //strlen():int
        $acLen = strlen($account);
        if($acLen < 4 || $acLen > 50) {
            header("Content-Type: application/json");
            $message = "param error:ac too short or too long";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        };

        // 檢查密碼長度，小於20字元，大於4字元
        //strlen()
        $pwLen = strlen($pw);
        if($pwLen < 4 || $pwLen > 20) {
            header("Content-Type: application/json");
            $message = "param error:pw too short or too long";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        };

        // 檢查帳號是否註冊過
        $user = new User();
        $user = $user->getUserByAccount($input["account"]);
        

        if(!empty($user)) {
            header("Content-Type: application/json");
            $message = "error:account already existed";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        // $data = $user;
        // $success = true;
        // header("Content-Type: application/json");
        // echo Util::response_data($success, $errorCode, $message, $data);
        // exit;
        
        // 創建用戶
        $user = new User();
        $user = $user->createUser($input);
        if(!isset($input['account'])) {
            header("Content-Type: application/json");
            $message = "error:create account failed";
            $errorCode = 999;
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        $data = $user;
        $success = true;
        header("Content-Type: application/json");
        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }
    
    /**
     * 
     */
    public function refreshToken()
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;

        $input = (array) json_decode(file_get_contents("php://input"), true);

        if (!array_key_exists('token', $input)) {
            header("Content-Type: application/json");
            http_response_code(400);
            $message = "missing token";
            $res = [
                'success' => $success,
                'message' => $message,
                'data' => $data
            ];

            echo json_encode($res);
            exit;
        }
        $codec = new JWTCodec($_ENV['SECRET_KEY']);

        try {
            $payload = $codec->decode($input['token']);
        } catch (\Exception $e) {
            header("Content-Type: application/json");
            http_response_code(400);
            $message = "invalid toekn";
            
            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }

        $userId = $payload['sub'];
        $refreshTokenGateway = new RefreshTokenGateway($_ENV['SECRET_KEY']);
        $refreshToken = $refreshTokenGateway->getByToken($input['token']);
        if ($refreshToken === false) {
            header("Content-Type: application/json");
            http_response_code(400);
            $message = "invalid toekn (not on whitelist)";

            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }


        $user = new User();
        $user = $user->getUserById($userId);
        if (empty($user)) {
            header("Content-Type: application/json");
            http_response_code(401);
            $message = "invalid authentication";

            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        // echo "userData: " . json_encode($userData);

        // JWT token
        $codec = new JWTCodec($_ENV['SECRET_KEY']);
        $token = Token::getToken($codec, $user['user_id'], $user['user_name']);

        $refreshTokenGateway->delete($input['token']);
        $refreshTokenGateway->create($token['refresh_token'], $token['refresh_token_expiry']);

        $data = $token;
        $success = true;
        header("Content-Type: application/json");
        echo Util::response_data($success, $errorCode, $message, $data);
        exit;
    }

    /**
     * 
     */
    public function logout()
    {
        $success = false;
        $errorCode = 0;
        $message = "";
        $data = null;

        $input = (array) json_decode(file_get_contents("php://input"), true);

        if (!array_key_exists('token', $input)) {
            header("Content-Type: application/json");
            http_response_code(400);
            $message = "missing toekn";

            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }
        $codec = new JWTCodec($_ENV['SECRET_KEY']);

        try {
            $payload = $codec->decode($input['token']);
        } catch (\Exception $e) {
            header("Content-Type: application/json");
            http_response_code(400);
            $message = "invalid toekn";

            echo Util::response_data($success, $errorCode, $message, $data);
            exit;
        }


        $refreshTokenGateway = new RefreshTokenGateway($_ENV['SECRET_KEY']);

        // JWT token
        $codec = new JWTCodec($_ENV['SECRET_KEY']);

        $refreshTokenGateway->delete($input['token']);

        // $data = $token;
        $success = true;
        header("Content-Type: application/json");

        echo Util::response_data($success, $errorCode, $message, $data);
        exit;

        
    }

}
