<?php

namespace App\Models;

use App\Core\Model;

class Setor extends Model
{
    protected string $table = 'setores';

    public function ativos(): array
    {
        return $this->query('SELECT * FROM setores WHERE ativo = 1 ORDER BY nome')->fetchAll();
    }

    /** Retorna setores com a contagem de chamados abertos (para dashboard/admin) */
    public function comContagemChamados(): array
    {
        $sql = "SELECT s.*,
                    (SELECT COUNT(*) FROM chamados c WHERE c.setor_id = s.id AND c.status NOT IN ('fechado','cancelado')) AS chamados_abertos,
                    (SELECT COUNT(*) FROM usuarios u WHERE u.setor_id = s.id) AS total_usuarios
                FROM setores s
                ORDER BY s.nome";
        return $this->query($sql)->fetchAll();
    }
}
