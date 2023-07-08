<?php

namespace Core;

use App\Models\RefreshToken;

class RefreshTokenGateway
{
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function create(string $token, int $expiry)
    {
        $hash = hash_hmac('sha256', $token, $this->key);
        $refreshToken = new RefreshToken();
        $refreshToken = $refreshToken->create($hash, $expiry);

        return $refreshToken;
    }

    public function delete(string $token): int
    {
        $hash = hash_hmac('sha256', $token, $this->key);
        $refreshToken = new RefreshToken();
        $delCount = $refreshToken->delete($hash);

        return $delCount;
    }

    public function getByToken(string $token)
    {
        $hash = hash_hmac("sha256", $token, $this->key);
        $refreshToken = new RefreshToken();
        $refreshToken = $refreshToken->getByToken($hash);
        
        return $refreshToken;
    }

    public function deleteExpired()
    {
        $refreshToken = new RefreshToken();
        $delCount = $refreshToken->deleteExpired();
        
        return $delCount;
    }
}
