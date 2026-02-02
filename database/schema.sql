DROP SCHEMA IF EXISTS "lbaw2494" CASCADE;
CREATE SCHEMA IF NOT EXISTS "lbaw2494";
SET search_path TO "lbaw2494";

DROP TABLE IF EXISTS cards CASCADE;


DROP TABLE IF EXISTS wishlists CASCADE;
DROP TABLE IF EXISTS reports CASCADE;
DROP TABLE IF EXISTS rates CASCADE;
DROP TABLE IF EXISTS bids CASCADE;
DROP TABLE IF EXISTS auctions CASCADE;
DROP TABLE IF EXISTS notifications CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS topups CASCADE;
DROP TABLE IF EXISTS subcategories CASCADE;
DROP TABLE IF EXISTS categories CASCADE;

DROP TYPE IF EXISTS AuctionStatus CASCADE;
DROP TYPE IF EXISTS BidStatus CASCADE;
DROP TYPE IF EXISTS ReportType CASCADE;
DROP TYPE IF EXISTS NotificationType CASCADE;
DROP TYPE IF EXISTS PaymentMethod CASCADE;

DROP FUNCTION IF EXISTS notify_auction_closing();

CREATE TYPE AuctionStatus AS ENUM(
    'active',
    'sold',
    'requestCancellation', 
    'cancelled' 
);

CREATE TYPE BidStatus AS ENUM(
    'active',
    'won',
    'lost',
    'requestCancellation', 
    'cancelled' 
);

CREATE TYPE ReportType AS ENUM (
    'user',
    'auction'
);

CREATE TYPE NotificationType AS ENUM (
    'auctionbid',
    'auctionending',
    'auctionreport',
    'userreport',
    'auctionended',
    'biddeleted',
    'userrating',
    'auctionwishlist'
);

CREATE TYPE PaymentMethod as ENUM (
    'creditCard', 
    'bankTransfer', 
    'payPal', 
    'mbWay'
);

CREATE TABLE categories (
    id SERIAL PRIMARY KEY, 
    name TEXT NOT NULL,
    description TEXT NOT NULL,
    imagepath VARCHAR NOT NULL
);

CREATE TABLE subcategories (
    id SERIAL PRIMARY KEY, 
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    imagepath VARCHAR NOT NULL,
    category_id INTEGER REFERENCES categories NOT NULL
);


CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name TEXT NOT NULL,
    username TEXT NOT NULL UNIQUE,
    imagepath TEXT NOT NULL,
    description TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    creationDate TIMESTAMP DEFAULT NOW(),
    balance DECIMAL(10, 2) DEFAULT 0 CHECK (balance >= 0.0),
    avgrating DECIMAL(2, 1) DEFAULT 0 CHECK (avgrating BETWEEN 0.0 AND 5.0),
    ratecount INTEGER DEFAULT 0,
    isBanned BOOLEAN DEFAULT FALSE NOT NULL,
    isadmin BOOLEAN DEFAULT FALSE NOT NULL,
    number_of_bids INT DEFAULT 0,
    number_of_auctions INT DEFAULT 0,
    auctions_followed INT DEFAULT 0 
);

CREATE TABLE user_blocks (
    id SERIAL PRIMARY KEY,
    blocker_id INTEGER REFERENCES  users(id) ON DELETE CASCADE NOT NULL,
    blocked_id INTEGER REFERENCES  users(id) ON DELETE CASCADE NOT NULL,
    block_date TIMESTAMP DEFAULT NOW()
);


CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    auctionName TEXT,
    bidValue NUMERIC(10,2),
    sellerID INTEGER,
    imagepath TEXT,
    auctionID INTEGER,
    bidderID INTEGER,
    auctionURL TEXT,
    bidderURL TEXT,
    reportURL TEXT,
    read BOOLEAN DEFAULT FALSE NOT NULL,
    creationDate TIMESTAMP NOT NULL DEFAULT NOW(),
    type NotificationType NOT NULL
);

CREATE TABLE auctions (
    id SERIAL PRIMARY KEY,     
    itemName VARCHAR NOT NULL,                      
    startingPrice NUMERIC(10, 2) NOT NULL CHECK (startingPrice > 0.0),                
    currentPrice NUMERIC(10, 2) NOT NULL DEFAULT 0.0,
    creationDate TIMESTAMP DEFAULT NOW(),               
    increment DECIMAL(10, 2) DEFAULT 5.0 NOT NULL,        
    deadLine TIMESTAMP NOT NULL CHECK (deadLine > creationDate), 
	status AuctionStatus DEFAULT 'active' NOT NULL,
    subcategory INTEGER REFERENCES subcategories NOT NULL,
    description TEXT NOT NULL,
    imagepath VARCHAR(50) NOT NULL,
    seller INTEGER REFERENCES users NOT NULL
);

CREATE TABLE bids (
    id SERIAL PRIMARY KEY, 
    value DECIMAL(10, 2) NOT NULL CHECK (value >= 5),
    date TIMESTAMP DEFAULT NOW(),
    bidder INTEGER REFERENCES users NOT NULL,
    auctionID INTEGER REFERENCES auctions ON DELETE CASCADE NOT NULL,
    status BidStatus DEFAULT 'active' NOT NULL
);

CREATE TABLE rates (
    id SERIAL PRIMARY KEY, 
    rating NUMERIC(3, 2) NOT NULL CHECK (rating BETWEEN 0 AND 5),
    rater INTEGER REFERENCES users NOT NULL,
    rated INTEGER REFERENCES users NOT NULL
);

CREATE TABLE reports (
    id SERIAL PRIMARY KEY,
    reported_id INTEGER NOT NULL,
    reason VARCHAR(255) NOT NULL,
    isSolved BOOLEAN DEFAULT FALSE NOT NULL,
    date TIMESTAMP DEFAULT NOW() NOT NULL,
    reporter INTEGER REFERENCES users NOT NULL,
    reviewer INTEGER REFERENCES users DEFAULT NULL,
    description VARCHAR(255),
    type ReportType NOT NULL
);

CREATE TABLE wishlists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE, 
    auction_id INTEGER REFERENCES auctions(id) ON DELETE CASCADE
);

CREATE TABLE topups (
    id SERIAL PRIMARY KEY,
    amount NUMERIC (10,2) NOT NULL,
    date TIMESTAMP DEFAULT NOW() NOT NULL,
    isWithdrawal BOOLEAN NOT NULL,
    method PaymentMethod NOT NULL,
    userID INTEGER REFERENCES users NOT NULL
);

CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL
);


---- Indexes

CREATE INDEX notification_search_index ON notifications USING btree(type, read);

CREATE INDEX bid_bidder_hash_index ON bids USING hash(bidder);

CREATE INDEX auction_itemname_fulltext_index ON auctions USING GIN (to_tsvector('english', itemName));


--  Triggers

-- Trigger 01
CREATE OR REPLACE FUNCTION prevent_self_bidding()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM auctions
        WHERE auctions.id = NEW.id AND auctions.seller = NEW.bidder
    ) THEN
        RAISE EXCEPTION 'Users cannot bid on their own auctions.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_prevent_self_bidding
BEFORE INSERT ON bids
FOR EACH ROW
EXECUTE FUNCTION prevent_self_bidding();




-- Trigger 02
CREATE OR REPLACE FUNCTION auction_cancellation_policy()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'cancelled' THEN
        IF EXISTS (
            SELECT 1
            FROM bids
            WHERE bids.auctionid = NEW.id
        ) THEN
            RAISE EXCEPTION 'Auction cannot be canceled because there are existing bids.';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_auction_cancellation
BEFORE UPDATE ON auctions
FOR EACH ROW
WHEN (OLD.status <> 'cancelled' AND NEW.status = 'cancelled')
EXECUTE FUNCTION auction_cancellation_policy();

-- Trigger 03
CREATE OR REPLACE FUNCTION update_user_balance()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.isWithdrawal = FALSE THEN
        UPDATE users
        SET balance = balance + NEW.amount
        WHERE id = NEW.userID;
    ELSE
        IF (SELECT balance FROM users WHERE id = NEW.userID) < NEW.amount THEN
            RAISE EXCEPTION 'Insufficient balance for withdrawal.';
        END IF;
        UPDATE users
        SET balance = balance - NEW.amount
        WHERE id = NEW.userID;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_user_balance
AFTER INSERT ON topups
FOR EACH ROW
EXECUTE FUNCTION update_user_balance();

-- Trigger 04
CREATE OR REPLACE FUNCTION extend_auction_deadline()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.date >= (SELECT deadLine - interval '15 minutes' FROM auctions WHERE id = NEW.auctionID) THEN
        UPDATE auctions
        SET deadLine = deadLine + interval '30 minutes'
        WHERE id = NEW.auctionID;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_extend_auction_deadline
AFTER INSERT ON bids
FOR EACH ROW
EXECUTE FUNCTION extend_auction_deadline();


-- Trigger 05
CREATE OR REPLACE FUNCTION prevent_consecutive_bids()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM bids b
        WHERE b.auctionID = NEW.auctionID
        AND b.bidder = NEW.bidder
        AND b.value = (SELECT MAX(value) FROM bids WHERE auctionID = NEW.auctionID)
    ) THEN
        RAISE EXCEPTION 'You already have the highest bid on this auction, consecutive bids are not allowed.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_prevent_consecutive_bids
BEFORE INSERT ON bids
FOR EACH ROW
EXECUTE FUNCTION prevent_consecutive_bids();

-- Trigger 06
CREATE OR REPLACE FUNCTION prevent_user_deletion_with_active_auctions()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM auctions a
        WHERE a.seller = OLD.id
        AND a.status = 'active'
    ) THEN
        RAISE EXCEPTION 'Cannot delete account while active auctions exist.';
    END IF;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_prevent_user_deletion_with_active_auctions
BEFORE DELETE ON users
FOR EACH ROW
EXECUTE FUNCTION prevent_user_deletion_with_active_auctions();

-- Trigger 07
CREATE OR REPLACE FUNCTION enforce_auction_duration_limit()
RETURNS TRIGGER AS $$
DECLARE
    max_duration INTERVAL := interval '30 days';
BEGIN
    IF NEW.deadLine > (NEW.creationDate + max_duration) THEN
        RAISE EXCEPTION 'Auction duration cannot exceed 30 days.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_auction_duration_limit
BEFORE INSERT OR UPDATE ON auctions
FOR EACH ROW
EXECUTE FUNCTION enforce_auction_duration_limit();

-- Trigger 08
CREATE OR REPLACE FUNCTION mark_user_content_as_deleted() RETURNS TRIGGER AS $$
BEGIN
    UPDATE auctions SET seller = 0 WHERE seller = OLD.id;

    UPDATE bids SET bidder = 0 WHERE bidder = OLD.userID;

    UPDATE rates SET rater = 0 WHERE rater = OLD.id;
    UPDATE rates SET rated = 0 WHERE rated = OLD.id;

    UPDATE reports SET reporter = 0 WHERE reporter = OLD.id;
    UPDATE reports SET reviewer = 0 WHERE reviewer = OLD.id;

    UPDATE wishlists SET userID = 0 WHERE userID = OLD.id;

    UPDATE topups SET userID = 0 WHERE userID = OLD.id;

    DELETE FROM users WHERE id = OLD.id;

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_mark_user_content_as_deleted
BEFORE DELETE ON users
FOR EACH ROW
EXECUTE FUNCTION mark_user_content_as_deleted();

/*
-- Trigger 09
CREATE OR REPLACE FUNCTION notify_user_rated() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO notifications (id, read, message, creationDate, type)
    VALUES (
		DEFAULT,
        FALSE, 
        CONCAT(NEW.rater, ' rated you with ', NEW.rating),
        CURRENT_TIMESTAMP,
        'rate'
    );

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;


CREATE TRIGGER user_rated_trigger
AFTER INSERT ON rates
FOR EACH ROW
EXECUTE FUNCTION notify_user_rated();

-- Trigger 10
CREATE OR REPLACE FUNCTION notify_auction_closing() 
RETURNS TRIGGER AS $$
DECLARE
    auction_record RECORD;
BEGIN
    FOR auction_record IN
        SELECT a.id, a.itemName, a.deadLine, w.userID
        FROM auctions a
        JOIN wishlists w ON a.id = w.auction
        WHERE a.deadLine <= NOW() + INTERVAL '10 minutes'
        AND a.deadLine > NOW()
    LOOP
        INSERT INTO Notification (id, read, message, creationDate, type)
        VALUES (
            DEFAULT,
            FALSE, 
            CONCAT('Auction ', auction_record.itemName, ' is about to end in less than 10 minutes!'),
            CURRENT_TIMESTAMP,
            'auctionStatus'
        );
    END LOOP;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER notify_auction_closing_trigger
AFTER INSERT OR UPDATE ON auctions
FOR EACH STATEMENT
EXECUTE FUNCTION notify_auction_closing();

-- Trigger 11
CREATE OR REPLACE FUNCTION notify_bid_placed() RETURNS TRIGGER AS $$
DECLARE
    auction_record RECORD;
    seller_id INT;
BEGIN
    FOR auction_record IN
        SELECT w.userID
        FROM wishlists w
        WHERE w.auction = NEW.auction
    LOOP
        INSERT INTO Notification (id, read, message, creationDate, type)
        VALUES (
            DEFAULT, 
            FALSE, 
            CONCAT('A new bid has been placed on the auction ', NEW.auction, '.'),
            CURRENT_TIMESTAMP,
            'bidStatus'
        );
    END LOOP;

    SELECT seller INTO seller_id FROM Auction WHERE auctionID = NEW.auction;

    IF seller_id = NEW.bidder THEN
        INSERT INTO Notification (notiffID, read, message, creationDate, type)
        VALUES (
            DEFAULT, 
            FALSE, 
            CONCAT('A new bid has been placed on your auction ', NEW.auction, '.'),
            CURRENT_TIMESTAMP,
            'bidStatus'
        );
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION notify_bid_placed_function() RETURNS TRIGGER AS $$
BEGIN
    PERFORM notify_bid_placed();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger 12
CREATE OR REPLACE FUNCTION report_report_solved() RETURNS VOID AS $$
DECLARE
    report_record RECORD;
BEGIN
    FOR report_record IN
        SELECT r.id, r.reporter, r.reason
        FROM reports r
        WHERE r.isSolved = TRUE
        AND NOT EXISTS (
            SELECT 1 FROM notifications n
            WHERE n.message LIKE CONCAT('Your report on ', r.reason, ' has been resolved.')
            AND n.creationDate > NOW() - INTERVAL '1 DAY'
        )
    LOOP
        INSERT INTO notifications (id, read, message, creationDate, type)
        VALUES (
            DEFAULT, 
            FALSE, 
            CONCAT('Your report on ', report_record.reason, ' has been resolved.'),
            CURRENT_TIMESTAMP,
            'report'
        );
    END LOOP;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION notify_report_solved_trigger_function() RETURNS TRIGGER AS $$
BEGIN
    PERFORM notify_report_solved();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger 13
CREATE OR REPLACE FUNCTION notify_wishlist_item_sold() RETURNS TRIGGER AS $$
DECLARE
    wishlist_record RECORD;
BEGIN
    FOR wishlist_record IN
        SELECT w.id, w.userID, a.itemName AS auction_name
        FROM wishlists w
        JOIN auctions a ON w.auction = a.id
        WHERE a.status = 'sold'
        AND NOT EXISTS (
            SELECT 1 
            FROM notifications n
            WHERE n.message LIKE CONCAT('The item ', a.itemName, ' you wished for has been sold.')
            AND n.creationDate > NOW() - INTERVAL '1 DAY'
        )
    LOOP
        INSERT INTO notifications (id, read, message, creationDate, type)
        VALUES (
            DEFAULT, 
            FALSE, 
            CONCAT('The item ', wishlist_record.auction_name, ' you wished for has been sold.'),
            CURRENT_TIMESTAMP,
            'wishlist'
        );
    END LOOP;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER notify_wishlist_item_sold_trigger
AFTER UPDATE OF status ON auctions
FOR EACH ROW
WHEN (OLD.status != 'sold' AND NEW.status = 'sold')
EXECUTE FUNCTION notify_wishlist_item_sold();
*/

-- Trigger 14
CREATE OR REPLACE FUNCTION validate_bid() 
RETURNS TRIGGER AS $$
DECLARE
    current_bid_price DECIMAL(10, 2);
    increment_calculated DECIMAL(10, 2);
    minimum_increment DECIMAL(10, 2);
    minimum_percentage DECIMAL(10, 2);
BEGIN
    -- Fetch current price and increment from the auctions table
    SELECT currentPrice, increment 
    INTO current_bid_price, increment_calculated
    FROM auctions
    WHERE id = NEW.auctionID;

    -- Calculate the minimum bid based on increment and percentage
    minimum_increment := current_bid_price + increment_calculated;
    minimum_percentage := current_bid_price + current_bid_price * 0.10;

    -- Check if the new bid value satisfies either condition
    IF NEW.value < LEAST(minimum_increment, minimum_percentage) THEN
        -- Delete the bid if invalid
        DELETE FROM bids WHERE id = NEW.id;
        RAISE EXCEPTION 'Bid must be at least 10%% of the current price or the increment amount';
    END IF;

    -- If the bid is valid, update the auction's current price
    UPDATE auctions
    SET currentPrice = NEW.value
    WHERE id = NEW.auctionID;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger definition
CREATE TRIGGER validate_bid_trigger
AFTER INSERT ON bids
FOR EACH ROW
EXECUTE FUNCTION validate_bid();