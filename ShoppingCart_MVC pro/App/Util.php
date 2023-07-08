<?php

namespace App;

use Tuupola\base58;

class Util
{
    /**
     * Create unique ID.
     *
     * @param int  $length
     * @return string
     */
    public static function generateID_V3($length)
    {
        $bitcoin = new Base58(['characters' => Base58::BITCOIN]);

        return $bitcoin->encode(random_bytes($length));
    }
    
    public static function response_data($success, $errorCode, $message, $data)
    {
        $flag = $success ? "success" : "fail";

        return json_encode(
            [
                'status' => $flag,
                'comment' => $errorCode,
                'message' => $message,
                'data' => $data
            ]
        );
    }
}