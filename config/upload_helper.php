<?php

function processarUploadImagem(array $arquivo, array &$erros): ?string
{
    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        $erros['imagem'] = 'Erro ao enviar a imagem.';
        return null;
    }

    $tamanhoMaximo = 5 * 1024 * 1024; // 5MB
    if ($arquivo['size'] > $tamanhoMaximo) {
        $erros['imagem'] = 'A imagem deve ter no máximo 5MB.';
        return null;
    }

    $infoImagem = @getimagesize($arquivo['tmp_name']);
    if ($infoImagem === false) {
        $erros['imagem'] = 'O arquivo enviado não é uma imagem válida.';
        return null;
    }

    $extensoesPermitidas = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    if (!isset($extensoesPermitidas[$infoImagem[2]])) {
        $erros['imagem'] = 'Formato de imagem não suportado. Use JPG, PNG, GIF ou WEBP.';
        return null;
    }

    $extensao = $extensoesPermitidas[$infoImagem[2]];
    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensao;
    $destino = __DIR__ . '/../uploads/produtos/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        $erros['imagem'] = 'Não foi possível salvar a imagem enviada.';
        return null;
    }

    return $nomeArquivo;
}
