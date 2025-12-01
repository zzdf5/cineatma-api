<?php

require_once __DIR__ . "./../models/StudioModel.php";

class StudioService
{
    private $studioModel;

    public function __construct($conn)
    {
        $this->studioModel = new StudioModel($conn);
    }

    public function getAllStudios()
    {
        $studios = $this->studioModel->getAllStudios();
        return [
            'success' => true,
            'data' => $studios
        ];
    }

    public function getStudioById($id)
    {
        $studio = $this->studioModel->getStudioById($id);
        if (!$studio) {
            throw new Exception('Studio not found', 404);
        }

        return [
            'success' => true,
            'data' => $studio
        ];
    }

    public function insertStudio($name, $seatCapacity, $type)
    {
        $studio = $this->studioModel->insertStudio($name, $seatCapacity, $type);
        if (!$studio) {
            throw new Exception('Failed to create studio');
        }

        return [
            'success' => true,
            'message' => 'Studio created successfully',
            'studio' => $studio
        ];
    }

    public function deleteStudio($id)
    {
        $studio = $this->studioModel->getStudioById($id);
        if (!$studio) {
            throw new Exception('Studio not found', 404);
        }

        $showtimeCount = $this->studioModel->countShowtimesByStudioId($id);
        if ($showtimeCount > 0) {
            throw new Exception("Cannot delete studio: there are $showtimeCount showtimes still using this studio", 400);
        }

        $deleted = $this->studioModel->deleteStudio($id);
        if (!$deleted) {
            throw new Exception('Failed to delete studio');
        }

        return [
            'success' => true,
            'message' => 'Studio deleted successfully'
        ];
    }

    public function updateStudio($id, $name, $seatCapacity, $type)
    {
        $studio = $this->studioModel->getStudioById($id);
        if (!$studio) {
            throw new Exception('Studio not found', 404);
        }

        $updatedStudio = $this->studioModel->updateStudio($id, $name, $seatCapacity, $type);
        if (!$updatedStudio) {
            throw new Exception('Failed to update studio');
        }

        return [
            'success' => true,
            'message' => 'Studio updated successfully',
            'studio' => $updatedStudio
        ];
    }
}
