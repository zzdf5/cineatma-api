<?php

class HandleException
{
    public static function handle(\Exception $e, $defaultCode = 400)
    {
        $code = $e->getCode();
        if ($code < 100 || $code > 599) $code = $defaultCode;
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
