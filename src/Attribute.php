<?php

namespace UrbanBrussels\NovaApi;

enum Attribute
{
    case SUBMISSION;
    case ARC;
    case ARI;

    public function wfs(): array
    {
        return match($this)
        {
            self::SUBMISSION => ['datedepot', 'date_depot'],
            self::ARC => ['date_arc', 'datearclast'],
            self::ARI => ['date_ari', 'datearilast'],
        };
    }
}