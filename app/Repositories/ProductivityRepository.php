<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use App\Helpers\Uuid;

class ProductivityRepository extends Repository
{
    public function createStudySession(array $data): string
    {
        $id = Uuid::v4();
        
        $sql = "INSERT INTO sessoes_estudo 
                (id, usuario_id, disciplina_id, inicio_sessao, fim_sessao, duracao_minutos, tipo_tecnica) 
                VALUES (:id, :usuario_id, :disciplina_id, :inicio_sessao, :fim_sessao, :duracao_minutos, :tipo_tecnica)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'usuario_id' => $data['usuario_id'],
            'disciplina_id' => $data['disciplina_id'],
            'inicio_sessao' => $data['inicio_sessao'],
            'fim_sessao' => $data['fim_sessao'],
            'duracao_minutos' => $data['duracao_minutos'],
            'tipo_tecnica' => $data['tipo_tecnica']
        ]);
        
        return $id;
    }

    public function updateDailyStudyTime(string $userId, string $date, int $minutes): void
    {
        // First check if record exists for this date
        $sql = "SELECT id, tempo_estudo_minutos FROM registros_produtividade 
                WHERE usuario_id = :usuario_id AND data_registro = :data_registro";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $userId, 'data_registro' => $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            $newTime = $record['tempo_estudo_minutos'] + $minutes;
            $updateSql = "UPDATE registros_produtividade SET tempo_estudo_minutos = :tempo_estudo_minutos WHERE id = :id";
            $this->db->prepare($updateSql)->execute([
                'tempo_estudo_minutos' => $newTime,
                'id' => $record['id']
            ]);
        } else {
            $id = Uuid::v4();
            $insertSql = "INSERT INTO registros_produtividade (id, usuario_id, data_registro, tempo_estudo_minutos) 
                          VALUES (:id, :usuario_id, :data_registro, :tempo_estudo_minutos)";
            $this->db->prepare($insertSql)->execute([
                'id' => $id,
                'usuario_id' => $userId,
                'data_registro' => $date,
                'tempo_estudo_minutos' => $minutes
            ]);
        }
    }

    public function incrementCompletedTasks(string $userId, string $date): void
    {
        $sql = "SELECT id, tarefas_concluidas FROM registros_produtividade 
                WHERE usuario_id = :usuario_id AND data_registro = :data_registro";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['usuario_id' => $userId, 'data_registro' => $date]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($record) {
            $newTasks = $record['tarefas_concluidas'] + 1;
            $updateSql = "UPDATE registros_produtividade SET tarefas_concluidas = :tarefas_concluidas WHERE id = :id";
            $this->db->prepare($updateSql)->execute([
                'tarefas_concluidas' => $newTasks,
                'id' => $record['id']
            ]);
        } else {
            $id = Uuid::v4();
            $insertSql = "INSERT INTO registros_produtividade (id, usuario_id, data_registro, tarefas_concluidas) 
                          VALUES (:id, :usuario_id, :data_registro, :tarefas_concluidas)";
            $this->db->prepare($insertSql)->execute([
                'id' => $id,
                'usuario_id' => $userId,
                'data_registro' => $date,
                'tarefas_concluidas' => 1
            ]);
        }
    }

    public function getStatistics(string $userId, string $filter, ?string $disciplineId): array
    {
        $dateFilter = "";
        $params = ['usuario_id' => $userId];

        if ($filter === 'dia') {
            $dateFilter = "AND DATE(inicio_sessao) = CURDATE()";
        } elseif ($filter === 'semana') {
            $dateFilter = "AND YEARWEEK(inicio_sessao, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($filter === 'mes') {
            $dateFilter = "AND MONTH(inicio_sessao) = MONTH(CURDATE()) AND YEAR(inicio_sessao) = YEAR(CURDATE())";
        }

        $disciplineFilterSession = "";
        $disciplineFilterTask = "";
        if ($disciplineId) {
            $disciplineFilterSession = "AND disciplina_id = :disciplina_id";
            $disciplineFilterTask = "AND disciplina_id = :disciplina_id";
            $params['disciplina_id'] = $disciplineId;
        }

        // Sessions count & time
        $sqlSessions = "SELECT COUNT(id) as total_sessoes, COALESCE(SUM(duracao_minutos), 0) as tempo_estudado 
                        FROM sessoes_estudo 
                        WHERE usuario_id = :usuario_id $dateFilter $disciplineFilterSession";
        $stmtSessions = $this->db->prepare($sqlSessions);
        $stmtSessions->execute($params);
        $sessionsData = $stmtSessions->fetch(PDO::FETCH_ASSOC);

        // Tasks completed
        $taskDateFilter = str_replace('inicio_sessao', 'COALESCE(data_vencimento, criado_em)', $dateFilter);
        $sqlTasks = "SELECT COUNT(id) as tarefas_concluidas 
                     FROM tarefas 
                     WHERE usuario_id = :usuario_id AND status = 'concluido' $taskDateFilter $disciplineFilterTask";
        $stmtTasks = $this->db->prepare($sqlTasks);
        $stmtTasks->execute($params);
        $tasksData = $stmtTasks->fetch(PDO::FETCH_ASSOC);

        // Evolution data for charts (last 7 days by default, or depends on filter)
        // Group by day for the chart
        if ($filter === 'mes') {
            $groupBy = "DATE(inicio_sessao)";
            $limit = 30;
        } elseif ($filter === 'semana') {
            $groupBy = "DATE(inicio_sessao)";
            $limit = 7;
        } else { // dia
            $groupBy = "HOUR(inicio_sessao)";
            $limit = 24;
        }

        $sqlChart = "SELECT $groupBy as label, COALESCE(SUM(duracao_minutos), 0) as valor 
                     FROM sessoes_estudo 
                     WHERE usuario_id = :usuario_id $dateFilter $disciplineFilterSession
                     GROUP BY label
                     ORDER BY label ASC";
        $stmtChart = $this->db->prepare($sqlChart);
        $stmtChart->execute($params);
        $chartData = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

        return [
            'tempo_estudado' => (int) $sessionsData['tempo_estudado'],
            'sessoes_estudo' => (int) $sessionsData['total_sessoes'],
            'tarefas_concluidas' => (int) $tasksData['tarefas_concluidas'],
            'produtividade' => $this->calculateProductivityScore((int) $sessionsData['tempo_estudado'], (int) $tasksData['tarefas_concluidas']),
            'grafico' => $chartData
        ];
    }

    private function calculateProductivityScore(int $timeStudied, int $tasksCompleted): int
    {
        // Simple algorithm: 1 point per 10 minutes studied + 5 points per task completed
        return (int) floor($timeStudied / 10) + ($tasksCompleted * 5);
    }
}
