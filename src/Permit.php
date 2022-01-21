<?php

namespace UrbanBrussels\NovaApi;

use DateTime;
use DateTimeZone;
use ici\ici_tools\WfsLayer;

class Permit
{
    public string $reference_nova;
    public string $type;
    public array $subtype;
    public array $object;
    private ?array $attributes_array;
    public array $source;
    public bool $validation;
    public ?DateTime $date_inquiry_begin;
    public ?DateTime $date_inquiry_end;
    public bool $inquiry_active;
    public array $advices;
    public array $references;
    public ?string $language;
    public array $address;
    public array $area_typology;
    public ?DateTime $date_arc;
    public ?DateTime $date_ari;
    public ?DateTime $date_submission;
    public ?DateTime $date_cc;
    public ?DateTime $date_notification;
    public ?DateTime $date_additional_elements;
    public array $links;
    public ?string $status;
    public ?string $authority;
    public array $errors;
    public ?int $charges;
    public array $suspensions;

    public function __construct(string $refnova, array $attributes_array = [])
    {
        $this->reference_nova = strtoupper(trim($refnova));
        $this->type = $this->setType();
        $this->source = $this->setSource();
        if(!empty($attributes_array)) {
            $this->attributes_array = $attributes_array;
        }
        else {
            $this->attributes_array = $this->setAttributesArray();
        }
        $this->setAttributes();

        if ($this->getValidation() === false) {
            throw new \UnexpectedValueException('This class only accepts valid Nova references. Input was: '.$refnova);
        }
    }

    private function setAttributes(): void
    {
        $this->validation = $this->setValidation();
        if ($this->validation === false) {
            return;
        }

        $this->setInquiry();
        $this->advices = $this->setAdvices();
        $this->references = $this->setReferences();
        $this->subtype = $this->setSubtype();
        $this->object = $this->setObject();
        $this->date_arc = $this->setDateArc();
        $this->date_ari = $this->setDateAri();
        $this->date_submission = $this->setDateSubmission();
        $this->date_notification = $this->setDateNotification();
        $this->date_cc = $this->setDateCc();
        $this->date_additional_elements = $this->setDateAdditionalElements();
        $this->language = $this->setLanguage();
        $this->address = $this->setAddress();
        $this->area_typology = $this->setAreaTypology();
        $this->links = $this->setLinks();
        $this->status = $this->setStatus();
        $this->authority = $this->setAuthority();
        $this->charges = $this->setCharges();
        $this->suspensions = $this->setSuspensions();
        $this->errors = $this->setErrors();

        unset($this->attributes_array);
    }

    private function setValidation(): bool
    {
        return !($this->attributes_array === null);
    }

    private function setInquiry(): void
    {
        $this->date_inquiry_begin = $this->setDateInquiryBegin();
        $this->date_inquiry_end = $this->setDateInquiryEnd();

        $now = new DateTime();
        if ($this->date_inquiry_end > $now && $this->date_inquiry_begin < $now) {
            $this->inquiry_active = true;
        } else {
            $this->inquiry_active = false;
        }
    }

    private function setDateInquiryBegin(): DateTime|null|bool
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_INQUIRY_BEGIN));
        return self::toDatetime($date);
    }

    private function setDateInquiryEnd(): DateTime|null|bool
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_INQUIRY_END));
        return self::toDatetime($date);
    }

    private function setAdviceInstances(): array
    {
        $json_advices = $this->fromArray('avis_instances');
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

    private function setReferences(): array
    {
        $references['uuid'] = $this->fromArray('uuid');
        $references['reference_dossier'] = $this->fromArray($this->contextAttribute(Attribute::REFERENCE_DOSSIER));
        $references['reference_municipality'] = $this->fromArray($this->contextAttribute(Attribute::REFERENCE_MUNICIPALITY));
        $references['reference_mixed_permit'] = $this->fromArray('ref_mixed_permit') ?? $this->fromArray('refmixedpermit');

        return $references;
    }

    private function setCharges(): ?int
    {
        return $this->fromArray('deliveredpermittotalcharge');
    }

    private function setLinks(): array
    {
        $links['openpermits']['fr'] = 'https://openpermits.brussels/fr/_'.$this->reference_nova;
        $links['openpermits']['nl'] = 'https://openpermits.brussels/nl/_'.$this->reference_nova;
        $links['nova'] = 'https://nova.brussels/nova-ui/page/open/request/AcmDisplayCase.xhtml?ids=&id='.$this->references['reference_dossier'].'&uniqueCase=true';
        $links['nova_api'] = $this->source['query_url'];

        return $links;
    }

    private function setType(): string
    {
        if (str_contains($this->reference_nova, 'IPE')
            || str_contains($this->reference_nova, 'CL')
            || str_contains($this->reference_nova, 'IRCE')
            || str_contains($this->reference_nova, 'ICE')
            || str_contains($this->reference_nova, 'C_')
            || str_contains($this->reference_nova, 'PLP')
            || str_contains($this->reference_nova, 'IRPE')) {
            return "PE";
        }

        return "PU";
    }

    private function setStatus(): ?string
    {
        $status_fr = $this->fromArray('statutpermisfr');
        $final_state = $this->fromArray('statut_dossier') ?? $this->fromArray('etatfinal');

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

    private function setSubtype(): array
    {
        $subtype['code'] = $this->fromArray('case_subtype') ?? $this->fromArray('typedossier');
        $subtype['fr'] = $this->fromArray('case_subtype_fr') ?? $this->fromArray('typedossierfr');
        $subtype['nl'] = $this->fromArray('case_subtype_nl') ?? $this->fromArray('typedossiernl');

        return $subtype;
    }

    private function setAuthority(): ?string
    {
        $subtype = $this->getSubtype()['code'];
        if (in_array(
            $subtype,
            ["PFD", "PFU", "SFD", "ECO", "SOC", "CPFD", "GOU_PU", "LPFD", "LPFU", "CPFU", "LCFU", "LSFD"]
        )) {
            return "region";
        }

        if (!is_null($subtype)) {
            return "municipality";
        }

        return null;
    }

    private function setLanguage(): ?string
    {
        return $this->contextAttribute(Attribute::LANGUAGE);
    }

    private function setObject(): array
    {
        $object['fr']['standard'] = $this->fromArray('object_fr') ?? $this->fromArray('objectfr');
        $object['nl']['standard'] = $this->fromArray('object_nl') ?? $this->fromArray('objectnl');

        $object['fr']['real'] = $this->fromArray($this->contextAttribute(Attribute::OBJECT_FR));
        $object['nl']['real'] = $this->fromArray($this->contextAttribute(Attribute::OBJECT_NL));

        return $object;
    }

    private function setAreaTypology(): array
    {
        if ($this->type === "PE") {
            return [];
        }
        $typology = [];

        foreach ($this->attributes_array as $k => $v) {
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

    private function setSuspensions(): array
    {
        $suspensions = [];

        $json_suspensions = $this->fromArray('suspensions');
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

    private function setDateArc(): ?DateTime
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_ARC));
        return self::toDatetime($date);
    }

    private function setDateAri(): ?DateTime
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_ARI));
        if (is_null($date)) {
            return null;
        }
        return self::toDatetime($date);
    }

    private function setDateSubmission(): ?DateTime
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_SUBMISSION));
        return self::toDatetime($date);
    }

    private function setDateCc(): ?DateTime
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_CC));
        return self::toDatetime($date);
    }

    private function setDateAdditionalElements(): ?DateTime
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_ADDITIONAL_ELEMENTS)); // Todo: check for PE
        return self::toDatetime($date);
    }

    private function setDateNotification(): ?DateTime
    {
        $date = $this->fromArray($this->contextAttribute(Attribute::DATE_NOTIFICATION));
        return self::toDatetime($date);
    }

    public function setAttributesArray(): ?array
    {
        $wfs = new WfsLayer($this->source['base_path'], $this->source['layer_name']);
        $permits = $wfs->setCqlFilter($this->contextAttribute(Attribute::REFERENCE_NOVA).'=\''.$this->reference_nova.'\'')
            ->setCount(1)
            ->setOutputSrs(4326)
            ->getPropertiesArray(true);

        return $permits[0] ?? null;
    }

    public function setAddress(): array
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

    private function setAdvices(): array
    {
        $advices = [];

        $advices['college'] = $this->fromArray('avis_cbe') ?? $this->fromArray('cbe');
        $advices['cc'] = $this->fromArray('avis_cc') ?? $this->fromArray('aviscc');
        $advices['fd'] = $this->fromArray('avis_fd') ?? $this->fromArray('avisfd');

        $advices['instances'] = $this->setAdviceInstances();

        return $advices;
    }

    public function setErrors(): array
    {
        $errors = [];
        $now = new DateTime();
        $older_date = new DateTime('1800-01-01');

        if ($this->getDateSubmission() > $now) {
            $errors[] = 'Submission date should not be in the future';
        }

        if ($this->getDateSubmission() < $older_date) {
            $errors[] = 'Submission date is too old';
        }

        if (is_null($this->getDateSubmission())) {
            $errors[] = 'Submission date should not be null';
        }

        if (!is_null($this->getDateNotification()) && $this->getDateSubmission() > $this->getDateNotification()) {
            $errors[] = 'Notification date should not be anterior to Submission date';
        }

        if (!is_null($this->getDateCc()) && $this->getDateCc() < $this->getDateSubmission()) {
            $errors[] = 'Concertation date should not be anterior to Submission date';
        }

        if ($this->getDateInquiryBegin() > $this->getDateInquiryEnd()) {
            $errors[] = 'End of inquiry date should not be anterior to Begin of inquiry date';
        }

        if ($this->getAddress()['zipcode'] === "") {
            $errors[] = 'Zipcode should not be empty';
        }

        if ($this->getAddress()['streetname']['fr'] === "" || $this->getAddress()['streetname']['nl'] === "") {
            $errors[] = 'Streetname should not be empty in french or dutch';
        }

        return $errors;
    }

    public function setSource(): array
    {
        if ($this->type === 'PE') {
            $source = [
                'base_path' => 'https://geoservices-others.irisnet.be/geoserver/Nova/ows',
                'layer_name' => 'Nova:vm_nova_pe',
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=ref_nova=\''.$this->reference_nova.'\'';
        } else {
            $source = [
                'base_path' => 'https://geoservices-others.irisnet.be/geoserver/ows',
                'layer_name' => 'Nova:vmnovaurbanview',
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=refnova=\''.$this->reference_nova.'\'';
        }

        return $source;
    }

    public function getReferencenova(): string
    {
        return $this->reference_nova;
    }

    public function getSubtype(): array
    {
        return $this->subtype;
    }

    public function getObject(): array
    {
        return $this->object;
    }

    public function getAddress(): array
    {
        return $this->address;
    }

    public function getValidation(): bool
    {
        return $this->validation;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getDateInquiryBegin(): ?DateTime
    {
        return $this->date_inquiry_begin;
    }

    public function getDateInquiryEnd(): ?DateTime
    {
        return $this->date_inquiry_end;
    }

    public function getDateCc(): ?DateTime
    {
        return $this->date_cc;
    }

    public function getDateArc(): ?DateTime
    {
        return $this->date_arc;
    }

    public function getDateAri(): ?DateTime
    {
        return $this->date_ari;
    }

    public function getDateSubmission(): ?DateTime
    {
        return $this->date_submission;
    }

    public function getDateNotification(): ?DateTime
    {
        return $this->date_notification;
    }

    public function getInquiryActive(): bool
    {
        return $this->inquiry_active;
    }

    public function getAdvices(): array
    {
        return $this->advices;
    }

    public function getReferences(): array
    {
        return $this->references;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAreaTypology(): array
    {
        return $this->area_typology;
    }

    public function getLinks(): array
    {
        return $this->links;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getAuthority(): ?string
    {
        return $this->authority;
    }

    public function getErrors(): array
    {
        return $this->errors;
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

    private function contextAttribute(Attribute $attribute): string
    {
        return $this->type === "PU" ? $attribute->pu() : $attribute->pe();
    }

    private function fromArray(string $attribute)
    {
        return $this->attributes_array[$attribute] ?? null;
    }
}