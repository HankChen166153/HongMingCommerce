<?php

namespace Core;

class Token
{
    public static function getToken(JWTCodec $codec, $userId, $userName)
    {
        $payload = [
            'sub' => $userId,
            'name' => $userName,
            'exp' =>time() + 5
        ];
        $accessToken = $codec->encode($payload);
        $refreshTokenExpiry = time() + 43200;
        $refreshToken = $codec->encode([
            'sub' => $userId,
            'exp' => $refreshTokenExpiry
        ]);
        $token = [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'refresh_token_expiry' => $refreshTokenExpiry
        ];

        return $token;
    }
}
