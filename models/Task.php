<?php

class Task
{
    //DB stuff
    private $conn;
    private $table = 'tasks';

    //Task Properties
    public $id;
    public $user_id;
    public $name;
    public $point;
    public $created_at;

    //Constructor with DB
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get Task
    public function read()
    {
        //Create query
        $query = 'SELECT 
               c.name AS user_name,
               p.id,
               p.user_id,
               p.name,
               p.point,
               p.created_at
                    FROM
                ' . $this->table . ' p
                    LEFT JOIN 
                users c ON p.user_id=c.id
                    ORDER BY
                p.created_at DESC';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Execute query
        $stmt->execute();

        return $stmt;
    }

    //Get Single Task
    public function read_single()
    {
        //Create query
        $query = 'SELECT 
               c.name AS user_name,
               p.id,
               p.user_id,
               p.name,
               p.point,
               p.created_at
                    FROM
                ' . $this->table . ' p
                    LEFT JOIN 
                users c ON p.user_id=c.id
                    WHERE
                p.id = ?
                LIMIT 0,1';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Bind ID
        $stmt->bindParam(1, $this->id);

        //Execute query
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        //Set properties
        $this->name = $row['name'];
        $this->point = $row['point'];
        $this->user_id = $row['user_id'];
        $this->user_name = $row['user_name'];
    }

    //Create Task
    public function create()
    {
        //Create query
        $query = 'INSERT INTO ' .
            $this->table . '
        SET
            name=:name,
            point=:point,
            user_id=:user_id';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Clean data
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->point = htmlspecialchars(strip_tags($this->point));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        //Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':point', $this->point);
        $stmt->bindParam(':user_id', $this->user_id);

        //Execute query
        if ($stmt->execute()) {
            return true;
        }

        //Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    //Delete Task
    public function delete()
    {
        //Create query
        $query = 'DELETE FROM ' . $this->table . ' WHERE id=:id';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Clean data
        $this->id = htmlspecialchars(strip_tags($this->id));

        //Bind data
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        //Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
}