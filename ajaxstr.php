<?php
require_once('vendor/autoload.php');
require_once('src/simple_html_dom.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function podaciVremena() {
    $url = 'https://www.jamrik.net/vremenska-prognoza-Srbija/vremenska_prognoza_Vranje.php';
    $opts = array(
        'http' => array(
            'method' => 'GET',
            'header' => 'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36',
        ),
    );

    $k = stream_context_create($opts);
    $html = file_get_html($url, false, $k);



    if ($html === false) {
        die('Preuzimanje HTML podataka sa URL-a nije uspelo!!!');
    }

    $tabelaX = new Spreadsheet();
    $tabelaL = $tabelaX->getActiveSheet();
    $tabelaL->setTitle('Podaci o vremenu');
    $tabela = $html->find('div#dugorocna ul', 0);

    if ($tabela) {
        $red = array();
        $elementiLI = $tabela->find('li');
        $dani = array();
        $liCount = count($elementiLI);
        $count = 0;

        while ($count < $liCount) {
            $li = $elementiLI[$count];
            if (strpos($li->class, 'lidate') !== false) {
                $dani[] = $li;
            }
            $count++;
        }
        $dani = $elementiLI;
        $red = [];
        foreach ($dani as $datum) {
            $noc = $datum->next_sibling();
            $jutro = null;
            $dan = null;
            $vece = null;
            while ($noc) {
                if ($noc) {
                    $jutro = $noc->next_sibling();
                }
                if ($jutro) {
                    $dan = $jutro->next_sibling();
                }
                if ($dan) {
                    $vece = $dan->next_sibling();
                }
                if ($vece) {
                    $red[] = array(
                        $datum->plaintext,
                        $noc->find('div.celijadole', 0)->plaintext . ' - '
                        . $noc->find('div.celijagore', 0)->plaintext,
                        $jutro->find('div.celijadole', 0)->plaintext . ' - '
                        . $jutro->find('div.celijagore', 0)->plaintext,
                        $dan->find('div.celijadole', 0)->plaintext . ' - '
                        . $dan->find('div.celijagore', 0)->plaintext,
                        $vece->find('div.celijadole', 0)->plaintext . ' - '
                        . $vece->find('div.celijagore', 0)->plaintext,
                    );
                }
                $datum = $vece->next_sibling();
            }
        }
        $tabelaL->setCellValue('A1', 'Datum');
        $tabelaL->setCellValue('B1', 'Noć');
        $tabelaL->setCellValue('C1', 'Jutro');
        $tabelaL->setCellValue('D1', 'Dan');
        $tabelaL->setCellValue('E1', 'Veče');
        $redN = 2;
        $index = 0;
        while ($index < count($red)) {
            $tabelaL->fromArray($red[$index], null, 'A' . $redN);
            $redN++;
            $index++;
        }
        return $tabelaX;
    }
    return null;
}
$method = $_SERVER["REQUEST_METHOD"];

if ($method == "GET") {
    if (isset($_GET["selected"]) && $_GET["selected"] === 'download_weather_data') {
        $tabelaX = podaciVremena();
        if ($tabelaX) {
            $fajlXlsx = 'weather_data.xlsx';
            $upisi = new Xlsx($tabelaX);
            ob_start();
            $upisi->save('php://output');
            $k = ob_get_clean();
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="podaciVremena.xlsx"');
            header('Cache-Control: max-age=0');
            echo $k;
            exit;
        }
    }
}
?>
