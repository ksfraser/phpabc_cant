-- Table for default name/sname for voices
CREATE TABLE IF NOT EXISTS abc_voice_names (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voice_name VARCHAR(64) NOT NULL,
    name VARCHAR(64) NOT NULL,
    sname VARCHAR(64) NOT NULL
);
-- Example data, prepopulated from abc_midi_defaults
INSERT IGNORE INTO abc_voice_names (voice_name, name, sname) VALUES
    ('Drums', 'Drums', 'Drums'),
    ('Bagpipes', 'Bagpipes', 'Bagpipes'),
    ('Flute', 'Flute', 'Flute'),
    ('TenorSax', 'Tenor Sax', 'Tenor Sax'),
    ('Clarinet', 'Clarinet', 'Clarinet'),
    ('Trombone', 'Trombone', 'Trombone'),
    ('Tuba', 'Tuba', 'Tuba'),
    ('Alto', 'Alto', 'Alto'),
    ('Trumpet', 'Trumpet', 'Trumpet'),
    ('Guitar', 'Guitar', 'Guitar'),
    ('Piano', 'Piano', 'Piano'),
    ('BassGuitar', 'Bass Guitar', 'Bass Guitar'),
    ('Tenor', 'Tenor', 'Tenor'),
    ('Bass', 'Bass', 'Bass'),
    ('Snare', 'Snare', 'Snare');
