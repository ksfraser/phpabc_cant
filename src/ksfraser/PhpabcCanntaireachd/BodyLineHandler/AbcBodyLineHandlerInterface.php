<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\BodyLineHandler;

interface AbcBodyLineHandlerInterface {
    public function handle(&$context, $line);
    public function matches($line): bool;
}
