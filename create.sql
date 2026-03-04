CREATE DATABASE IF NOT EXISTS BMP-180
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_hungarian_ci;

USE BMP-180;

CREATE TABLE IF NOT EXISTS measurements (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    temperature FLOAT NOT NULL,
    pressure    FLOAT NOT NULL,          -- ← változás: humidity helyett pressure (hPa)
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- Opcionális: index a gyorsabb lekérdezésekhez
CREATE INDEX idx_created_at ON measurements(created_at);
