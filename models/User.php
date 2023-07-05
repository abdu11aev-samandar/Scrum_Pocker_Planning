<?php

class User extends Validation
{
    // DB stuff
    private $conn;
    private $table = 'users';

    // User Properties
    public $id;
    public $login;
    public $email;
    public $password;
    public $confirm_password;

    // Constructor with DB
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get Users
    public function read()
    {
        // Create query
        $query = 'SELECT 
               id,
               login,
               password,
               email,
               created_at
            FROM
               ' . $this->table . '
            ORDER BY
               created_at DESC';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Execute query
        $stmt->execute();

        return $stmt;
    }

    // Get Single User
    public function read_single()
    {
        // Create query
        $query = 'SELECT 
               id,
               login,
               password,
               email,
               created_at
            FROM
               ' . $this->table . '
            WHERE
               id = ?
            LIMIT 1';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Bind ID
        $stmt->bindParam(1, $this->id);

        // Execute query
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set properties
        $this->login = $row['login'];
        $this->password = $row['password'];
    }

    // Create User
    public function create()
    {
        $this->login = htmlspecialchars(strip_tags($this->login));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->confirm_password = htmlspecialchars(strip_tags($this->confirm_password));

        // Password confirmation check
        if ($this->password !== $this->confirm_password) {
            return false;
        }

        // Check if login already exists
        $existingUser = $this->conn->prepare("SELECT login FROM users WHERE login = ?");
        $existingUser->execute([$this->login]);

        if ($existingUser->rowCount() > 0) {
            // Login already exists in the database
            return false;
        }

        // Hash the password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        // Insert data into database
        try {
            $stmt = $this->conn->prepare("INSERT INTO users (login, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$this->login, $hashed_password, $this->email]);

            // Data successfully inserted
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Update User
    public function update()
    {
        // Create query
        $query = 'UPDATE ' .
            $this->table . '
            SET
            login = :login,
            password = :password,
            email = :email
            WHERE
            id = :id';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->login = htmlspecialchars(strip_tags($this->login));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Bind data
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':email', $this->email);

        // Execute query
        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }

    // Delete user
    public function delete()
    {
        // Create query
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';

        // Prepare statement
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind data
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }

        // Print error if something goes wrong
        printf("Error: %s.\n", $stmt->error);

        return false;
    }
}
