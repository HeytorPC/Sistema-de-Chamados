<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Chamado;
use App\Models\Setor;

class RelatorioController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['administrador', 'atendente']);

        $chamadoModel = new Chamado();
        $filtros = array_filter([
            'status'     => $this->input('status'),
            'prioridade' => $this->input('prioridade'),
            'setor_id'   => $this->input('setor_id'),
        ]);

        $resultado = $chamadoModel->listarComFiltros($filtros, 1, 1000);
        $setorModel = new Setor();

        $this->view('relatorios/index', [
            'chamados' => $resultado['dados'],
            'setores'  => $setorModel->ativos(),
            'filtros'  => $filtros,
        ]);
    }

    /** Exporta os resultados filtrados em CSV (compatível com Excel) */
    public function exportarExcel(): void
    {
        $this->requireRole(['administrador', 'atendente']);

        $chamadoModel = new Chamado();
        $filtros = array_filter([
            'status'     => $this->input('status'),
            'prioridade' => $this->input('prioridade'),
            'setor_id'   => $this->input('setor_id'),
        ]);
        $resultado = $chamadoModel->listarComFiltros($filtros, 1, 5000);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_chamados_' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fputs($out, "\xEF\xBB\xBF"); // BOM para acentuação correta no Excel
        fputcsv($out, ['Código', 'Título', 'Setor', 'Categoria', 'Prioridade', 'Status', 'Solicitante', 'Responsável', 'Criado em', 'Fechado em'], ';');

        foreach ($resultado['dados'] as $c) {
            fputcsv($out, [
                $c['codigo'], $c['titulo'], $c['setor_nome'], $c['categoria_nome'],
                $c['prioridade'], $c['status'], $c['solicitante_nome'], $c['responsavel_nome'] ?? '-',
                $c['criado_em'], $c['fechado_em'] ?? '-',
            ], ';');
        }

        fclose($out);
        exit;
    }

    /** Exporta os resultados filtrados em PDF (HTML simples pronto para impressão) */
    public function exportarPdf(): void
    {
        $this->requireRole(['administrador', 'atendente']);

        $chamadoModel = new Chamado();
        $filtros = array_filter([
            'status'     => $this->input('status'),
            'prioridade' => $this->input('prioridade'),
            'setor_id'   => $this->input('setor_id'),
        ]);
        $resultado = $chamadoModel->listarComFiltros($filtros, 1, 5000);

        $this->viewOnly('relatorios/pdf', ['chamados' => $resultado['dados']]);
    }
}
