<?php

declare(strict_types=1);

namespace PedroTroller\ImageResize;

use PedroTroller\ImageResize\Command\Reduce;
use Symfony\Component\Console\Application as SymfonyApplication;

final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new Reduce());
        $this->setDefaultCommand('reduce', true);
    }
}
