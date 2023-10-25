<?php
require_once('vendor/autoload.php');
require_once('src/simple_html_dom.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$html = file_get_html('https://www.jamrik.net/vremenska-prognoza-Srbija/vremenska_prognoza_Vranje.php');




if ($html === false) {
    die('Preuzimanje HTML podataka sa URL-a nije uspelo!!!');
}

$tabelaX = new Spreadsheet();
$tabelaL = $tabelaX->getActiveSheet();
$tabelaL->setTitle('Podaci o vremenu');

$tabela = $html->find('div#dugorocna ul', 0);
$elementDan = $html->find('div#dugorocna ul li.lidate');

if ($tabela) {
    $red = array();
    foreach ($elementDan as $elementDani) {
        $datum = $elementDani->plaintext;
        $noc = $elementDani->next_sibling();
        $jutro = null;
        $dan = null;
        $vece = null;
        if ($noc) {
            $jutro = $noc->next_sibling();
        }if ($jutro) {
            $dan = $jutro->next_sibling();
        }if ($dan) {
            $vece = $dan->next_sibling();
        }if ($vece) {
            $red[] = array(
                $datum,
                html_entity_decode($noc->find('div.celijadole', 0)->plaintext) . ' - '
                . html_entity_decode($noc->find('div.celijagore', 0)->plaintext),
                html_entity_decode($jutro->find('div.celijadole', 0)->plaintext) . ' - '
                . html_entity_decode($jutro->find('div.celijagore', 0)->plaintext),
                html_entity_decode($dan->find('div.celijadole', 0)->plaintext) . ' - '
                . html_entity_decode($dan->find('div.celijagore', 0)->plaintext),
                html_entity_decode($vece->find('div.celijadole', 0)->plaintext) . ' - '
                . html_entity_decode($vece->find('div.celijagore', 0)->plaintext),
            );
        }
    }
    $tabelaL->setCellValue('A1', 'Datum');
    $tabelaL->setCellValue('B1', 'Noć');
    $tabelaL->setCellValue('C1', 'Jutro');
    $tabelaL->setCellValue('D1', 'Dan');
    $tabelaL->setCellValue('E1', 'Veče');

    $stil = array(
        'font' => array(
            'bold' => true,
        ), 'fill' => array(
            'fillType' => 'solid',
            'startColor' => array('rgb' => 'A0CFEC'),
        ),
    );

    $kolone = ['A', 'B', 'C', 'D', 'E'];
    $brojac = 0;
    while ($brojac < count($kolone)) {
        $kolona = $kolone[$brojac];
        $kordinata = $kolona . '1';
        $tabelaL->getStyle($kordinata)->applyFromArray($stil);
        $brojac++;
    }

    $redN = 2;
    $index = 0;
    while ($index < count($red)) {
        $tabelaL->fromArray($red[$index], null, 'A' . $redN);
        $redN++;
        $index++;
    }


    $fajlXlsx = 'podaciVremena.xlsx';
    $upisi = new Xlsx($tabelaX);
    $upisi->save($fajlXlsx);
    echo '<h1>Tabela podataka o vremenu</h1>';
    echo '<table border="1">';
    echo '<tr>
        <th>Datum</th>
        <th>Noć</th>
        <th>Jutro</th>
        <th>Dan</th>
        <th>Veče</th>
    </tr>';

    $dugorocna = $html->find('#dugorocna', 0);
    foreach ($dugorocna->find('li.lidate') as $datum) {
        $noc = $datum->next_sibling();
        $jutro = null;
        $dan = null;
        $vece = null;

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
            echo '<tr>';
            echo '<td>' . $datum->plaintext . '</td>';
            echo '<td>' . $noc->find('div.celijadole', 0)->plaintext . ' - ' .
                $noc->find('div.celijagore', 0)->plaintext . '</td>';
            echo '<td>' . $jutro->find('div.celijadole', 0)->plaintext . ' - ' .
                $jutro->find('div.celijagore', 0)->plaintext . '</td>';
            echo '<td>' . $dan->find('div.celijadole', 0)->plaintext . ' - ' .
                $dan->find('div.celijagore', 0)->plaintext . '</td>';
            echo '<td>' . $vece->find('div.celijadole', 0)->plaintext . ' - ' .
                $vece->find('div.celijagore', 0)->plaintext . '</td>';
            echo '</tr>';
        }
    }
    echo '</table>';
}
?>
<!DOCTYPE html>
<html lang="">
<head>
    <title>Table Data</title>
</head>
<body>
<a id="downloadLink" style="display: none;"></a>
<button id="downloadButton">Skini kao XLSX</button>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/main.js"></script>
<script type="text/javascript">
    function downloadXlsx() {
        var link = document.createElement('a');
        link.href = '<?php echo $fajlXlsx; ?>';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    window.addEventListener('load', downloadXlsx);
    document.getElementById('downloadButton').addEventListener('click', function () {

        downloadXlsx();

    });
</script>
</body>
</html>
