USE mydb;

-- CreateTable
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL
);

-- Seed
INSERT INTO mydb.messages
    (message)
VALUES 
    ('message-0'),
    ('message-1'),
    ('message-2');