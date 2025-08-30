<?php
namespace Ksfraser\PhpabcCanntaireachd\BodyLineHandler;

interface AbcBodyLineHandlerInterface {
    public function handle(&$context, $line);
    public function matches($line): bool;
}
