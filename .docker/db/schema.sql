SET GLOBAL max_allowed_packet = 128 * 1048576; -- 128MB

SET GLOBAL event_scheduler = ON;

USE mydb;

-- Drop all events if exist
DROP EVENT IF EXISTS purge_expired_refresh_tokens;

-- Drop all tables if exist
DROP TABLE IF EXISTS illustration;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS purchase_history;
DROP TABLE IF EXISTS cart_product;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product;
DROP TABLE IF EXISTS shop;
DROP TABLE IF EXISTS customer;
DROP TABLE IF EXISTS refresh_token;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS message;

-- Create all tables
CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL
);

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(255) NOT NULL UNIQUE,
    email NVARCHAR(255),
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role NVARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE refresh_token (
    jti BINARY(16) PRIMARY KEY,   -- UUIDv4 binary form
    hash VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    issued_at TIMESTAMP,
    expires_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE customer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    phone_number VARCHAR(30),

    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE shop (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    location NVARCHAR(255),
    phone_number VARCHAR(30),

    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name NVARCHAR(255) NOT NULL UNIQUE,
    original_price DECIMAL(10, 2) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    brief_description NVARCHAR(500),
    detail_description TEXT,
    shop_id INT NOT NULL,
    -- Redundant fields to improve search speed
    average_rating DECIMAL(5, 2),
    discount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    --
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (shop_id) REFERENCES shop(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE cart_product (
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (cart_id, product_id),
    FOREIGN KEY (cart_id) REFERENCES cart(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE purchase_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE illustration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    main BOOLEAN NOT NULL DEFAULT FALSE,
    image_path NVARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL DEFAULT 1,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE (customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customer(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES product(id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- Triggers to handle redundant fields automatically

-- Trigger for discount on product's price changed
DELIMITER //

CREATE TRIGGER update_discount_before_update
BEFORE UPDATE ON product
FOR EACH ROW
BEGIN
    -- Check if there is a change in price or original_price
    IF NEW.price != OLD.price OR NEW.original_price != OLD.original_price THEN
        -- Ensure original_price is greater than 0 to avoid division by zero
        IF NEW.original_price > 0 THEN
            -- Calculate the discount percentage based on the new values
            SET NEW.discount = ((NEW.original_price - NEW.price) / NEW.original_price) * 100;
        ELSE
            -- Set discount to 0 if original_price is not positive
            SET NEW.discount = 0;
        END IF;
    END IF;
END//

DELIMITER ;

-- Trigger for average_rating on product's rating changed
DELIMITER //

CREATE TRIGGER update_average_rating_after_insert
AFTER INSERT ON feedback
FOR EACH ROW
BEGIN
    -- Recalculate the average rating for the product
    UPDATE product
    SET average_rating = (
        SELECT AVG(rating) 
        FROM feedback
        WHERE product_id = NEW.product_id
    )
    WHERE id = NEW.product_id;
END//

DELIMITER ;

DELIMITER //

CREATE TRIGGER update_average_rating_after_update
AFTER UPDATE ON feedback
FOR EACH ROW
BEGIN
    -- Recalculate the average rating for the product
    UPDATE product
    SET average_rating = (
        SELECT AVG(rating)
        FROM feedback
        WHERE product_id = NEW.product_id
    )
    WHERE id = NEW.product_id;
END//

DELIMITER ;

DELIMITER //

CREATE TRIGGER update_average_rating_after_delete
AFTER DELETE ON feedback
FOR EACH ROW
BEGIN
    DECLARE new_avg DECIMAL(10, 2)(5, 2);

    -- Calculate the new average rating for the product
    SELECT AVG(rating)
    INTO new_avg
    FROM feedback
    WHERE product_id = OLD.product_id;

    -- Update the average rating in the product table, setting it to NULL if there are no remaining ratings
    UPDATE product
    SET average_rating = new_avg
    WHERE id = OLD.product_id;
END//

DELIMITER ;

DELIMITER //

CREATE TRIGGER update_user_timestamp_after_customer_update
AFTER UPDATE ON customer
FOR EACH ROW
BEGIN
    -- Update the updated_at field in the corresponding user record
    UPDATE user
    SET updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.user_id;
END//

DELIMITER ;

DELIMITER //

CREATE TRIGGER update_user_timestamp_after_shop_update
AFTER UPDATE ON shop
FOR EACH ROW
BEGIN
    -- Update the updated_at field in the corresponding user record
    UPDATE user
    SET updated_at = CURRENT_TIMESTAMP
    WHERE id = NEW.user_id;
END//

DELIMITER ;

-- Event Scheduler
CREATE EVENT purge_expired_refresh_tokens
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    DELETE FROM refresh_token
    WHERE expires_at IS NOT NULL AND expires_at <= NOW();
END;