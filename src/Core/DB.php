<?php
namespace App\Core;

use \Exception;
use \SQLite3;

class DB
{
    protected $filename;
    protected $driver;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function open()
    {
        $init = !file_exists($this->filename);

        if(!$this->driver)
        {
            $this->driver = new SQLite3($this->filename);
        }

        if($init)
        {
            $this->exec('CREATE TABLE users (id INTEGER PRIMARY KEY ASC, name TEXT, password TEXT)');
        }

        return $this;
    }

    protected function exec($query)
    {
        if(!$this->driver->exec($query))
        {
            throw new Exception($this->driver->lastErrorMsg());
        };
    }

    protected function querySingle($query, $entireRow = false)
    {
        $result = $this->driver->querySingle($query, $entireRow);
        if($result === false)
        {
            throw new Exception($this->driver->lastErrorMsg());
        };

        return $result;
    }

    public function checkUser($name, $password)
    {
        $md5 = md5($password);
        $userId = $this->open()->querySingle('SELECT id FROM users WHERE name = "'
            . $this->driver->escapeString($name) . '" AND password = "'
            . $this->driver->escapeString($md5) . '"');
        
        return (bool)$userId;
    }
}
