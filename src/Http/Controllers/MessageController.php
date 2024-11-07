<?php
namespace App\Http\Controllers;

use App\Constants\HttpCode;
use App\Core\Dal\DatabaseHandler;

class MessageController
{
    public function __construct(private DatabaseHandler $db) {
        
    }

    public function index() {
        $rows = $this->db->query("SELECT * FROM message");
        return response()->json($rows);
    }

    public function store() {
        $rows = $this->db->query("SELECT COUNT(*) as count FROM message");
        $row = $rows[0];
        $count = $row['count'];
        $success = $this->db->execute("
            INSERT INTO message (message)
            SELECT CONCAT('message-', '$count')
            WHERE NOT EXISTS (
            SELECT 1 FROM message WHERE message = CONCAT('message-', '$count')
            )
        ");

        return $success
            ? response()->make()->statusCode(HttpCode::CREATED)
            : response()->err(HttpCode::CONFLICT);
    }
}
