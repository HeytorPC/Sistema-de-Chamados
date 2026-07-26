<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Chamado;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $chamadoModel = new Chamado();

        $stats = $chamadoModel->estatisticasGerais();
        $stats['tempo_medio_atendimento'] = $chamadoModel->tempoMedioAtendimentoHoras();
        $stats['tempo_medio_resolucao'] = $chamadoModel->tempoMedioResolucaoHoras();

        $porDia = $chamadoModel->chamadosPorDia(14);
        $porSetor = $chamadoModel->chamadosPorSetor();
        $porPrioridade = $chamadoModel->chamadosPorPrioridade();
        $porStatus = $chamadoModel->chamadosPorStatus();

        // Chamados recentes do usuário (colaborador vê os seus; atendente/admin vê todos)
        $filtros = Auth::isAtendente() ? [] : ['solicitante_id' => Auth::id()];
        $recentes = $chamadoModel->listarComFiltros($filtros, 1, 8);

        $this->view('dashboard/index', [
            'stats' => $stats,
            'porDia' => $porDia,
            'porSetor' => $porSetor,
            'porPrioridade' => $porPrioridade,
            'porStatus' => $porStatus,
            'recentes' => $recentes['dados'],
        ]);
    }
}
