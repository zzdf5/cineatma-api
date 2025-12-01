<?php
require_once __DIR__ . "./../models/UserModel.php";
require_once __DIR__ . "./../helpers/SendMail.php";

define('COOKIE_SECRET_KEY', 'buah_jambu');

class UserService
{
    private $userModel;

    public function __construct($conn)
    {
        $this->userModel = new UserModel($conn);
    }

    public function login($email, $password)
    {
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters", 400);
        }

        $user = $this->userModel->getUserByEmail($email);
        if (!$user) {
            throw new Exception("User not found", 404);
        }

        if (!password_verify($password, $user['password'])) {
            throw new Exception("Invalid password", 401);
        }

        $role = $user['role'];
        $roleData = json_encode(['role' => $role]);
        $encoded = base64_encode($roleData);
        $signature = hash_hmac('sha256', $encoded, COOKIE_SECRET_KEY);
        $cookieValue = $encoded . '.' . $signature;

        setcookie(
            'user_role',
            $cookieValue,
            time() + 24 * 60 * 60,
            "/",
            "",
            false,
            true
        );

        return [
            'success' => true,
            'message' => 'Login successful'
        ];
    }

    public function logout()
    {
        if (!isset($_COOKIE['user_role'])) {
            throw new Exception("User is not logged in", 401);
        }

        setcookie('user_role', '', time() - 3600, "/", "", false, true);

        return [
            'success' => true,
            'message' => 'Logout successful'
        ];
    }

    public function register($username, $name, $phone, $email, $password, $city, $role = 'user')
    {
        if (preg_match('/\s/', $username)) {
            throw new Exception("Username cannot contain spaces", 400);
        }
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters", 400);
        }
        if ($this->userModel->getUserByEmail($email)) {
            throw new Exception("Email already registered", 409);
        }
        if ($this->userModel->getUserByUsername($username)) {
            throw new Exception("Username already registered", 409);
        }

        $userData = [
            'username' => $username,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'password' => $password,
            'city' => $city,
            'role' => $role
        ];

        $data = json_encode($userData);
        $encoded = base64_encode($data);
        $signature = hash_hmac('sha256', $encoded, COOKIE_SECRET_KEY);

        setcookie('pending_user', $encoded . '.' . $signature, time() + 600, "/", "", false, true);

        $otpCode = rand(100000, 999999);
        $mail = new SendMail();
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; text-align: center;'>
                <h2>OTP Verification</h2>
                <p>Hello $name,</p>
                <p>Your OTP code for Cineatma account registration is:</p>
                <h1 style='color: #2F80ED;'>$otpCode</h1>
                <p>This code is valid for 2 minutes.</p>
            </div>
        ";
        $mail->send($email, $name, $otpCode . ' - OTP Code Cineatma Account', $htmlContent);

        session_start();
        $_SESSION['otp_code'] = $otpCode;
        $_SESSION['otp_expires'] = time() + 120;

        return [
            'success' => true,
            'message' => 'User data saved temporarily in cookie'
        ];
    }

    public function verifyOtp($otp)
    {
        if (!isset($_COOKIE['pending_user'])) {
            throw new Exception("No pending registration found", 400);
        }

        $cookie = $_COOKIE['pending_user'];
        list($encoded, $signature) = explode('.', $cookie);
        $expected = hash_hmac('sha256', $encoded, COOKIE_SECRET_KEY);

        if (!hash_equals($expected, $signature)) {
            throw new Exception("Cookie tampered!", 400);
        }

        $userData = json_decode(base64_decode($encoded), true);

        session_start();
        if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expires'])) {
            throw new Exception("OTP not found. Please request a new one.", 400);
        }

        if (time() > $_SESSION['otp_expires']) {
            unset($_SESSION['otp_code'], $_SESSION['otp_expires']);
            throw new Exception("OTP has expired. Please request a new one.", 400);
        }

        if ($otp != $_SESSION['otp_code']) {
            throw new Exception("Invalid OTP.", 401);
        }

        $created = $this->userModel->createUser(
            $userData['username'],
            $userData['name'],
            $userData['phone'],
            $userData['email'],
            $userData['password'],
            $userData['city'],
            $userData['role']
        );

        if (!$created) {
            throw new Exception("Failed to register user. Please try again later.", 500);
        }

        setcookie('pending_user', '', time() - 3600, "/", "", false, true);
        unset($_SESSION['otp_code'], $_SESSION['otp_expires']);

        return [
            'success' => true,
            'message' => 'User registered successfully'
        ];
    }

    public function updateProfile($userId, $username, $name, $phone, $city)
    {
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        if (preg_match('/\s/', $username)) {
            throw new Exception("Username cannot contain spaces", 400);
        }

        if ($this->userModel->getUserByUsername($username) && $username != $user['username']) {
            throw new Exception("Username already registered", 409);
        }

        $updatedUser = $this->userModel->updateProfile($userId, $username, $name, $phone, $city);
        if (!$updatedUser) {
            throw new Exception("Failed to update profile", 500);
        }

        return [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    }

    public function updateAvatar($userId, $file)
    {
        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            throw new Exception("User not found", 404);
        }

        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Avatar file is required", 400);
        }

        if (!empty($user['avatar']) && str_starts_with($user['avatar'], '/avatar_user/')) {
            $oldPath = __DIR__ . '/../../public' . $user['avatar'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array(strtolower($ext), $allowed)) {
            throw new Exception("Invalid file type", 400);
        }

        $filename = uniqid('avatar_') . '.' . $ext;
        $targetDir = __DIR__ . '/../../public/avatar_user/';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0777, true)) {
            throw new Exception("Failed to create avatar directory", 500);
        }

        $targetPath = $targetDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception("Failed to upload avatar file", 500);
        }

        $avatarPath = '/avatar_user/' . $filename;

        $updated = $this->userModel->updateAvatar($userId, $avatarPath);
        if (!$updated) {
            throw new Exception("Failed to update avatar in database", 500);
        }

        return [
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatar' => $avatarPath
        ];
    }

    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        if (strlen($newPassword) < 8) {
            throw new Exception("New password must be at least 8 characters", 400);
        }

        $user = $this->userModel->getUserById($userId);
        if (!$user) {
            throw new Exception("User not found", 404);
        }

        if (!password_verify($currentPassword, $user['password'])) {
            throw new Exception("Current password is incorrect", 401);
        }

        $updated = $this->userModel->updatePassword($userId, $newPassword);

        if (!$updated) {
            throw new Exception("Failed to update password", 500);
        }

        return [
            'success' => true,
            'message' => 'Password updated successfully'
        ];
    }
}
