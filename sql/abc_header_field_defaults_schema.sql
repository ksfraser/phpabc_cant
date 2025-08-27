-- Table for ABC header field defaults
CREATE TABLE IF NOT EXISTS abc_header_field_defaults (
    id INT AUTO_INCREMENT PRIMARY KEY,
    field_name VARCHAR(8) NOT NULL,
    field_value VARCHAR(255) NOT NULL
);

-- Prepopulate with initial values
INSERT IGNORE INTO abc_header_field_defaults (field_name, field_value) VALUES
    ('K', 'HP'),
    ('Q', '1/4=90'),
    ('L', '1/8'),
    ('M', '2/4'),
    ('R', 'March'),
    ('O', 'Kevin Fraser'),
    ('Z', 'Kevin Fraser');
