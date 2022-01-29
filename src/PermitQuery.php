<?php

namespace UrbanBrussels\NovaApi;

use DateTime;
use DateTimeZone;
use ici\ici_tools\WfsLayer;

class PermitQuery
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
    public PermitCollection $permits;

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

        $this->permits = new PermitCollection();
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

    public function getResults(): PermitCollection
    {
        $wfs = new WfsLayer($this->path, $this->layer);
        $wfs->setCqlFilter($this->cql_filter)
            ->setCount($this->limit);

        if(!empty($this->order)) {
           $wfs->setSortBy($this->order[0], $this->order[1]);
        }

        $results = $wfs->getPropertiesArray(false);

        foreach ($results as $result) {
            $permit = new Permit($result[$this->contextAttribute(Attribute::REFERENCE_NOVA)]);
            $permit->setLanguage($result[$this->contextAttribute(Attribute::LANGUAGE)]);
            $permit->setType($this->type);
            $permit->setSubtype($result[$this->contextAttribute(Attribute::SUBTYPE)]);
            $permit->setDateSubmission(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_SUBMISSION)]));
            $permit->setDateArc(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ARC)]));
            $permit->setDateAri(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ARI)]));
            $permit->setDateAdditionalElements(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ADDITIONAL_ELEMENTS)]));
            $permit->setDateCc(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_CC)]));
            $permit->setDateInquiryBegin(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_INQUIRY_BEGIN)]));
            $permit->setDateNotification(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_NOTIFICATION)]));
            $permit->setAreaTypology($this->defineAreaTypologyFromAttributes($result));
            $permit->setAdvices($this->defineAdviceInstances($result['avis_instances'] ?? null));
            $permit->setAddress($this->defineAddressFromAttributes($result));

            $this->permits->addPermit($permit);
        }

        return $this->permits;
    }

    public function first(): ?OldPermit
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


    private static function toDatetime(?string $date): ?DateTime {
        if (is_null($date)) {
            return null;
        }

        // Two formats needed because of inconsistencies in Nova data
        $date_time = DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new DateTimeZone('Europe/Brussels'));
        if(!$date_time) {
            $date_time = DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $date, new DateTimeZone('Europe/Brussels'));
        }
        return $date_time;
    }

    private function defineAreaTypologyFromAttributes(array $attributes): array
    {
        if ($this->type === "PE") {
            return [];
        }
        $typology = [];

        foreach ($attributes as $k => $v) {
            if (!is_null($v) && (str_contains($k, 'autorized') || str_contains($k, 'existing') || str_contains(
                        $k,
                        'projected'
                    ))) {
                $type = str_replace(['autorized', 'existing', 'projected'], '', $k);
                $subtype = str_replace($type, '', $k);
                $typology[$type][$subtype] = $v;
            }
        }

        return $typology;
    }

    private function defineAdviceInstances(?string $advice_instances): array
    {
        $json_advices = $advice_instances ?? null;
        if (is_null($json_advices)) {
            return [];
        }
        $json_advices = json_decode($json_advices, true, 512, JSON_THROW_ON_ERROR);

        $instances['fr'] = [];
        $instances['nl'] = [];

        foreach ($json_advices['data']['Case_Advice_Instance_List']['elements'] as $instance) {
            $instances['fr'][] = $instance['translations'][0]['label'];
            $instances['nl'][] = $instance['translations'][1]['label'];
        }

        return $instances;
    }

    public function defineAddressFromAttributes(array $attributes): array
    {
        $address['streetname']['fr'] = $this->fromArray($this->contextAttribute(Attribute::STREET_NAME_FR));
        $address['streetname']['nl'] = $this->fromArray($this->contextAttribute(Attribute::STREET_NAME_NL));
        if (empty($address['streetname']['fr'])) {
            $address['streetname']['fr'] = null;
        }
        if (empty($address['streetname']['nl'])) {
            $address['streetname']['nl'] = null;
        }

        $address['number']['from'] = $this->fromArray($this->contextAttribute(Attribute::STREET_NUMBER_FROM));
        $address['number']['to'] = $this->fromArray($this->contextAttribute(Attribute::STREET_NUMBER_TO));

        if (empty($address['number']['from'])) {
            $address['number']['from'] = null;
        }
        if (empty($address['number']['to'])) {
            $address['number']['to'] = null;
        }

        $address['number']['full'] = (!is_null($address['number']['from']) && !is_null(
                $address['number']['to']
            )) ? $address['number']['from'].'-'.$address['number']['to'] : $address['number']['from'];

        $address['municipality']['fr'] = $this->fromArray($this->contextAttribute(Attribute::MUNICIPALITY_FR));
        $address['municipality']['nl'] = $this->fromArray($this->contextAttribute(Attribute::MUNICIPALITY_NL));

        $address['zipcode'] = (int)$this->fromArray('zipcode');

        return $address;
    }

    private function fromArray(string $attribute)
    {
        return $this->attributes_array[$attribute] ?? null;
    }
}