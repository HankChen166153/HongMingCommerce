<?php

namespace App\Models;

use PDO;

class RefreshToken extends \Core\Model
{
    public function create(string $hash, int $expiry)
    {
        $db = static::getDB();

        $sql = "INSERT INTO refresh_token (token_hash, expires_at)
                VALUES (:token_hash, :expires_at)";

        $stmt = $db->prepare($sql);

        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        $stmt->bindValue(":expires_at", $expiry, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(string $hash)
    {
        $db = static::getDB();

        $sql = "DELETE FROM refresh_token
                WHERE token_hash = :token_hash";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function getByToken(string $hash)
    {
        $db = static::getDB();

        $sql = "SELECT *
                FROM refresh_token
                WHERE token_hash = :token_hash";
                
        $stmt = $db->prepare($sql);
        $stmt->bindValue(":token_hash", $hash, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteExpired()
    {
        $db = static::getDB();
        
        $sql = "DELETE FROM refresh_token
                WHERE expires_at < UNIX_TIMESTAMP()";
            
        $stmt = $db->query($sql);
        
        return $stmt->rowCount();
    }
}
