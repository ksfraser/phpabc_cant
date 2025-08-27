-- Table for default voice order
CREATE TABLE IF NOT EXISTS abc_voice_order_defaults (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voice_name VARCHAR(64) NOT NULL,
    sort_order INT NOT NULL
);

-- Prepopulate with initial values based on midi defaults and specified order
INSERT IGNORE INTO abc_voice_order_defaults (voice_name, sort_order) VALUES
    ('Bagpipes', 1),
    ('Melody', 2),
    ('Harmony', 3),
    ('Flute', 4),
    ('TenorSax', 5),
    ('Clarinet', 6),
    ('Trombone', 7),
    ('Tuba', 8),
    ('Alto', 9),
    ('Trumpet', 10),
    ('Guitar', 11),
    ('Piano', 12),
    ('BassGuitar', 13),
    ('Drums', 14),
    ('Snare', 15),
    ('Tenor', 16),
    ('Bass', 17);
