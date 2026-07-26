<?php

namespace App\Models;

use App\Core\Model;

class Chamado extends Model
{
    protected string $table = 'chamados';

    /**
     * Gera o próximo código sequencial no formato CH-AAAA-00001
     */
    public function gerarCodigo(): string
    {
        $ano = date('Y');
        $sql = "SELECT COUNT(*) AS total FROM chamados WHERE codigo LIKE :prefixo";
        $prefixo = "CH-{$ano}-%";
        $row = $this->query($sql, ['prefixo' => $prefixo])->fetch();
        $proximo = ((int) $row['total']) + 1;
        return sprintf('CH-%s-%05d', $ano, $proximo);
    }

    /**
     * Busca um chamado completo com dados relacionados (JOINs).
     */
    public function buscarCompleto(int $id): array|false
    {
        $sql = "SELECT c.*,
                    cat.nome AS categoria_nome,
                    s.nome AS setor_nome,
                    s.sla_horas,
                    sol.nome AS solicitante_nome, sol.email AS solicitante_email,
                    resp.nome AS responsavel_nome
                FROM chamados c
                LEFT JOIN categorias cat ON cat.id = c.categoria_id
                LEFT JOIN setores s ON s.id = c.setor_id
                LEFT JOIN usuarios sol ON sol.id = c.solicitante_id
                LEFT JOIN usuarios resp ON resp.id = c.responsavel_id
                WHERE c.id = :id";
        return $this->query($sql, ['id' => $id])->fetch();
    }

    /**
     * Lista chamados com filtros dinâmicos e paginação.
     * $filtros pode conter: status, prioridade, setor_id, categoria_id, busca, solicitante_id, responsavel_id
     */
    public function listarComFiltros(array $filtros = [], int $pagina = 1, int $porPagina = 15): array
    {
        $where = [];
        $params = [];

        if (!empty($filtros['status'])) {
            $where[] = 'c.status = :status';
            $params['status'] = $filtros['status'];
        }
        if (!empty($filtros['prioridade'])) {
            $where[] = 'c.prioridade = :prioridade';
            $params['prioridade'] = $filtros['prioridade'];
        }
        if (!empty($filtros['setor_id'])) {
            $where[] = 'c.setor_id = :setor_id';
            $params['setor_id'] = $filtros['setor_id'];
        }
        if (!empty($filtros['categoria_id'])) {
            $where[] = 'c.categoria_id = :categoria_id';
            $params['categoria_id'] = $filtros['categoria_id'];
        }
        if (!empty($filtros['solicitante_id'])) {
            $where[] = 'c.solicitante_id = :solicitante_id';
            $params['solicitante_id'] = $filtros['solicitante_id'];
        }
        if (!empty($filtros['responsavel_id'])) {
            $where[] = 'c.responsavel_id = :responsavel_id';
            $params['responsavel_id'] = $filtros['responsavel_id'];
        }
        if (!empty($filtros['busca'])) {
            $where[] = '(c.titulo LIKE :busca OR c.codigo LIKE :busca OR c.descricao LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT c.*, cat.nome AS categoria_nome, s.nome AS setor_nome,
                    sol.nome AS solicitante_nome, resp.nome AS responsavel_nome
                FROM chamados c
                LEFT JOIN categorias cat ON cat.id = c.categoria_id
                LEFT JOIN setores s ON s.id = c.setor_id
                LEFT JOIN usuarios sol ON sol.id = c.solicitante_id
                LEFT JOIN usuarios resp ON resp.id = c.responsavel_id
                {$whereSql}
                ORDER BY FIELD(c.prioridade,'urgente','alta','media','baixa'), c.criado_em DESC
                LIMIT {$porPagina} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->execute();
        $dados = $stmt->fetchAll();

        $sqlCount = "SELECT COUNT(*) AS total FROM chamados c {$whereSql}";
        $total = (int) $this->query($sqlCount, $params)->fetch()['total'];

        return [
            'dados' => $dados,
            'total' => $total,
            'paginas' => (int) ceil($total / $porPagina),
            'pagina_atual' => $pagina,
        ];
    }

    // ---------------------------------------------------------------
    // MÉTRICAS PARA O DASHBOARD
    // ---------------------------------------------------------------

    public function estatisticasGerais(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN DATE(criado_em) = CURDATE() THEN 1 ELSE 0 END) AS hoje,
                    SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) AS em_andamento,
                    SUM(CASE WHEN status = 'em_espera' THEN 1 ELSE 0 END) AS em_espera,
                    SUM(CASE WHEN status = 'fechado' THEN 1 ELSE 0 END) AS fechados,
                    SUM(CASE WHEN prioridade = 'urgente' AND status NOT IN ('fechado','cancelado') THEN 1 ELSE 0 END) AS urgentes,
                    SUM(CASE WHEN status NOT IN ('fechado','cancelado') THEN 1 ELSE 0 END) AS abertos
                FROM chamados";
        return $this->query($sql)->fetch();
    }

    public function tempoMedioAtendimentoHoras(): float
    {
        // Tempo médio entre criação e primeira mudança para "em_andamento"
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, c.criado_em, h.criado_em)) / 60 AS media
                FROM chamados c
                INNER JOIN historicos h ON h.chamado_id = c.id AND h.acao = 'status_alterado' AND h.descricao LIKE '%Em andamento%'";
        $row = $this->query($sql)->fetch();
        return round((float) ($row['media'] ?? 0), 1);
    }

    public function tempoMedioResolucaoHoras(): float
    {
        $sql = "SELECT AVG(TIMESTAMPDIFF(MINUTE, criado_em, fechado_em)) / 60 AS media
                FROM chamados WHERE fechado_em IS NOT NULL";
        $row = $this->query($sql)->fetch();
        return round((float) ($row['media'] ?? 0), 1);
    }

    public function chamadosPorDia(int $dias = 14): array
    {
        $sql = "SELECT DATE(criado_em) AS dia, COUNT(*) AS total
                FROM chamados
                WHERE criado_em >= DATE_SUB(CURDATE(), INTERVAL :dias DAY)
                GROUP BY DATE(criado_em)
                ORDER BY dia";
        return $this->query($sql, ['dias' => $dias])->fetchAll();
    }

    public function chamadosPorSetor(): array
    {
        $sql = "SELECT s.nome AS setor, COUNT(c.id) AS total
                FROM setores s
                LEFT JOIN chamados c ON c.setor_id = s.id
                GROUP BY s.id, s.nome
                ORDER BY total DESC";
        return $this->query($sql)->fetchAll();
    }

    public function chamadosPorPrioridade(): array
    {
        $sql = "SELECT prioridade, COUNT(*) AS total FROM chamados GROUP BY prioridade";
        return $this->query($sql)->fetchAll();
    }

    public function chamadosPorStatus(): array
    {
        $sql = "SELECT status, COUNT(*) AS total FROM chamados GROUP BY status";
        return $this->query($sql)->fetchAll();
    }
}
