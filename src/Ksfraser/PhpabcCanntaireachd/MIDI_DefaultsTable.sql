-- Table for MIDI defaults for voices
CREATE TABLE abc_midi_defaults (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voice_name VARCHAR(64) NOT NULL,
    midi_channel INT NOT NULL,
    midi_program INT NOT NULL
);
-- Example data
INSERT INTO abc_midi_defaults (voice_name, midi_channel, midi_program) VALUES
('Drums', 10, 0),
('Bagpipes', 0, 110),
('Flute', 1, 72),
('Tenor', 2, 65),
('Clarinet', 3, 71),
('Trombone', 4, 57),
('Tuba', 5, 58),
('Alto', 6, 65),
('Trumpet', 7, 56),
('Guitar', 8, 27),
('Piano', 9, 0),
('BassGuitar', 12, 20);
