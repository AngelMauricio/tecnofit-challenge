<?php
declare(strict_types=1);
namespace Repositories;

use PDO;

class MovementRepository
{
    private PDO $db;

    // Recebemos a conexão pronta de fora (Injeção de Dependência)
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function get(string $identifier): object
    {
        $where = is_numeric($identifier) ? "m.id = :arg" : "m.name = :arg";
        $sql = <<<SQL
            SELECT 
                m.id, m.name
            FROM movement m
            WHERE {$where}
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['arg' => $identifier]);

        $object = $stmt->fetchObject();
        if (! $object) {
            throw new \Exception('Movement not found', 404);
        }
        return $object;
    }

    public function getRanking(string $identifier): array
    {
        $where = is_numeric($identifier) ? "m.id = :arg" : "m.name = :arg";
        $sql = <<<SQL
            SELECT 
                ranking_position,
                user_name,
                personal_record,
                record_date
            FROM (
                SELECT 
                    u.name AS user_name,
                    pr.value AS personal_record,
                    pr.date AS record_date,
                    RANK() OVER (ORDER BY pr.value DESC) AS ranking_position,
                    ROW_NUMBER() OVER (PARTITION BY pr.user_id ORDER BY pr.value DESC, pr.date DESC) as personal_score_rank
                FROM personal_record pr
                INNER JOIN user u ON pr.user_id = u.id
                INNER JOIN movement m ON pr.movement_id = m.id
                WHERE {$where}
            ) AS ranked_data
            WHERE personal_score_rank = 1
            ORDER BY ranking_position ASC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['arg' => $identifier]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}