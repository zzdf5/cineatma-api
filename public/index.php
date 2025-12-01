<?php

require_once __DIR__ . "./../app/controllers/UserController.php";
require_once __DIR__ . "./../app/controllers/StudioController.php";
require_once __DIR__ . "./../app/controllers/MovieController.php";
require_once __DIR__ . "./../app/controllers/ShowtimeController.php";
require_once __DIR__ . "./../app/controllers/BookingController.php";

$conn = getConnection();

$userController = new UserController($conn);
$studioController = new StudioController($conn);
$movieController = new MovieController($conn);
$showtimeController = new ShowtimeController($conn);
$bookingController = new BookingController($conn);

$routes = [
    'POST' => [
        // Auth
        '/api/register' => [$userController, 'register'],
        '/api/verify' => [$userController, 'verifyRegistration'],
        '/api/login' => [$userController, 'login'],
        '/api/logout' => [$userController, 'logout'],

        // Studio
        '/api/studio' => [$studioController, 'store'],

        // Movie
        '/api/movie' => [$movieController, 'store'],

        // Showtime
        '/api/showtime' => [$showtimeController, 'store'],

        // Booking
        '/api/booking' => [$bookingController, 'store']
    ],
    'GET' => [
        '/php_info' => function () {
            phpinfo();
        },
        // Studio
        '/api/studios' => [$studioController, 'index'],
        '/api/studio/{id}' => [$studioController, 'show'],

        // Movie
        '/api/movies' => [$movieController, 'index'],
        '/api/movie/{id}' => [$movieController, 'show'],

        // Showtime
        '/api/showtimes' => [$showtimeController, 'index'],
        '/api/showtime/{id}' => [$showtimeController, 'show'],

        // Booking
        '/api/bookings' => [$bookingController, 'index'],
        '/api/booking/{id}' => [$bookingController, 'show'],
        '/api/bookings/user/{id}' => [$bookingController, 'getByUserId'],
        '/api/bookings/showtime/{id}' => [$bookingController, 'getByShowtimeId']
    ],
    'DELETE' => [
        // Studio
        '/api/studio/{id}' => [$studioController, 'destroy'],

        // Movie
        '/api/movie/{id}' => [$movieController, 'destroy'],

        // Showtime
        '/api/showtime/{id}' => [$showtimeController, 'destroy'],

        // Booking
        '/api/booking/{id}' => [$bookingController, 'destroy']
    ]
];


$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];

if (isset($routes[$method][$path])) {
    call_user_func($routes[$method][$path]);
} else {
    $found = false;

    foreach ($routes[$method] as $route => $handler) {
        if (strpos($route, '{id}') !== false) {
            $pattern = "@^" . str_replace('{id}', '(\d+)', $route) . "$@";
            if (preg_match($pattern, $path, $matches)) {
                $found = true;
                call_user_func($handler, $matches[1]);
                break;
            }
        } elseif ($route === $path) {
            $found = true;
            call_user_func($handler);
            break;
        }
    }

    if (!$found) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    }
}
