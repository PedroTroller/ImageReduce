<?php

declare(strict_types=1);

namespace PedroTroller\ImageResize;

use Symfony\Component\Console\Application as SymfonyApplication;
use PedroTroller\ImageResize\Command\Reduce;

final class Application extends SymfonyApplication
{
    public function __construct()
    {
        parent::__construct();

        $this->add(new Reduce());
        $this->setDefaultCommand('reduce', true);
    }
}
