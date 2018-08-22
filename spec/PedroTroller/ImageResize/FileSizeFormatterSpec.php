<?php

declare(strict_types=1);

namespace spec\PedroTroller\ImageResize;

use PedroTroller\ImageResize\FileSizeFormatter;
use PhpSpec\ObjectBehavior;

class FileSizeFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(FileSizeFormatter::class);
    }

    function it_can_format_a_byte_amount()
    {
        $this->bytes(120)->shouldReturn('120 B');
        $this->bytes(1200)->shouldReturn('1.20 KB');
        $this->bytes(1200000)->shouldReturn('1.20 MB');
        $this->bytes(1200000000)->shouldReturn('1.20 GB');
        $this->bytes(1200000000000)->shouldReturn('1.20 TB');
    }

    function it_can_format_a_kilo_byte_amount()
    {
        $this->kiloBytes(1.2)->shouldReturn('1.20 KB');
        $this->kiloBytes(120)->shouldReturn('120 KB');
        $this->kiloBytes(1200)->shouldReturn('1.20 MB');
        $this->kiloBytes(1200000)->shouldReturn('1.20 GB');
        $this->kiloBytes(1200000000)->shouldReturn('1.20 TB');
    }

    function it_can_format_a_mega_byte_amount()
    {
        $this->megaBytes(1.2)->shouldReturn('1.20 MB');
        $this->megaBytes(120)->shouldReturn('120 MB');
        $this->megaBytes(1200)->shouldReturn('1.20 GB');
        $this->megaBytes(1200000)->shouldReturn('1.20 TB');
    }

    function it_can_format_a_giga_byte_amount()
    {
        $this->gigaBytes(1.2)->shouldReturn('1.20 GB');
        $this->gigaBytes(120)->shouldReturn('120 GB');
        $this->gigaBytes(1200)->shouldReturn('1.20 TB');
    }

    function it_can_format_a_tera_byte_amount()
    {
        $this->teraBytes(1.2)->shouldReturn('1.20 TB');
        $this->teraBytes(120)->shouldReturn('120 TB');
        $this->teraBytes(1200)->shouldReturn('1200 TB');
    }
}
