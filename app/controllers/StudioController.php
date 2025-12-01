<?php

require_once __DIR__ . "./../services/StudioService.php";

class StudioController
{
    private $studioService;

    public function __construct($conn)
    {
        $this->studioService = new StudioService($conn);
    }

    public function index()
    {
        header('Content-Type: application/json');

        try {
            $response = $this->studioService->getAllStudios();
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->studioService->getStudioById($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 404);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function store()
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            $required = ['name', 'seat_capacity', 'type'];
            $missing = [];
            foreach ($required as $field) {
                if (!isset($data[$field]) || $data[$field] === '') {
                    $missing[] = $field;
                }
            }

            if (!empty($missing)) {
                throw new Exception("Missing required fields: " . implode(", ", $missing), 400);
            }

            $response = $this->studioService->insertStudio(
                $data['name'],
                $data['seat_capacity'],
                $data['type']
            );

            http_response_code(201);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        header('Content-Type: application/json');

        try {
            $response = $this->studioService->deleteStudio($id);
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
