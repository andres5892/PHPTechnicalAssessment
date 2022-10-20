<?php

require_once 'vendor/autoload.php';

class Connection
{
    /**
     * @var string
     */
    private $envPath = __DIR__ . '/..';

    /**
     * @var string
     */
    private $dsn;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * Connection constructor
     */
    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable($this->envPath);
        $dotenv->load();
        
        $this->dsn = $_ENV['DBSERVER'];
        $this->username = $_ENV['DBUSER'];
        $this->password = $_ENV['DBPASS'];
    }

    /**
     * @return \PDO
     */
    public function connect()
    {
        try
        {
            return new \PDO($this->dsn, $this->username, $this->password,[\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', \PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION]);
        }
        catch (\PDOException $exception)
        {
            die("Error conectando al servidor: " . $exception->getMessage());
        }
    }
}
