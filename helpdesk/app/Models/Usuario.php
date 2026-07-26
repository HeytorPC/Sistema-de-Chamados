<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email): array|false
    {
        return $this->query(
            'SELECT * FROM usuarios WHERE email = :email LIMIT 1',
            ['email' => $email]
        )->fetch();
    }

    /** Lista usuários com nome do setor (JOIN) */
    public function allComSetor(): array
    {
        $sql = "SELECT u.*, s.nome AS setor_nome
                FROM usuarios u
                LEFT JOIN setores s ON s.id = u.setor_id
                ORDER BY u.nome";
        return $this->query($sql)->fetchAll();
    }

    public function atendentesPorSetor(int $setorId): array
    {
        $sql = "SELECT id, nome FROM usuarios
                WHERE setor_id = :setor_id AND perfil IN ('atendente','administrador') AND ativo = 1
                ORDER BY nome";
        return $this->query($sql, ['setor_id' => $setorId])->fetchAll();
    }

    public function emailExiste(string $email, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT id FROM usuarios WHERE email = :email';
        $params = ['email' => $email];
        if ($ignorarId) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignorarId;
        }
        return (bool) $this->query($sql, $params)->fetch();
    }
}
