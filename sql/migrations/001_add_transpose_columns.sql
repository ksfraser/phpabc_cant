-- Add transpose and octave columns to abc_voice_names table
-- This supports Phase 4B: Transpose Modes

-- Add transpose column (semitones to transpose, e.g., 2 for Bb instruments)
ALTER TABLE abc_voice_names 
ADD COLUMN IF NOT EXISTS transpose INT DEFAULT 0 
COMMENT 'Semitones to transpose (0=concert pitch, 2=Bb, 9=Eb, 7=F)';

-- Add octave column (octave shift, e.g., -1 for bass instruments)
ALTER TABLE abc_voice_names 
ADD COLUMN IF NOT EXISTS octave INT DEFAULT 0 
COMMENT 'Octave shift (-1=down octave, 0=no shift, 1=up octave)';

-- Add index for faster lookups
CREATE INDEX IF NOT EXISTS idx_voice_name ON abc_voice_names(voice_name);

-- Update existing records with standard transpose values
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Drums';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Bagpipes';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Flute';
UPDATE abc_voice_names SET transpose = 2 WHERE voice_name = 'TenorSax';
UPDATE abc_voice_names SET transpose = 2 WHERE voice_name = 'Clarinet';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Trombone';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Tuba';
UPDATE abc_voice_names SET transpose = 9 WHERE voice_name = 'Alto';  -- Alto Sax (Eb)
UPDATE abc_voice_names SET transpose = 2 WHERE voice_name = 'Trumpet';  -- Bb
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Guitar';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Piano';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'BassGuitar';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Tenor';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Bass';
UPDATE abc_voice_names SET transpose = 0 WHERE voice_name = 'Snare';

-- Add common orchestral instruments with transpose values
INSERT IGNORE INTO abc_voice_names (voice_name, name, sname, transpose, octave) VALUES
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
