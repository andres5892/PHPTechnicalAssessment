<?php

require_once 'Connection.php';

class DataImport
{
    private $argv;

    private $connection;

    private $headers = ['first_name', 'last_name', 'email', 'password', 'birthday', 'country', 'region',
        'city', 'postal_code', 'street_suffix', 'street', 'street_number', 'telephone'];

    public function __construct($argv)
    {
        $this->argv = $argv;
        $this->connection = new Connection();
    }

    /**
     * @return void
     */
    public function getCsvData()
    {
        if (count($this->argv) <= 1) {
            die("Missing argument (e.g. php -f src/DataImport.php temp/customers-info.csv)");
        }
        if (($file = fopen($this->argv[1], "r")) !== FALSE) {
            $headers = fgetcsv($file, null, ',');
            $headers[0] = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $headers[0]);
            if ($this->headers !== $headers) {
                die("Error: header order, number of columns, and names are strict (" . implode($this->headers, ',') . ")");
            }
            $count = 1;
            while (($row = fgetcsv($file, null, ",")) !== FALSE) {
                $count++;
                if (count($row) !== count($headers)) {
                    die("Row $count has fewer columns than expected.");
                }
                $data = array_combine($headers, $row);
                if (!$this->emailValidator($data['email'])) {
                    die("Row $count has not a valid email");
                }
                if (!$this->dateValidator($data['birthday'])) {
                    die("Row $count has not a valid birthday");
                }
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

                $this->insertToDatabase($data);

                var_dump($data);

                die;
            }
            fclose($file);
        }
    }

    /**
     * @param $email
     * @return mixed
     */
    private function emailValidator($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * @param $date
     * @return bool
     */
    private function dateValidator($date): bool
    {
        $dateTime = DateTime::createFromFormat('m/d/Y', $date);
        return $dateTime && $dateTime->format('m/d/Y') == $date;
    }

    private function insertToDatabase($data)
    {
        var_dump($this->connection->connect());die;
    }
}

$dataImport = new DataImport($argv);
$dataImport->getCsvData();