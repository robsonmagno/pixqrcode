<?php

declare(strict_types=1);

namespace Rmagnoprado\Pixqrcode;

class Payload
{
    /**
     * Biblioteca de funções para geração da linha do Pix copia e cola
     * cujo texto é utilizado para a geração do QRCode para recebimento
     * de pagamentos através do Pix do Banco Central.
     * Desenvolvido em 2020 por Renato Monteiro Batista - http://renato.ovh
     * Este código pode ser copiado, modificado, redistribuído
     * inclusive comercialmente desde que mantida a refereência ao autor.
     */

    /**
     * Esta rotina monta o código do pix conforme o padrão EMV
     * Todas as linhas são compostas por [ID do campo][Tamanho do campo com dois dígitos][Conteúdo do campo]
     * Caso o campo possua filhos esta função age de maneira recursiva.
     * Autor: Eng. Renato Monteiro Batista
     *
     * @param array<int, array<int, string>|string> $px
     *
     * @return string $ret
     */
    public function montaPix(array $px): string
    {
        $ret = '';
        foreach ($px as $k => $v) {
            if (! is_array($v)) {
                // Formata o campo valor com 2 digitos.
                if ($k === 54) {
                    $v = number_format((float) $v, 2, '.', '');
                } else {
                    $v = $this->remove_char_especiais($v);
                }
                $ret .= $this->c2((string) $k).$this->cpm($v).$v;
            } else {
                $conteudo = $this->montaPix($v);
                $ret .= $this->c2((string) $k).$this->cpm($conteudo).$conteudo;
            }
        }

        # A função montaPix prepara todos os campos existentes antes do CRC (campo 63).
        # O CRC deve ser calculado em cima de todo o conteúdo, inclusive do próprio 63.
        # O CRC tem 4 dígitos, então o campo será um 6304.
        $ret .= '6304';
        //Adiciona o campo do CRC no fim da linha do pix.
        $ret .= $this->crcChecksum($ret);
        //Calcula o checksum CRC16 e acrescenta ao final.

        return $ret;
    }

    private function remove_char_especiais(string $txt): string
    {
        /*
        # Esta função retorna somente os caracteres alfanuméricos (a-z,A-Z,0-9) de uma string.
        # Caracteres acentuados são convertidos pelos equivalentes sem acentos.
        # Emojis são removidos, mantém espaços em branco.
        #
        # Autor: Eng. Renato Monteiro Batista
        */
        return preg_replace('/\W /', '', $this->remove_acentos($txt));
    }

    private function remove_acentos(string $texto): string
    {
        /*
        # Esta função retorna uma string substituindo os caracteres especiais de acentuação
        # pelos respectivos caracteres não acentuados em português-br.
        #
        # Autor: Eng. Renato Monteiro Batista
        */
        $search = explode(',', 'à,á,â,ä,æ,ã,å,ā,ç,ć,č,è,é,ê,ë,ē,ė,ę,î,ï,í,ī,į,ì,ł,ñ,ń,ô,ö,ò,ó,œ,ø,ō,õ,ß,ś,š,û,ü,ù,ú,ū,ÿ,ž,ź,ż,À,Á,Â,Ä,Æ,Ã,Å,Ā,Ç,Ć,Č,È,É,Ê,Ë,Ē,Ė,Ę,Î,Ï,Í,Ī,Į,Ì,Ł,Ñ,Ń,Ô,Ö,Ò,Ó,Œ,Ø,Ō,Õ,Ś,Š,Û,Ü,Ù,Ú,Ū,Ÿ,Ž,Ź,Ż');
        $replace = explode(',', 'a,a,a,a,a,a,a,a,c,c,c,e,e,e,e,e,e,e,i,i,i,i,i,i,l,n,n,o,o,o,o,o,o,o,o,s,s,s,u,u,u,u,u,y,z,z,z,A,A,A,A,A,A,A,A,C,C,C,E,E,E,E,E,E,E,I,I,I,I,I,I,L,N,N,O,O,O,O,O,O,O,O,S,S,U,U,U,U,U,Y,Z,Z,Z');
        return $this->remove_emoji(str_replace($search, $replace, $texto));
    }

    private function remove_emoji(string $string): string
    {
        /*
        # Esta função retorna o conteúdo de uma string removendo oas caracteres especiais
        # usados para representação de emojis.
        #
        */
        return preg_replace('%(?:
        \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
        )%xs', '  ', $string);
    }

    private function cpm(string $tx): string
    {
        /*
        # Esta função auxiliar retorna a quantidade de caracteres do texto $tx com dois dígitos.
        #
        # Autor: Renato Monteiro Batista
        */
        if (strlen($tx) > 99) {
            die("Tamanho máximo deve ser 99, inválido: {$tx} possui " . strlen($tx) . ' caracteres.');
        }
        /*
        Não aprecio o uso de die no código, é um tanto deselegante pois envolve matar.
        Mas considerando que 99 realmente é o tamanho máximo aceitável, estou adotando-o.
        Mas aconselho que essa verificação seja feita em outras etapas do código.
        Caso não tenha entendido a problemática consulte  a página 4 do Manual de Padrões para Iniciação do Pix.
        Ou a issue 4 deste projeto: https://github.com/renatomb/php_qrcode_pix/issues/4
        */
        return $this->c2((string) strlen($tx));
    }

    private function c2(string $input): string
    {
        /*
        # Esta função auxiliar trata os casos onde o tamanho do campo for < 10 acrescentando o
        # dígito 0 a esquerda.
        #
        # Autor: Renato Monteiro Batista
        */
        return str_pad($input, 2, '0', STR_PAD_LEFT);
    }

    /*
    # Esta função auxiliar calcula o CRC-16/CCITT-FALSE
    #
    # Autor: evilReiko (https://stackoverflow.com/users/134824/evilreiko)
    # Postada originalmente em: https://stackoverflow.com/questions/30035582/how-to-calculate-crc16-ccitt-in-php-hex
    */
    // The PHP version of the JS str.charCodeAt(i)
    private function charCodeAt(string $str, int $i): string
    {
        return (string) ord(substr($str, $i, 1));
    }

    private function crcChecksum(string $str): string
    {
        $crc = 0xFFFF;
        $strlen = strlen($str);
        for ($c = 0; $c < $strlen; $c++) {
            $crc ^= (int) $this->charCodeAt($str, $c) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }
        $hex = $crc & 0xFFFF;
        $hex = dechex($hex);
        $hex = strtoupper($hex);
        $hex = str_pad($hex, 4, '0', STR_PAD_LEFT);
        return $hex;
    }
}
