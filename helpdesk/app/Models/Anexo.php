<?php

namespace App\Models;

use App\Core\Model;

class Anexo extends Model
{
    protected string $table = 'anexos';

    public function porChamado(int $chamadoId): array
    {
        $sql = "SELECT a.*, u.nome AS usuario_nome
                FROM anexos a
                INNER JOIN usuarios u ON u.id = a.usuario_id
                WHERE a.chamado_id = :chamado_id
                ORDER BY a.criado_em DESC";
        return $this->query($sql, ['chamado_id' => $chamadoId])->fetchAll();
    }
}
