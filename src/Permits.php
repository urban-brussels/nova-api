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
    private array $results;
    private string $filter;
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
        if($this->type === "PE") {
            $this->path = self::PE_PATH;
            $this->layer = self::PE_LAYER_NAME;
        }
        else {
            $this->path = self::PU_PATH;
            $this->layer = self::PU_LAYER_NAME;
        }
    }

    public function filterById(int $id): self
    {
        $id_dossier = ($this->type === "PE") ? 'nova_seq' : 's_iddossier';
        $this->cql_filter = $id_dossier. '=' . $id;

        return $this;
    }

    public function getResults(): self
    {
        $wfs = new WfsLayer($this->path, $this->layer);
        $this->results = $wfs->setCqlFilter($this->cql_filter)
            ->setCount(1)
            ->setOutputSrs(4326)
            ->getPropertiesArray(false);

        return $this;
    }

    public function first(): array
    {
        return $this->results[0] ?? [];
    }

}