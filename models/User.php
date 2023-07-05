<?php

class User extends Validation
{
    //DB stuff
    private $conn;
    private $table = 'users';

    //User Properties
    public $id;
    public $login;
    public $password;
    public $confirm_password;
    public $created_at;

    //Constructor with DB
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get Posts
    public function read()
    {
        //Create query
        $query = 'SELECT 
               p.id,
               p.login,
               p.password,
               p.created_at
                    FROM
                ' . $this->table . ' p
                    ORDER BY
                p.created_at DESC';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Execute query
        $stmt->execute();

        return $stmt;
    }

    //Get Single User
    public function read_single()
    {
        //Create query
        $query = 'SELECT 
               p.id,
               p.login,
               p.password,
               p.created_at
                    FROM
                ' . $this->table . ' p
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
        $this->login = $row['login'];
        $this->password = $row['password'];
    }

    //Create User
    public function create()
    {
        $this->login = htmlspecialchars(strip_tags($this->login));
        $this->password = md5(htmlspecialchars(strip_tags($this->password)));
        $this->confirm_password = md5(htmlspecialchars(strip_tags($this->confirm_password)));

//    parolni tekshirish confirm bilan
        if ($this->password == $this->confirm_password) {
            // Login ni tekshirish
            $existingUser = $this->conn->prepare("SELECT login FROM users WHERE login = ?");
            $existingUser->execute([$this->login]);

            if ($existingUser->rowCount() > 0) {
                // Login bazada mavjud
                return false;
            } else {
                // Ma'lumotlarni bazaga yozish
                try {
                    $stmt = $this->conn->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
                    $stmt->execute([$this->login, $this->password]);

                    // Ma'lumotlar muvaffaqiyatli saqlandi
                    return true;
                } catch (PDOException $e) {
                    return false;
                }
            }
        } else {
            return false;
        }

    }

    //Update User
    public function update()
    {
        //Create query
        $query = 'UPDATE ' .
            $this->table . '
        SET
            login=:login,
            password=:password,
        WHERE
            id=:id';

        //Prepare statement
        $stmt = $this->conn->prepare($query);

        //Clean data
        $this->login = htmlspecialchars(strip_tags($this->login));
        $this->password = htmlspecialchars(strip_tags($this->password));


        //Bind data
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':password', $this->password);

        //Execute query
        if ($stmt->execute()) {
            return true;
        }

        //Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    //Delete user
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