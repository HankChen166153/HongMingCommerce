<?php

namespace Core;

use Exception;
use InvalidSignatureException;

class Auth
{    
    private int $user_id;
    private JWTCodec $codec;

    public function __construct(JWTCodec $codec)
    {
        $this->codec = $codec;
    }
        

    public function authenticateAccessToken(): bool
    {
        if ( ! preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches)) {
            header("Content-Type: application/json");
            http_response_code(400);
            echo json_encode(['error' => "incomplete authorization header"]);
            return false;
        }
        
        try {
            $data = $this->codec->decode($matches[1]);

        
        } catch (InvalidSignatureException $e) {
            header("Content-Type: application/json");
            http_response_code(401);
            echo json_encode(['error' => "Invalid signature"]);

            return false;
        } catch (TokenExpiredException $e) {
            header("Content-Type: application/json");
            http_response_code(401);
            echo json_encode(['error' => "token has expired"]);

            return false;
        } catch (Exception $e) {
            header("Content-Type: application/json");
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);

            return false;
        }
        
        $this->user_id = $data['sub'];
        
        return true;
    }

    public function getUserId()
    {
        return $this->user_id;
    }
}












