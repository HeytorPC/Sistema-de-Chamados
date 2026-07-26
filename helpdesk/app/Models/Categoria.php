<?php

namespace App\Models;

use App\Core\Model;

class Categoria extends Model
{
    protected string $table = 'categorias';

    public function porSetor(int $setorId): array
    {
        return $this->query(
            'SELECT * FROM categorias WHERE setor_id = :setor_id AND ativo = 1 ORDER BY nome',
            ['setor_id' => $setorId]
        )->fetchAll();
    }

    public function ativas(): array
    {
        return $this->query('SELECT * FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll();
    }
}
