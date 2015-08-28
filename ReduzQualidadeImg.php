<?php
namespace app\helpers;

class ReduzQualidadeImg {
    private $imagemOriginal;
    private $larguraOriginal;
    private $alturaOriginal;

    private $imagemFinal;
    private $larguraFinal;
    private $alturaFinal;
    private $qualidade;

    public $qualidadeMinima = 50;
    public $dimensaoMaxima = 800;
    public $tamanhoMaximo = 307200;

    public $arquivoOrigem;
    private $tipo;
    private $tamanho;

    public $mantemTransparencia = true;


    private function abreImg() {
        if (!file_exists($this->arquivoOrigem)) return false;
        $this->tamanho = filesize($this->arquivoOrigem);
        list($this->larguraOriginal,$this->alturaOriginal,$this->tipo) = getimagesize($this->arquivoOrigem);
        switch ($this->tipo) {
            case IMAGETYPE_JPEG:
                $this->imagemOriginal = imagecreatefromjpeg($this->arquivoOrigem);
                break;

            case IMAGETYPE_PNG:
                $this->imagemOriginal= imagecreatefrompng($this->arquivoOrigem);
                break;

            case IMAGETYPE_GIF:
                $this->imagemOriginal = imagecreatefromgif($this->arquivoOrigem);
                break;

            default:
                return false;
                break;
        }
        return true;
    }

    private function redimensionaImagem() {
        if ($this->larguraOriginal > $this->alturaOriginal) {
            $this->larguraFinal = $this->dimensaoMaxima;
            $this->alturaFinal = ($this->dimensaoMaxima * $this->alturaOriginal) / $this->larguraOriginal;
        }
        else {
            $this->alturaFinal = $this->dimensaoMaxima;
            $this->larguraFinal = ($this->dimensaoMaxima * $this->larguraOriginal) / $this->alturaOriginal;
        }
        $this->imagemFinal = imagecreatetruecolor($this->larguraFinal,$this->alturaFinal);
        if ($this->mantemTransparencia) {
            imagealphablending($this->imagemFinal,false);
            imagesavealpha($this->imagemFinal,true);
            imagecolortransparent($this->imagemFinal, imagecolorallocatealpha($this->imagemFinal,0,0,0,127));
        }
        else {
            imagefill($this->imagemFinal,0,0,imagecolorallocate($this->imagemFinal,255,255,255));
        }
        imagecopyresampled($this->imagemFinal,$this->imagemOriginal,0,0,0,0,$this->larguraFinal,$this->alturaFinal,$this->larguraOriginal,$this->alturaOriginal);
    }

    private function calculaQualidade() {
        $this->qualidade = 100;
        $arquivoTemporario = tempnam(sys_get_temp_dir(), 'img');
        while ($this->qualidade >= $this->qualidadeMinima) {
            clearstatcache();
            imagejpeg($this->imagemFinal,$arquivoTemporario,$this->qualidade);
            if (filesize($arquivoTemporario) > $this->tamanhoMaximo) $this->qualidade -= 5;
            else break;
        }
        unlink($arquivoTemporario);
    }

    public function reduz() {
        if (!$this->abreImg()) return -1;

        if (($this->larguraOriginal > $this->dimensaoMaxima) || ($this->alturaOriginal > $this->dimensaoMaxima)) {
            $this->redimensionaImagem();
        }
        else {
            if ($this->tamanho <= $this->tamanhoMaximo) return 0;
            $this->imagemFinal = $this->imagemOriginal;
        }

        if ((($this->tipo == IMAGETYPE_PNG) || ($this->tipo == IMAGETYPE_GIF)) && ($this->mantemTransparencia)) {
            if (!$this->imagemFinal) $this->imagemFinal = $this->imagemOriginal;
            return 1;
        }

        $this->calculaQualidade();
        return 1;
    }

    public function reduzComWatermark($arquivoWatermark,$posicao) {
        if (!$this->abreImg()) return -1;

        if (($this->larguraOriginal > $this->dimensaoMaxima) || ($this->alturaOriginal > $this->dimensaoMaxima)) {
            $this->redimensionaImagem();
        }
        else {
            $this->imagemFinal = $this->imagemOriginal;
            $this->larguraFinal = $this->larguraOriginal;
            $this->alturaFinal = $this->alturaOriginal;
        }

        $imagemWatermark = imagecreatefrompng($arquivoWatermark);
        imagealphablending($imagemWatermark,true);
        imagesavealpha($imagemWatermark,true);
        list($larguraOriginalWatermark,$alturaOriginalWatermark) = getimagesize($arquivoWatermark);

        $limiteLargura = $this->larguraFinal / 3;
        $limiteAltura = $this->alturaFinal / 3;

        if ($larguraOriginalWatermark > $alturaOriginalWatermark) {
            $larguraFinalWatermark = $limiteLargura;
            $alturaFinalWatermark = ($limiteLargura * $alturaOriginalWatermark) / $larguraOriginalWatermark;
            if ($alturaFinalWatermark > $limiteAltura) {
                $alturaFinalWatermark = $limiteAltura;
                $larguraFinalWatermark = ($limiteAltura * $larguraOriginalWatermark) / $alturaOriginalWatermark;
            }
        }
        else {
            $alturaFinalWatermark = $limiteAltura;
            $larguraFinalWatermark = ($limiteAltura * $larguraOriginalWatermark) / $alturaOriginalWatermark;
            if ($larguraFinalWatermark > $limiteLargura) {
                $larguraFinalWatermark = $limiteLargura;
                $alturaFinalWatermark = ($limiteLargura * $alturaOriginalWatermark) / $larguraOriginalWatermark;
            }
        }

        switch($posicao) {
            case 1:
                $xWatermark = 0;
                $yWatermark = 0;
                break;

            case 2:
                $xWatermark = ($this->larguraFinal - $larguraFinalWatermark) / 2;
                $yWatermark = 0;
                break;

            case 3:
                $xWatermark = $this->larguraFinal - $larguraFinalWatermark;
                $yWatermark = 0;
                break;

            case 4:
                $xWatermark = 0;
                $yWatermark = ($this->alturaFinal - $alturaFinalWatermark) / 2;
                break;

            case 5:
            default:
                $xWatermark = ($this->larguraFinal - $larguraFinalWatermark) / 2;
                $yWatermark = ($this->alturaFinal - $alturaFinalWatermark) / 2;
                break;

            case 6:
                $xWatermark = $this->larguraFinal - $larguraFinalWatermark;
                $yWatermark = ($this->alturaFinal - $alturaFinalWatermark) / 2;
                break;

            case 7:
                $xWatermark = 0;
                $yWatermark = $this->alturaFinal - $alturaFinalWatermark;
                break;

            case 8:
                $xWatermark = ($this->larguraFinal - $larguraFinalWatermark) / 2;
                $yWatermark = $this->alturaFinal - $alturaFinalWatermark;
                break;

            case 9:
                $xWatermark = $this->larguraFinal - $larguraFinalWatermark;
                $yWatermark = $this->alturaFinal - $alturaFinalWatermark;
        }

        if ($this->mantemTransparencia) {
            imagealphablending($this->imagemFinal,true);
            imagesavealpha($this->imagemFinal,true);
        }

        imagecopyresampled($this->imagemFinal,$imagemWatermark,$xWatermark,$yWatermark,0,0,$larguraFinalWatermark,$alturaFinalWatermark,$larguraOriginalWatermark,$alturaOriginalWatermark);

        if ((($this->tipo == IMAGETYPE_PNG) || ($this->tipo == IMAGETYPE_GIF)) && ($this->mantemTransparencia)) {
            if (!$this->imagemFinal) $this->imagemFinal = $this->imagemOriginal;
            return 1;
        }

        $this->calculaQualidade();
        return 1;
    }

    public function salvar($arquivoDestino) {
        if (!$this->imagemFinal) {
            return copy($this->arquivoOrigem,$arquivoDestino);
        }

        if ($this->mantemTransparencia) {
            switch ($this->tipo) {
                case IMAGETYPE_PNG:
                    imagepng($this->imagemFinal,$arquivoDestino,9);
                    return true;
                    break;

                case IMAGETYPE_GIF:
                    imagegif($this->imagemFinal,$arquivoDestino);
                    return true;
                    break;
            }
        }

        imagejpeg($this->imagemFinal,$arquivoDestino,$this->qualidade);
        return true;
    }

    public function salvarMiniatura($arquivoDestino,$larguraMiniatura,$alturaMiniatura) {
        if (!$this->abreImg()) return false;

        if ($this->larguraOriginal > $this->alturaOriginal) {
            $larguraFinal = $larguraMiniatura;
            $alturaFinal = ($larguraMiniatura * $this->alturaOriginal) / $this->larguraOriginal;
            if ($alturaFinal < $alturaMiniatura) {
                $alturaFinal = $alturaMiniatura;
                $larguraFinal = ($alturaMiniatura * $this->larguraOriginal) / $this->alturaOriginal;
            }
        }
        else {
            $alturaFinal = $alturaMiniatura;
            $larguraFinal = ($alturaMiniatura * $this->larguraOriginal) / $this->alturaOriginal;
            if ($larguraFinal < $larguraMiniatura) {
                $larguraFinal = $larguraMiniatura;
                $alturaFinal = ($larguraMiniatura * $this->alturaOriginal) / $this->larguraOriginal;
            }
        }

        $imagemFinal = imagecreatetruecolor($larguraFinal,$alturaFinal);
        if ($this->mantemTransparencia) {
            imagealphablending($imagemFinal,false);
            imagesavealpha($imagemFinal,true);
            imagecolortransparent($imagemFinal, imagecolorallocatealpha($imagemFinal,0,0,0,127));
        }
        else {
            imagefill($imagemFinal,0,0,imagecolorallocate($imagemFinal,255,255,255));
        }
        imagecopyresampled($imagemFinal,$this->imagemOriginal,0,0,0,0,$larguraFinal,$alturaFinal,$this->larguraOriginal,$this->alturaOriginal);
        $imagemFinal = imagecrop($imagemFinal,array(
            'x' => ($larguraFinal-$larguraMiniatura)/2,
            'y' => ($alturaFinal-$alturaMiniatura)/2,
            'width' => $larguraMiniatura,
            'height' => $alturaMiniatura
        ));

        if ($this->mantemTransparencia) {
            switch ($this->tipo) {
                case IMAGETYPE_PNG:
                    imagepng($imagemFinal,$arquivoDestino,9);
                    return true;
                    break;

                case IMAGETYPE_GIF:
                    imagegif($imagemFinal,$arquivoDestino);
                    return true;
                    break;
            }
        }

        imagejpeg($imagemFinal,$arquivoDestino,75);
        return true;
    }

    public function pegarMiniatura($larguraMiniatura,$alturaMiniatura)
    {
        $caminhoTemporario = tempnam(sys_get_temp_dir(), 'img');
        $this->salvarMiniatura($caminhoTemporario, $larguraMiniatura, $alturaMiniatura);
        if (file_exists($caminhoTemporario))
        {
            $conteudoMiniatura = file_get_contents($caminhoTemporario);
            unlink($caminhoTemporario);
            return $conteudoMiniatura;
        }
        return false;
    }
}
