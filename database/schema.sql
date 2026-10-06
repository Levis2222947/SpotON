-- SpotOn – databaseschema
-- Importeer dit bestand eerst, daarna seed.sql.
-- Op Plesk: verwijder de regels CREATE DATABASE en USE als de database al bestaat.

CREATE DATABASE IF NOT EXISTS spoton CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spoton;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Gebruikers. Rol 'visitor' = bezoeker, 'staff' = ticketmedewerker.
CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('visitor', 'staff') NOT NULL DEFAULT 'visitor',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

-- Evenementen met capaciteit en verkoopperiode.
CREATE TABLE events (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title          VARCHAR(150) NOT NULL,
    description    TEXT NOT NULL,
    program        TEXT NULL,
    location       VARCHAR(150) NOT NULL,
    category_id    INT UNSIGNED NOT NULL,
    starts_at      DATETIME NOT NULL,
    capacity       INT UNSIGNED NOT NULL,
    sale_starts_at DATETIME NOT NULL,
    sale_ends_at   DATETIME NOT NULL,
    status         ENUM('draft', 'published', 'cancelled') NOT NULL DEFAULT 'draft',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_events_starts_at (starts_at),
    KEY idx_events_status (status),
    CONSTRAINT fk_events_category FOREIGN KEY (category_id) REFERENCES categories (id),
    CONSTRAINT chk_events_capacity CHECK (capacity > 0),
    CONSTRAINT chk_events_sale_period CHECK (sale_starts_at < sale_ends_at)
) ENGINE=InnoDB;

-- Een reservering bevat één of meer tickets.
-- Alleen reserveringen met status 'confirmed' tellen mee voor de bezette plaatsen.
CREATE TABLE reservations (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    event_id     INT UNSIGNED NOT NULL,
    quantity     TINYINT UNSIGNED NOT NULL,
    status       ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cancelled_at DATETIME NULL,
    KEY idx_reservations_event_status (event_id, status),
    CONSTRAINT fk_reservations_user FOREIGN KEY (user_id) REFERENCES users (id),
    CONSTRAINT fk_reservations_event FOREIGN KEY (event_id) REFERENCES events (id),
    CONSTRAINT chk_reservations_quantity CHECK (quantity > 0)
) ENGINE=InnoDB;

-- Elk ticket heeft een unieke code die aan de deur één keer gebruikt kan worden.
CREATE TABLE tickets (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT UNSIGNED NOT NULL,
    code           VARCHAR(20) NOT NULL,
    status         ENUM('valid', 'used', 'cancelled') NOT NULL DEFAULT 'valid',
    used_at        DATETIME NULL,
    used_by        INT UNSIGNED NULL,
    UNIQUE KEY uq_tickets_code (code),
    CONSTRAINT fk_tickets_reservation FOREIGN KEY (reservation_id) REFERENCES reservations (id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_used_by FOREIGN KEY (used_by) REFERENCES users (id)
) ENGINE=InnoDB;
