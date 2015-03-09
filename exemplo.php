<?php

echo '<h2>Imagem Original (1920x1200)</h2><img src="imagemOriginal.jpg"/><hr/>';

require 'ReduzQualidadeImg.php';

$reducao = new ReduzQualidadeImg();

// propriedades
$reducao->arquivoOrigem = 'imagemOriginal.jpg'; // caminho da imagem para a redução
$reducao->dimensaoMaxima = 1000; // dimensão máxima em pixels na largura ou na altura, 800 por padrão
$reducao->qualidadeMinima = 50; // qualidade mínima da compressão jpeg, 50 por padrão
$reducao->tamanhoMaximo = 307200; // tamanho máximo em bytes, 300kb por padrão
$reducao->mantemTransparencia = true; // mantém a transparência se houver em arquivos gif e png, true por padrão

// reduz()
// faz a redução da imagem, otimizando em dimensão e tamanho o máximo possível.
// retorna 1 em caso de sucesso,
// retorna 0 caso não tenha sido necessário reduzir,
// retorna -1 em caso de erro
if ($reducao->reduz() == 1) {
    // salvar(string arquivoDestino, [bool forcarSalvamento])
    // salva a imagem no caminho especificado do arquivoDestino, de acordo com a extensão de origem.
    $reducao->salvar('imagemReduzida.jpg'); // salva o arquivo no caminho especificado
    echo '<h2>Imagem reduzida (dimens&atilde;o m&aacute;xima 1000px)</h2><img src="imagemReduzida.jpg"/><hr/>';
}
elseif ($reducao->reduz() == 0) {
    // quando não há a redução, por padrão, não ocorre o salvamento da imagem.
    // podemos tratar o arquivo como quisermos.
}

// Poderemos também usar este outro fluxo, que independente de reduzir ou não, a imagem será salva no caminho especificado.
// $reducao->reduz();
// $reducao->salvar('imagemSalvaForcadamento.jpg',true);


// salvarMiniatura(string arquivoDestino, largura, altura)
// Se quiser salvar uma thumbnail, basta usar o salvarMiniatura() com o tamanho especificado que ele redimensiona proporcionalmente e centralizado dentro do tamanho especificado.
// retorna true em caso de sucesso
// retorna false em caso de erro
$reducao->salvarMiniatura('miniatura.jpg',250,150); // arquivo de saída, largura e altura da miniatura
echo '<h2>Miniatura (250x150)</h2><img src="miniatura.jpg"/><hr/>';

// reduzComWatermark(string arquivoWatermark, posicao)
// posicao: corresponde ao posicionamento da marca d'agua na imagem (1 para canto superior esquerdo, 9 para canto inferior direito, 5 para o centro, etc)
//   1  2  3
//   4  5  6
//   7  8  9
$reducao->reduzComWatermark('watermark.png',9);
echo '<h2>Adicionando Watermark...</h2><img src="watermark.png"/><hr/>';


$reducao->salvar('imagemComWatermark.jpg',true);
echo '<h2>Imagem com Watermark</h2><img src="imagemComWatermark.jpg"/><hr/>';