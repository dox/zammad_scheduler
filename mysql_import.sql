CREATE TABLE IF NOT EXISTS tickets (
    uid SERIAL PRIMARY KEY,
    subject TEXT NOT NULL,
    body TEXT NOT NULL,
    zammad_priority VARCHAR(100) NOT NULL DEFAULT '2',
    zammad_group BIGINT NOT NULL,
    tags VARCHAR(200),
    frequency VARCHAR(100) NOT NULL,
    frequency2 VARCHAR(100),
    zammad_agent BIGINT NOT NULL,
    zammad_customer BIGINT NOT NULL,
    cc VARCHAR(255),
    status VARCHAR(45) NOT NULL DEFAULT 'Enabled',
    last_id BIGINT
);

CREATE TABLE IF NOT EXISTS logs (
    uid SERIAL PRIMARY KEY,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    type VARCHAR(40) NOT NULL
);
