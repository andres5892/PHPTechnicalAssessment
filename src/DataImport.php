<?php

class DataImport
{
    /**
     * @var string
     */
    private $root = "../temp/customers-info.csv";

    public function getCsvData(){
        $fila = 1;
        if (($gestor = fopen("test.csv", "r")) !== FALSE) {
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                $numero = count($datos);
                echo "<p> $numero de campos en la línea $fila: <br /></p>\n";
                $fila++;
                for ($c=0; $c < $numero; $c++) {
                    echo $datos[$c] . "<br />\n";
                }
            }
            fclose($gestor);
        }
    }
    
    

}