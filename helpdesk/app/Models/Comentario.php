<?php

namespace App\Models;

use App\Core\Model;

class Comentario extends Model
{
    protected string $table = 'comentarios';

    public function porChamado(int $chamadoId, bool $incluirInternos = true): array
    {
        $sql = "SELECT co.*, u.nome AS usuario_nome, u.avatar AS usuario_avatar, u.perfil AS usuario_perfil
                FROM comentarios co
                INNER JOIN usuarios u ON u.id = co.usuario_id
                WHERE co.chamado_id = :chamado_id";
        if (!$incluirInternos) {
            $sql .= " AND co.interno = 0";
        }
        $sql .= " ORDER BY co.criado_em ASC";
        return $this->query($sql, ['chamado_id' => $chamadoId])->fetchAll();
    }
}
