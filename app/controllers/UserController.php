<?php
require_once __DIR__ . "./../services/UserService.php";

class UserController
{
    private $userService;

    public function __construct($conn)
    {
        $this->userService = new UserService($conn);
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        header('Content-Type: application/json');

        try {
            $required = ['username', 'name', 'phone', 'email', 'password', 'city', 'role'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Missing required field: $field", 400);
                }
            }

            $response = $this->userService->register(
                $data['username'] ?? '',
                $data['name'] ?? '',
                $data['phone'] ?? '',
                $data['email'] ?? '',
                $data['password'] ?? '',
                $data['city'] ?? '',
                $data['role'] ?? 'user'
            );

            http_response_code(201);
            echo json_encode($response);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400; // default ke 400 jika code = 0
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        header('Content-Type: application/json');

        try {
            if (!isset($data['email'], $data['password'])) {
                throw new Exception('Missing required fields: email, password', 400);
            }

            $response = $this->userService->login($data['email'], $data['password']);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        header('Content-Type: application/json');

        try {
            $response = $this->userService->logout();
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function verifyRegistration()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        header('Content-Type: application/json');

        try {
            if (!isset($data['otp'])) {
                throw new Exception('OTP is required', 400);
            }

            $response = $this->userService->verifyOtp($data['otp']);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 400;
            http_response_code($code);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
