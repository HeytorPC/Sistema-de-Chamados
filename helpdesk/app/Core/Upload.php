<?php

namespace App\Core;

/**
 * Classe responsável por validar e mover arquivos enviados com segurança.
 */
class Upload
{
    /**
     * Processa um único arquivo do array $_FILES.
     * Retorna array com dados do arquivo salvo ou lança exceção em caso de erro.
     */
    public static function processar(array $file, string $subpasta = 'chamados'): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no upload do arquivo (código ' . $file['error'] . ').');
        }

        if ($file['size'] > UPLOAD_MAX_SIZE) {
            throw new \RuntimeException('Arquivo excede o tamanho máximo permitido (' . (UPLOAD_MAX_SIZE / 1024 / 1024) . 'MB).');
        }

        $nomeOriginal = basename($file['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (!in_array($extensao, UPLOAD_ALLOWED_EXT, true)) {
            throw new \RuntimeException('Extensão de arquivo não permitida: .' . $extensao);
        }

        // Valida o tipo MIME real do arquivo (não confia apenas na extensão)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $mimesPermitidos = [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip', 'application/x-zip-compressed',
        ];

        if (!in_array($mimeReal, $mimesPermitidos, true)) {
            throw new \RuntimeException('Tipo de arquivo não permitido (MIME: ' . $mimeReal . ').');
        }

        $pastaDestino = UPLOAD_PATH . '/' . $subpasta;
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }

        // Nome de armazenamento único e seguro (evita path traversal e colisões)
        $nomeArmazenado = bin2hex(random_bytes(16)) . '.' . $extensao;
        $destino = $pastaDestino . '/' . $nomeArmazenado;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new \RuntimeException('Não foi possível salvar o arquivo enviado.');
        }

        return [
            'nome_original'   => $nomeOriginal,
            'nome_armazenado' => $subpasta . '/' . $nomeArmazenado,
            'tipo_mime'       => $mimeReal,
            'tamanho'         => $file['size'],
        ];
    }

    /**
     * Normaliza $_FILES['campo'] (múltiplos arquivos) em uma lista de arquivos individuais.
     */
    public static function normalizarMultiplos(array $filesField): array
    {
        $arquivos = [];
        if (!isset($filesField['name']) || !is_array($filesField['name'])) {
            return [$filesField];
        }
        foreach ($filesField['name'] as $i => $nome) {
            if ($filesField['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $arquivos[] = [
                'name'     => $filesField['name'][$i],
                'type'     => $filesField['type'][$i],
                'tmp_name' => $filesField['tmp_name'][$i],
                'error'    => $filesField['error'][$i],
                'size'     => $filesField['size'][$i],
            ];
        }
        return $arquivos;
    }
}
