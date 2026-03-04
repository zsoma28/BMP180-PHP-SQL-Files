USE BMP180;

CREATE TABLE IF NOT EXISTS api_keys (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    api_key     VARCHAR(64) UNIQUE NOT NULL,
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Példa kulcs beszúrása (generálj biztonságosat, pl. openssl rand -hex 32)
INSERT INTO api_keys (api_key) VALUES ('123456789');   -- ← cseréld ki!
