<?php

namespace UrbanBrussels\NovaApi;

use DateTime;
use DateTimeZone;
use ici\ici_tools\WfsLayer;

class PermitCollection implements \Iterator
{
    private PermitQuery $permit_query;
    public array $permits;
    protected int $position = 0;
    public string $query_url;

    public function __construct(PermitQuery $permit_query)
    {
        $this->permit_query = $permit_query;
        $this->permits = [];
        $this->getResults();
    }

    public function addPermit(Permit $permit): void
    {
        $this->permits[] = $permit;
    }

    public function removePermit(Permit $permit): void
    {
        $key = array_search($permit, $this->permits, true);
        unset($this->permits[$key]);
    }

    public function getPermits(): array
    {
        return $this->permits;
    }

    public function current(): int
    {
        return $this->permits[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->medias[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @throws \JsonException
     */
    public function getResults(): self
    {
        $wfs = new WfsLayer($this->permit_query->path, $this->permit_query->layer);
        $wfs->setCqlFilter($this->permit_query->cql_filter)
            ->setCount($this->permit_query->limit);

        if(!empty($this->order)) {
            $wfs->setSortBy($this->order[0], $this->order[1]);
        }
        $this->query_url = $wfs->getQueryUrl();
        $results = $wfs->getPropertiesArray(false);

        foreach ($results as $result) {
            $permit = new Permit($result[$this->permit_query->contextAttribute(Attribute::REFERENCE_NOVA)]);
            $permit->setLanguage($result[$this->permit_query->contextAttribute(Attribute::LANGUAGE)]);
            $permit->setType($this->permit_query->type);
            $permit->setSubtype($result[$this->permit_query->contextAttribute(Attribute::SUBTYPE)]);
            $permit->setDateSubmission($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_SUBMISSION)]));
            $permit->setDateArc($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_ARC)]));
            $permit->setDateAri($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_ARI)]));
            $permit->setDateAdditionalElements($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_ADDITIONAL_ELEMENTS)]));
            $permit->setDateCc($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_CC)]));
            $permit->setDateInquiryBegin($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_INQUIRY_BEGIN)]));
            $permit->setDateInquiryEnd($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_INQUIRY_END)]));
            $permit->setDateNotification($this->permit_query::toDatetime($result[$this->permit_query->contextAttribute(Attribute::DATE_NOTIFICATION)]));
            $permit->setAreaTypology($this->defineAreaTypologyFromAttributes($result));
            $permit->setAdvices($this->defineAdvicesFromAttributes($result));
            $permit->setAddress($this->defineAddressFromAttributes($result));
            $permit->setSource($this->defineSource($permit->getReferenceNova()));
            $permit->setSuspensions($this->defineSuspensions($result['suspensions'] ?? null));
            $permit->setUuid($result['uuid']);
            $permit->setReferenceFile($result[$this->permit_query->contextAttribute(Attribute::REFERENCE_FILE)]);
            $permit->setReferenceMunicipality($result[$this->permit_query->contextAttribute(Attribute::REFERENCE_MUNICIPALITY)]);
            $permit->setReferenceMixedPermit($this->defineReferenceMixedPermit($result[$this->permit_query->contextAttribute(Attribute::IS_MIXED)], $result[$this->permit_query->contextAttribute(Attribute::REFERENCE_MIXED_PERMIT)]));
            $permit->setCharges($result['deliveredpermittotalcharge'] ?? null);
            $permit->setObject($this->defineObjectFromAttributes($result));
            $permit->setStatus($this->defineStatusFromAttributes($result));
            $permit->setQueryUrl($this->definePermitQueryUrl($permit->getReferenceNova()));
            $permit->setSubmissionType($result[$this->permit_query->contextAttribute(Attribute::SUBMISSION_TYPE)]);

            $this->addPermit($permit);
        }

        return $this;
    }


    private function defineAreaTypologyFromAttributes(array $attributes): array
    {
        if ($this->permit_query->type === "PE") {
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
                if($subtype === "autorized") { $subtype = "authorized"; }
                $typology[$type][$subtype] = $v;
            }
        }

        return $typology;
    }

    /**
     * @throws \JsonException
     */
    private function defineAdvicesFromAttributes(array $attributes): array
    {
        $advices = [];

        $advices['college'] = $attributes['avis_cbe'] ?? $attributes['aviscbe'];
        $advices['cc'] = $attributes['avis_cc'] ?? $attributes['aviscc'];
        $advices['fd'] = $attributes['avis_fd'] ?? $attributes['avisfd'];


        $json_advices = $attributes['avis_instances'] ?? null;
        if (is_null($json_advices)) {
            return $advices;
        }
        $json_advices = json_decode($json_advices, true, 512, JSON_THROW_ON_ERROR);

        $instances['fr'] = [];
        $instances['nl'] = [];

        foreach ($json_advices['data']['Case_Advice_Instance_List']['elements'] as $instance) {
            $instances['fr'][] = $instance['translations'][0]['label'];
            $instances['nl'][] = $instance['translations'][1]['label'];
        }

        return $advices['instances'] = $instances;
    }

    public function defineAddressFromAttributes(array $attributes): array
    {
        $address['streetname']['fr'] = $attributes[$this->permit_query->contextAttribute(Attribute::STREET_NAME_FR)] ?? null;
        $address['streetname']['nl'] = $attributes[$this->permit_query->contextAttribute(Attribute::STREET_NAME_NL)] ?? null;
        if (empty($address['streetname']['fr'])) {
            $address['streetname']['fr'] = null;
        }
        if (empty($address['streetname']['nl'])) {
            $address['streetname']['nl'] = null;
        }

        $address['number']['from'] = $attributes[$this->permit_query->contextAttribute(Attribute::STREET_NUMBER_FROM)] ?? null;
        $address['number']['to'] = $attributes[$this->permit_query->contextAttribute(Attribute::STREET_NUMBER_TO)] ?? null;

        if (empty($address['number']['from'])) {
            $address['number']['from'] = null;
        }
        if (empty($address['number']['to'])) {
            $address['number']['to'] = null;
        }

        $address['number']['full'] = (!is_null($address['number']['from']) && !is_null(
                $address['number']['to']
            )) ? $address['number']['from'].'-'.$address['number']['to'] : $address['number']['from'];

        $address['municipality']['fr'] = $attributes[$this->permit_query->contextAttribute(Attribute::MUNICIPALITY_FR)];
        $address['municipality']['nl'] = $attributes[$this->permit_query->contextAttribute(Attribute::MUNICIPALITY_NL)];

        $address['zipcode'] = (int)$attributes['zipcode'];

        return $address;
    }


    public function defineSource($reference_nova): array
    {
        if ($this->permit_query->type === 'PE') {
            $source = [
                'base_path' => $this->permit_query::PE_PATH,
                'layer_name' => $this->permit_query::PE_LAYER_NAME,
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=ref_nova=\''.$reference_nova.'\'';
        } else {
            $source = [
                'base_path' => $this->permit_query::PU_PATH,
                'layer_name' => $this->permit_query::PU_LAYER_NAME,
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=refnova=\''.$reference_nova.'\'';
        }

        return $source;
    }


    private function defineSuspensions(?string $json_suspensions): array
    {
        $suspensions = [];

        if(is_null($json_suspensions)) {
            return $suspensions;
        }

        $array_suspensions = json_decode($json_suspensions, true);

        foreach($array_suspensions as $suspension) {
            $suspensions['fr'] = $suspension[0]['suspension']['motif-fr'];
            $suspensions['nl'] = $suspension[0]['suspension']['motif-nl'];
            $suspensions['from'] = DateTime::createFromFormat('Y-m-d', $suspension[0]['suspension']['date-from'], new DateTimeZone('Europe/Brussels'));
            $suspensions['to'] = DateTime::createFromFormat('Y-m-d', $suspension[0]['suspension']['date-to'], new DateTimeZone('Europe/Brussels'));
        }

        return $suspensions;
    }

    private function defineObjectFromAttributes(array $attributes): array
    {
        $object['fr']['standard'] = $attributes[$this->permit_query->contextAttribute(Attribute::OBJECT_STANDARD_FR)] ?? null;
        $object['nl']['standard'] = $attributes[$this->permit_query->contextAttribute(Attribute::OBJECT_STANDARD_NL)] ?? null;

        $object['fr']['real'] = $attributes[$this->permit_query->contextAttribute(Attribute::OBJECT_REAL_FR)] ?? null;
        $object['nl']['real'] = $attributes[$this->permit_query->contextAttribute(Attribute::OBJECT_REAL_NL)] ?? null;

        return $object;
    }


    private function defineStatusFromAttributes(array $attributes): ?string
    {
        $status_fr = $attributes['statutpermisfr'] ?? null;
        $final_state = $attributes['statut_dossier'] ?? $attributes['etatfinal'] ?? null;

        if ($status_fr === "Annulé") {
            return 'canceled';
        }

        if ($final_state === "R") {
            return 'appeal';
        }

        if ($final_state === "S") {
            return 'suspended';
        }

        if ($final_state === "I") {
            return 'instruction';
        }

        if ($final_state === "V") {
            return 'delivered';
        }

        if ($final_state === "NV") {
            return 'refused';
        }

        return null;
    }

    private function definePermitQueryUrl(string $reference_nova): string
    {
        $wfs = new WfsLayer($this->permit_query->path, $this->permit_query->layer);
        $wfs->setCqlFilter($this->permit_query->contextAttribute(Attribute::REFERENCE_NOVA)."='".$reference_nova."'")
            ->setCount(1);

        return $wfs->getQueryUrl();
    }

    private function defineReferenceMixedPermit(bool $is_mixed, ?string $mixed_reference): ?string
    {
        if($is_mixed === false) {
            return null;
        }
            return $mixed_reference ?? ''; // Return empty reference to make the distinction with non mixed permits
    }
}