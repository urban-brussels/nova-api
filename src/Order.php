<?php

namespace UrbanBrussels\NovaApi;

enum Order
{
    case ASC;
    case DESC;

    public function wfs(): string
    {
        return match($this)
        {
            self::ASC => 'A',
            self::DESC => 'D',
        };
    }
}