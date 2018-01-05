<?php


require_once('../vendor/autoload.php');
$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('template.xlsx');

$worksheet = $spreadsheet->getActiveSheet();

$filas = $worksheet->toArray();

$nuevo_valor = array('%CAMPO1%' => 'Valor de campo 1', '%CAMPO2%' => 'Valor de campo 2');

echo '<pre>';
//var_dump($filas);
foreach($filas as $x => $fila){
    foreach($fila as $y => $celda){
        if (!empty($celda)) {
            echo "[$x, $y: $celda]";
            if (preg_match('/\%.+\%/', $celda)){
                echo "XXX";
                //$worksheet->setCellValueByColumnAndRow($x+1, $y+1, $nuevo_valor[$celda]);
                $worksheet->setCellValueByColumnAndRow($y+1, $x+1, $nuevo_valor[$celda]);
            }
        }
    }
}

//$worksheet->getCell('A1')->setValue('John');
//$worksheet->getCell('A2')->setValue('Smith');

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
$writer->save('write.xls');

echo "<a href='write.xls'>write.xls</a>";
