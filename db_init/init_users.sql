CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    allocated_ip VARCHAR(50) NOT NULL,
    ftp_username VARCHAR(255) NOT NULL,
    ftp_password VARCHAR(255) NOT NULL,
    home_dir VARCHAR(255) NOT NULL,
    db_username VARCHAR(255) NOT NULL,
    db_password VARCHAR(255) NOT NULL,
    db_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
