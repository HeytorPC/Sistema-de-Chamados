<?php

namespace App\Models;

use App\Core\Model;

/**
 * Model de histórico: registros IMUTÁVEIS (não há update/delete de histórico).
 */
class Historico extends Model
{
    protected string $table = 'historicos';

    public function registrar(int $chamadoId, int $usuarioId, string $acao, string $descricao): int
    {
        return $this->create([
            'chamado_id' => $chamadoId,
            'usuario_id' => $usuarioId,
            'acao'       => $acao,
            'descricao'  => $descricao,
        ]);
    }

    public function porChamado(int $chamadoId): array
    {
        $sql = "SELECT h.*, u.nome AS usuario_nome
                FROM historicos h
                INNER JOIN usuarios u ON u.id = h.usuario_id
                WHERE h.chamado_id = :chamado_id
                ORDER BY h.criado_em ASC";
        return $this->query($sql, ['chamado_id' => $chamadoId])->fetchAll();
    }

    // Sobrescreve para impedir alteração/remoção do histórico (imutabilidade)
    public function update(int $id, array $data): bool
    {
        throw new \LogicException('Registros de histórico são imutáveis e não podem ser alterados.');
    }

    public function delete(int $id): bool
    {
        throw new \LogicException('Registros de histórico são imutáveis e não podem ser removidos.');
    }
}
