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
        $conn = $this->connection->connect();
        $conn->beginTransaction();
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
                    $conn->rollBack();
                    die("Row $count has fewer columns than expected.");
                }
                $data = array_combine($headers, $row);
                if (!$this->emailValidator($data['email'])) {
                    $conn->rollBack();
                    die("Row $count has not a valid email");
                }
                if (!$this->dateValidator($data['birthday'])) {
                    $conn->rollBack();
                    die("Row $count has not a valid birthday");
                }
                $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

                $countryId = $this->insertCountry($conn, $data);
                $addressId = $this->insertAddress($conn, $data, $countryId);
                $this->insertCustomer($conn, $data, $addressId);
            }
            fclose($file);
        }
        $conn->commit();
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

    /**
     * @param $conn
     * @param $data
     * @return mixed
     */
    private function insertCountry($conn, $data)
    {
        $stmt = $conn->prepare("SELECT id FROM Country WHERE country_name like ? LIMIT 1");
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute([$data['country']]);
        $id = $stmt->fetch()['id'];

        if (empty($id)) {
            $stmt = $conn->prepare("INSERT Country (country_name) VALUES (?)");
            $stmt->execute([$data['country']]);
            $id = $conn->lastInsertId();
        }

        return $id;
    }

    /**
     * @param $conn
     * @param $data
     * @param $countryId
     * @return mixed
     */
    private function insertAddress($conn, $data, $countryId)
    {
        $stmt = $conn->prepare("INSERT Address (street_suffix,street_name,street_number,
                                postal_code,region,city,country_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$data['street_suffix'], $data['street'], $data['street_number'],
                        $data['postal_code'], $data['region'], $data['city'], $countryId]);

        return $conn->lastInsertId();
    }

    /**
     * @param $conn
     * @param $data
     * @param $addressId
     * @return void
     */
    private function insertCustomer($conn, $data, $addressId)
    {
        $stmt = $conn->prepare("INSERT Customer (first_name,last_name,email,
                                password,birthday,telephone,address_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$data['first_name'], $data['last_name'], $data['email'], $data['password'],
                        $data['birthday'], $data['telephone'], $addressId]);
    }
}

$dataImport = new DataImport($argv);
$dataImport->getCsvData();
