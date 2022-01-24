<?php

namespace UrbanBrussels\NovaApi;

use ici\ici_tools\WfsLayer;

class PermitList
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
    private int $limit = 1000;
    private array $order;

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

    public function filterByIncidence(?int $year = null): self
    {
        if ($this->type === "PE") {
            $filter = '(rapport_incidence=true or etude_incidence=true)';
            if (!is_null($year)) {
                $filter .= " and date_debut_mpp >= '".$year."-01-01' and date_debut_mpp <= '".$year."-12-31'";
            }
        } else {
            $filter = '(ri=true or ei=true)';
            if (!is_null($year)) {
                $filter .= " and datedebutmpp >= '".$year."-01-01' and datedebutmpp <= '".$year."-12-31'";
            }
        }

        $this->cql_filter = $filter;

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

    public function filterByRawCQL(string $cql_filter): self
    {
        $this->cql_filter = $cql_filter;

        return $this;
    }

    public function filterByReferences(array $references, Attribute $attribute): self
    {
        $this->cql_filter = $this->contextAttribute($attribute) . " IN ('" . implode("','", $references)."')";

        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOrder(Attribute $attribute, Order $order = Order::DESC): self
    {
        $this->order = [$this->contextAttribute($attribute), $order->wfs()];
        return $this;
    }

    public function getResults(): self
    {
        $wfs = new WfsLayer($this->path, $this->layer);
        $wfs->setCqlFilter($this->cql_filter)
            ->setCount($this->limit);

        if(!empty($this->order)) {
           $wfs->setSortBy($this->order[0], $this->order[1]);
        }

        $results = $wfs->getPropertiesArray(false);

        $list = [];

        foreach ($results as $result) {
            $list[] = new Permit(($result['ref_nova'] ?? $result['refnova']), $result);
        }

        $this->results = $list;

        return $this;
    }

    public function first(): ?Permit
    {
        return $this->results[0] ?? null;
    }

    public function all(): array
    {
        return $this->results;
    }


    public function count(): int
    {
        return count($this->results) ?? 0;
    }

    private function contextAttribute(Attribute $attribute): string
    {
        return $this->type === "PU" ? $attribute->pu() : $attribute->pe();
    }

}