<?php

declare(strict_types=1);

namespace ksfraser\PhpabcCanntaireachd\Decorator;

class FermataDecorator extends BaseDecorator {
    protected static $latinName = 'Fermata';
    protected static $shortcuts = ['!fermata!', '^'];
}
