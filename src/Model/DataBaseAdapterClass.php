<?php


namespace App\Model;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use function getenv;

class DataBaseAdapterClass
{
  private $dbParamsArray = null, $dbConfig = null, $dbConnection = null;

  //public function __construct($dbName = null, $dbHost = null, $dbPort = null)
  public function setConnection($dbName = null, $dbHost = null, $dbPort = null)
  {
    $this->dbConfig = new Configuration();

    if( !$dbName || !$dbHost)
    {
      // A "\" before the beginning of a function represents the Global Namespace
      $db_url = getenv('DATABASE_URL');
      $this->dbParamsArray = array('url' => $db_url);
    }
    else
    {
      $this->dbParamsArray = array(
        'user' => 'postgres',
        'password' => 'postgres',
        'driver' => 'pdo_pgsql',
        'dbname' => $dbName,
        'host' => $dbHost,
        'port' => ($dbPort ?: '5432')
      );
    }

    try
    {
      $this->dbConnection = DriverManager::getConnection($this->dbParamsArray, $this->dbConfig);
    }
    catch (DBALException $e)
    {
      return false;
    }
    return true;
  }

  /**
   * @return Connection|null
   */
  public function getDbConnection(): ?Connection
  {
    return $this->dbConnection;
  }

}