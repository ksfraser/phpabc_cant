# legacy/

Original pre-PSR-4 code, moved out of `src/` on 2026-10.

## Why it moved

`src/Ksfraser/phpabc_canntaireachd/` sat beside `src/Ksfraser/PhpabcCanntaireachd/`
— two directories differing only in the case of one path segment. That was:

1. **A duplicate-class hazard.** 32 class files here declare the *same* fully
   qualified names as their modern counterparts (e.g. `TokenDictionary`,
   `CanntaireachdBarProcessor`). Any process that loaded both would fatal with
   `Cannot declare class …, because the name is already in use`. The modern
   tree is PSR-4 autoloaded; this tree was reachable only by hand-written
   `require`, which is exactly how the collision would happen.
2. **A case-insensitivity hazard.** On macOS/Windows the two directories are
   the *same* directory, so the tree could not even be checked out cleanly.

## What it contains

The original author's scripts (`abc2teaching.php`, `bmw2abc.php`,
`process.php`, `class.*.php`, `test.*.php`) kept for reference. They are
**not** autoloaded, are not covered by the test suite, and use
`require_once('abc_dict.php')` relative includes that only resolve when the
current working directory is this folder.

`abc_dict.php` here is now a shim that loads the canonical dictionary from
`src/ksfraser/PhpabcCanntaireachd/abc_dict.php`.

## Rule

Do not add features here, and do not copy code from here into `src/` without
porting it to the modern PSR-4 layout. New work belongs in
`src/ksfraser/PhpabcCanntaireachd/`.
