<?php

namespace UrbanBrussels\NovaApi;

use ici\ici_tools\WfsLayer;

class Permits
{
    private const PU_PATH = 'https://geoservices-others.irisnet.be/geoserver/Nova/ows';
    private const PE_PATH = self::PU_PATH;
    private const PU_LAYER_NAME = 'Nova:vmnovaurbanview';
    private const PE_LAYER_NAME = 'Nova:vm_nova_pe';

    private string $path;
    private string $layer;
    private string $cql_filter;

    public function __construct(string $type)
    {
        if($type === "PE") {
            $this->path = self::PE_PATH;
            $this->layer = self::PE_LAYER_NAME;
        }
        else {
            $this->path = self::PU_PATH;
            $this->layer = self::PU_LAYER_NAME;
        }
    }

    public function getResults(): array
    {
        $wfs = new WfsLayer($this->path, $this->layer);
        return $wfs->setCqlFilter($this->cql_filter)
            ->setCount(1)
            ->setOutputSrs(4326)
            ->getPropertiesArray(false);
    }
}