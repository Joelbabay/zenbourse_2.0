<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;
use Twig\TwigFunction;

class AppExtensionRuntime implements RuntimeExtensionInterface
{

    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function doSomething($value)
    {
        // ...
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isActiveMenu', [$this, 'isActiveMenu']),
        ];
    }
}
