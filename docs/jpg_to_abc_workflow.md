# JPG → ABC workflow (scans)

This repo can parse/process **ABC text**, but it cannot directly extract notes from **JPG scans**. Converting sheet-music images to ABC requires an **OMR** (optical music recognition) step.

## 1) Generate ABC stubs next to images

This creates one `.abc` per `.jpg/.jpeg` and injects a movement “cheat sheet” from the legacy dictionary.

```bash
php bin/abc-from-jpg-stubs-cli.php --tunes-dir tunes
```

Add `--overwrite` to regenerate existing stubs.

## 2) Convert scans to MusicXML (recommended)

Use an OMR tool like **Audiveris** to convert scans to MusicXML.

High-level:
- Import the JPGs into Audiveris
- Export each page/tune as MusicXML (`.musicxml` / `.xml`)

## 3) Convert MusicXML → ABC

Options:
- MuseScore can often import MusicXML and export ABC
- Python `music21` can convert MusicXML to ABC (quality varies)

## 4) Run this project’s canntaireachd processing

Once you have real ABC notes, you can generate canntaireachd and validate:

```bash
php bin/abc-cannt-cli.php --file path/to/tune.abc --convert
```

## Notes

- The movement dictionary lives in `src/Ksfraser/phpabc_canntaireachd/abc_dict.php` and `src/Ksfraser/phpabc_canntaireachd/abc_dict.txt`.
- The stub generator only scaffolds files and references movements; it does **not** interpret the scans.
