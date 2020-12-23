<?php

namespace Cocorico\CoreBundle\Utils;

class SIAE
{
    public function __construct()
    {
        $csv_file = "../src/Cocorico/CoreBundle/Resources/config/test.csv";
        $list = [];
        $handle = fopen($csv_file, "r");
        $this->columns = fgetcsv($handle, 1000, "|");

        while (($data = fgetcsv($handle, 1000, "|")) !== FALSE) {
            $list[] = array_combine($this->columns, $data); 
        }
        fclose($handle);

        $this->list = $list;
        dump($this->columns);
        //$this->list = array_map('str_getcsv', file($csv_file));
    }

    public function get($length=10) {
        return array_slice($this->list, 0, $length);
    }

    public function columns() {
        return $this->columns;
    }


}
