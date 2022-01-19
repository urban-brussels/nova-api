<?php

namespace UrbanBrussels\NovaApi;

enum Attribute
{
    case DATE_ARC;
    case DATE_ARI;
    case DATE_SUBMISSION;

    public function pu(): string
    {
        return match($this)
        {
            self::DATE_ARC => 'date_arc',
            self::DATE_ARI => 'date_ari',
            self::DATE_SUBMISSION => 'datedepot',
        };
    }

    public function pe(): string
    {
        return match($this)
        {
            self::DATE_ARC => 'datearclast',
            self::DATE_ARI => 'datearilast',
            self::DATE_SUBMISSION => 'date_depot',
        };
    }
}