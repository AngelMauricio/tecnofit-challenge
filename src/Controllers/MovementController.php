<?php
namespace Controllers;

use Repositories\MovementRepository;

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
            http_response_code($e->getCode());
            return ['error' => $e->getMessage()];
        }

        $rankingData = $this->Repository->getRanking($identifier);
        return $rankingData;
    }
}