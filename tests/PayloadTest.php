<?php
declare(strict_types=1);

use Rmagnoprado\Pixqrcode\Payload;

require 'vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Rmagnoprado\Debug\Main;

/** @PayloadTest */
class PayloadTest extends TestCase {

    public function test_montaPix() : void{

        $px[00]="01";
        $px[26][00]="BR.GOV.BCB.PIX"; //Indica arranjo específico; “00” (GUI) obrigatório e valor fixo: br.gov.bcb.pix
        $px[26][01]="42a57095-84f3-4a42-b9fb-d08935c86f47"; //Chave do destinatário do pix, pode ser EVP, e-mail, CPF ou CNPJ.
        $px[26][02]="Descricao"; // Descrição da transação, opcional.
        $px[52]="0000"; //Merchant Category Code “0000” ou MCC ISO18245
        $px[53]="986"; //Moeda, “986” = BRL: real brasileiro - ISO4217
        $px[54]="10.00"; //Valor da transação, se comentado o cliente especifica o valor da transação no próprio app. Utilizar o . como separador decimal. Máximo: 13 caracteres.
        $px[58]="BR"; //“BR” – Código de país ISO3166-1 alpha 2
        $px[59]="RENATO MONTEIRO BATISTA"; //Nome do beneficiário/recebedor. Máximo: 25 caracteres.
        $px[60]="NATAL"; //Nome cidade onde é efetuada a transação. Máximo 15 caracteres.
        $px[62][05]="***"; //Identificador de transação, quando gerado automaticamente usar ***. Limite 25 caracteres. Vide nota abaixo.
        /*
        O campo 62/50 é um campo facultativo, que indica a versão do arranjo de pagamentos que está sendo usada.
        $px[62][50][00]="BR.GOV.BCB.BRCODE"; //Payment system specific template - GUI
        $px[62][50][01]="1.0.0"; //Payment system specific template - versão
        */
        //Caso queira visualizar a matriz dos dados que serão montados no pix descomente a linha a seguir.
        //print_r($px);

        $obPayload = new Payload();

        $this->assertIsString(
            $obPayload->montaPix($px)
        );

        /*
        $obQrCode = new QrCode($pix);
        $image = (new Output\Png)->output($obQrCode,400);
        */

    }
}