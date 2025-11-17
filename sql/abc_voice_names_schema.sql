-- Table for default name/sname for voices
CREATE TABLE IF NOT EXISTS abc_voice_names (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voice_name VARCHAR(64) NOT NULL,
    name VARCHAR(64) NOT NULL,
    sname VARCHAR(64) NOT NULL,
    transpose INT DEFAULT 0 COMMENT 'Semitones to transpose (0=concert pitch, 2=Bb, 9=Eb, 7=F)',
    octave INT DEFAULT 0 COMMENT 'Octave shift (-1=down octave, 0=no shift, 1=up octave)',
    INDEX idx_voice_name (voice_name)
);
-- Example data with transpose values (Phase 4B)
INSERT IGNORE INTO abc_voice_names (voice_name, name, sname, transpose, octave) VALUES
    ('Drums', 'Drums', 'Drums', 0, 0),
    ('Bagpipes', 'Bagpipes', 'Bagpipes', 0, 0),
    ('Flute', 'Flute', 'Flute', 0, 0),
    ('TenorSax', 'Tenor Sax', 'T.Sax', 2, 0),  -- Bb instrument
    ('Clarinet', 'Clarinet', 'Cl', 2, 0),  -- Bb instrument
    ('Trombone', 'Trombone', 'Trb', 0, 0),
    ('Tuba', 'Tuba', 'Tuba', 0, 0),
    ('Alto', 'Alto', 'Alto', 9, 0),  -- Alto Sax (Eb)
    ('Trumpet', 'Trumpet', 'Tpt', 2, 0),  -- Bb instrument
    ('Guitar', 'Guitar', 'Gtr', 0, 0),
    ('Piano', 'Piano', 'Pno', 0, 0),
    ('BassGuitar', 'Bass Guitar', 'B.Gtr', 0, 0),
    ('Tenor', 'Tenor', 'Tenor', 0, 0),
    ('Bass', 'Bass', 'Bass', 0, 0),
    ('Snare', 'Snare', 'Snare', 0, 0),
    -- Additional orchestral instruments
    ('FrenchHorn', 'French Horn', 'Hn', 7, 0),  -- F instrument
    ('AltoSax', 'Alto Sax', 'A.Sax', 9, 0),  -- Eb instrument
    ('SopranoSax', 'Soprano Sax', 'S.Sax', 2, 0),  -- Bb instrument
    ('BaritoneSax', 'Baritone Sax', 'B.Sax', 9, 0),  -- Eb instrument
    ('EnglishHorn', 'English Horn', 'E.Hn', 7, 0),  -- F instrument
    ('Violin', 'Violin', 'Vln', 0, 0),
    ('Viola', 'Viola', 'Vla', 0, 0),
    ('Cello', 'Cello', 'Vc', 0, 0),
    ('DoubleBass', 'Double Bass', 'Db', 0, 0),
    ('Oboe', 'Oboe', 'Ob', 0, 0),
    ('Bassoon', 'Bassoon', 'Bsn', 0, 0),
    ('Piccolo', 'Piccolo', 'Picc', 0, 1),  -- Sounds octave higher
    ('Strings', 'Strings', 'Str', 0, 0),
    ('Percussion', 'Percussion', 'Perc', 0, 0);
