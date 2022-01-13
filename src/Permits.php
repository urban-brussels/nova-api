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
    private string $type;

    public function __construct(string $type)
    {
        $this->type = $type;
        if ($this->type === "PE") {
            $this->path = self::PE_PATH;
            $this->layer = self::PE_LAYER_NAME;
        } else {
            $this->path = self::PU_PATH;
            $this->layer = self::PU_LAYER_NAME;
        }
    }

    public function filterById(int $id): self
    {
        $id_dossier = ($this->type === "PE") ? 'nova_seq' : 's_iddossier';
        $this->cql_filter = $id_dossier.'='.$id;

        return $this;
    }

    public function filterByInquiryDate(string $date = null): self
    {
        if ($this->type === "PE") {
            $this->cql_filter = "date_debut_mpp <= '".date("Y-m-d")."T23:59:59Z' AND date_fin_mpp >= '".date(
                    "Y-m-d"
                )."T00:00:00Z' AND date_debut_mpp >= '".date(
                    "Y-m-d",
                    strtotime("-40 days")
                )."T10:00:00Z' AND date_fin_mpp <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
        } else {
            $this->cql_filter = "datedebutmpp <= '".date("Y-m-d")."T23:59:59Z' AND datefinmpp >= '".date(
                    "Y-m-d"
                )."T00:00:00Z' AND datedebutmpp >= '".date(
                    "Y-m-d",
                    strtotime("-40 days")
                )."T10:00:00Z' AND datefinmpp <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
        }

        return $this;
    }

    public function getResults(int $max_count = 1000, array $property_names = []): self
    {
        $properties = $this->setPropertyNames($property_names);

        $wfs = new WfsLayer($this->path, $this->layer);
        $this->results = $wfs->setCqlFilter($this->cql_filter)
            ->setCount($max_count)
            ->setPropertyName($properties)
            ->getPropertiesArray(false);

        return $this;
    }

    public function first(): array
    {
        return $this->results[0] ?? [];
    }

    public function all(): array
    {
        return $this->results ?? [];
    }

    private function setPropertyNames(array $property_names = []): string
    {
        if (empty($property_names)) {
            if ($this->type === "PE") {
                $property_names = ['s_iddossier', 'ref_nova', 'streetname_fr', 'streetname_nl', 'number_from', 'number_to', 'zipcode', 'date_depot', 'date_debut_mpp', 'date_fin_mpp', 'date_notif_decision'];
            } else {
                $property_names = ['s_iddossier', 'refnova', 'streetnamefr', 'streetnamenl', 'numberpartfrom', 'numberpartto', 'zipcode', 'datedepot', 'datedebutmpp', 'datefinmpp', 'datenotifdecision'];
            }
        }

        return implode(",", $property_names);
    }
}