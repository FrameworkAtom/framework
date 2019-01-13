<?php

namespace Atom\Database;

require_once __DIR__ . '/../Helpers/Misc.php';


use Atom\Exceptions\ModelException;
use \PDO;

/**
 * Database Connection Class.
 */
class Database {

    /**
     * @var $db PDO
     */
    private static $db;

    /**
     * Get an instance of the database connection.
     *
     * @return PDO
     */
    public static function GetDB()
    {
        if (!isset(self::$db))
            self::$db = new PDO('mysql:host=' . env('database')->host . ':' . env('database')->port . '; dbname=' . env('database')->db_name . '; charset=utf8', env('database')->user, env('database')->password);

        return self::$db;
    }

}

class Model
{

    /**
     * Database table name.
     *
     * @var string|null
     */
    protected $table_name = null;

    /**
     * Database primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Database have timestamps.
     *
     * @var bool
     */
    protected $timestamps = false;

    /**
     * Model instance.
     *
     * @var Model|null
     */
    protected static $_instance = null;

    /**
     * Create a new instance of the Database.
     *
     * @param array $datas
     * @return void
     */
    public function __construct(array $datas = [])
    {
        if ($this->table_name == null) {
            $table = explode('\\', get_called_class());
            $table_name = strtolower($table[count($table) - 1]);

            if (endsWith($table_name, 'y'))
                $this->table_name = substr($table_name, 0, strlen($table_name) - 1) . 'ies';
            else
                $this->table_name = $table_name . 's';
        }

        foreach ($datas as $key => $value)
            $this->$key = $value;

        static::$_instance = $this;
    }

    /**
     * Get Model instance.
     *
     * @return Model
     */
    public static function instance()
    {
        if (is_null(static::$_instance))
            static::$_instance = new Model();

        return static::$_instance;
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void;
     */
    public function set(string $key, $value)
    {
        $this->$key = $value;
    }

    /**
     * Get the model datas.
     *
     * @return array
     */
    public function datas(): array
    {
        return get_object_vars($this);
    }

    /**
     * Get the formatted model datas.
     *
     * @param string $exclude Key to exclude from the datas
     * @return array
     */
    public function object_datas(string $exclude = null): array
    {
        $datas = $this->datas();

        foreach ($datas as $k => $v) {
            if ($k == "primaryKey" || $k == "table_name" || $k == "timestamps" || $k == $exclude)
                unset($datas[$k]);
        }

        return $datas;
    }

    /**
     * Get all items of the current model in the database.
     *
     * @param string|null $where
     * @return array|bool
     */
    public static function all(string $where = null)
    {
        if ($where == null)
            $query = Database::GetDB()->prepare("SELECT * FROM ". self::tableName());
        else
            $query = Database::GetDB()->prepare("SELECT * FROM ". self::tableName() . " WHERE " . $where);

        if ($query->execute()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, get_called_class());
        }

        return false;
    }

    /**
     * Get model property with the given key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key;
    }

    /**
     * Get the given table structure.
     *
     * @param string $table
     * @return array|bool
     */
    private function getStructure(string $table)
    {
        $query = Database::GetDB()->prepare("SHOW COLUMNS FROM " . $table);

        if ($query->execute())
            return $query->fetchAll(\PDO::FETCH_OBJ);

        return false;
    }

    /**
     * Insert the current model in the database.
     *
     * @param array $datas
     * @return bool
     */
    public function save($datas = [])
    {
        $datas = filled($datas) ? $datas : $this->object_datas();

        foreach ($this->getStructure($this->table_name) as $structure) {
            if ($structure->Field == "created_at" || $structure->Field == "updated_at") {
                $this->timestamps = true;
            } else {
                $this->timestamps = false;
            }
        }

        $query = "INSERT INTO " . $this->table_name . "(";

        foreach ($datas as $k => $v) {
            $query .= $this->table_name . '.' . $k . ', ';
        }

        if ($this->timestamps)
            $query .= $this->table_name . '.created_at, ' . $this->table_name . '.updated_at) VALUES (';
        else
            $query = substr($query, 0, strlen($query) - 2) . ') VALUES (';


        foreach ($this->object_datas() as $data):
            if (is_string($data)) {
                if ($data == null || $data == '')
                    $query .= "NULL" . ',';
                else
                    $query .= '"' . $data . '", ';
            } else {
                if ($data == null || $data == '')
                    $query .= "NULL" . ',';
                else
                    $query .= $data . ', ';
            }
        endforeach;

        if ($this->timestamps)
            $query .= '"' . date('Y-m-d H:i:s') . '", "' . date('Y-m-d H:i:s') . '")';
        else
            $query = substr($query, 0, strlen($query) - 2) . ')';

        $statement = Database::GetDB()->prepare($query);

        $saved = $statement->execute();

        if ($saved) {
            foreach ($datas as $key => $value)
                $this->$key = $value;
        } else {
            throw new ModelException("Model can't be saved");
        }

        return $saved;
    }

    /**
     * Update the current model in the database.
     *
     * @param array $datas
     * @return bool
     */
    public function update($datas = [])
    {
        $datas = filled($datas) ? $datas : $this->object_datas('updated_at');

        foreach ($this->getStructure($this->table_name) as $structure) {
            if ($structure->Field == "created_at" || $structure->Field == "updated_at") {
                $this->timestamps = true;
            } else {
                $this->timestamps = false;
            }
        }

        $query = "UPDATE " . $this->table_name . " SET ";

        foreach ($datas as $k => $v) {
            if (is_string($v)) {
                if ($v == null || $v == '')
                    $query .= "NULL" . ", ";
                else
                    $query .= $this->table_name . "." . $k . " = \"" . $v . "\", ";
            } else {
                if ($v == null || $v == '')
                    $query .= $k . " = NULL" . ", ";
                else
                    $query .= $k . " = " . $v . ", ";
            }
        }

        if ($this->timestamps) {
            $query .= "updated_at = \"" . date('Y-m-d H:i:s') . "\" WHERE " . $this->primaryKey . " = " . $this->object_datas()[$this->primaryKey];
        } else {
            $query = substr($query, 0, strlen($query) - 2) . " WHERE " . $this->primaryKey . " = " . $this->object_datas()[$this->primaryKey];
        }

        $statement = Database::GetDB()->prepare($query);

        $updated = $statement->execute();

        if ($updated) {
            foreach ($datas as $key => $value)
                $this->$key = $value;
        } else {
            throw new ModelException("Model can't be updated");
        }
    }

    /**
     * Delete the current model in the database.
     *
     * @return bool
     */
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE " . $this->primaryKey . " = :id";
        $statement = Database::GetDB()->prepare($query);
        $statement->bindValue(':id', $this->datas()[$this->primaryKey]);

        return $statement->execute();
    }

    /**
     * Get model where the given primaryKey equals to the given value.
     *
     * @param string $primaryKey
     * @param mixed $value
     * @return Model|bool
     */
    public static function find($value)
    {
        $query = Database::GetDB()->prepare("SELECT * FROM ". self::tableName() ." WHERE ". Model::instance()->primaryKey ." = :val");
        $query->bindValue(':val', $value);

        if ($query->execute()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, get_called_class())[0];
        }

        return false;
    }

    /**
     * Get model where the given column equals to the given value.
     *
     * @param string $column
     * @param mixed $value
     * @return Model|bool
     */
    public static function where($column, $value)
    {
        $query = Database::GetDB()->prepare("SELECT * FROM ". self::tableName() ." WHERE ". $column ." = :val");
        $query->bindValue(':val', $value);

        if ($query->execute()) {
            return $query->fetchAll(\PDO::FETCH_CLASS, get_called_class())[0];
        }

        return false;
    }

    /**
     * Generate the current model table name.
     *
     * @return string
     */
    private static function tableName()
    {
        $table = explode('\\', get_called_class());
        $table_name = strtolower($table[count($table) - 1]);

        if (endsWith($table_name, 'y'))
            $table_name = substr($table_name, 0, strlen($table_name) - 1) . 'ies';
        else
            $table_name = $table_name . 's';

        return $table_name;
    }

}