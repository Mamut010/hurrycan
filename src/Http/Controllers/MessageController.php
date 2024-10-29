<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Dal\DatabaseHandler;

class MessageController
{
    public function __construct(private DatabaseHandler $db) {
        
    }

    public function index() {
        $rows = $this->db->queryAll("SELECT * FROM messages");
        return response()->json($rows);
    }

    public function store() {
        $rows = $this->db->queryAll("SELECT COUNT(*) as count FROM messages");
        $row = $rows[0];
        $count = $row['count'];
        $this->db->execute("
            INSERT INTO messages (message)
            SELECT CONCAT('message-', '$count')
            WHERE NOT EXISTS (
            SELECT 1 FROM messages WHERE message = CONCAT('message-', '$count')
            )
        ");

        return response()->make()->statusCode(HttpCode::CREATED);
    }
}
