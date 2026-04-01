<?php
namespace App\Controllers;

use App\Repositories\MovementRepository;

class MovementController {
    private MovementRepository $Repository;
    public function __construct(MovementRepository $Repository)
    {
        $this->Repository = $Repository;
    }

    public function get(?string $identifier): array 
    {
        if (!$identifier) {
            http_response_code(400);
            return ['error' => 'Identifier is required'];
        }

        try {
            $this->Repository->get($identifier);
        } catch (\Exception $e) {
            if (is_numeric($e->getCode())) {
                http_response_code($e->getCode());
                return ['error' => $e->getMessage()];
            } else {
                http_response_code(500);
                return ['error' => "Unexpected server error"];
            }
        }

        http_response_code(200);
        $rankingData = $this->Repository->getRanking($identifier);
        return $rankingData;
    }
}