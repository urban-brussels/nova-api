<?php

namespace UrbanBrussels\NovaApi;

use ici\ici_tools\WfsLayer;

class Permit
{
    public string $refnova;
    public string $type;
    public array $subtype;
    public array $object;
    private ?array $attributesArray;
    public array $source;
    public bool $validation;
    public ?\DateTime $date_inquiry_begin;
    public ?\DateTime $date_inquiry_end;
    public bool $inquiry_active;
    public array $advices;
    public array $references;
    public ?string $language;
    public array $address;
    public array $area_typology;
    public ?\DateTime $date_arc;
    public ?\DateTime $date_ari;
    public ?\DateTime $date_submission;
    public ?\DateTime $date_cc;
    public ?\DateTime $date_notification;

    public function __construct(string $refnova) {
        $this->refnova = strtoupper(trim($refnova));
        $this->type = $this->getType();
        $this->source = $this->getSource();
        $this->attributesArray = $this->getAttributesArray();
        $this->setAttributes();
    }

    private function setAttributes(): void
    {
        $this->validation = $this->setValidation();
        if($this->validation === false) { return; }

        $this->setInquiry();
        $this->advices = $this->setAdvices();
        $this->references = $this->setReferences();
        $this->subtype = $this->setSubtype();
        $this->object = $this->setObject();
        $this->date_arc = $this->setDateArc();
        $this->date_ari = $this->setDateAri();
        $this->date_submission = $this->setDateSubmission();
        $this->date_cc = $this->setDateCc();
        $this->language = $this->setLanguage();
        $this->address = $this->setAddress();
        $this->area_typology = $this->setAreaTypology();
    }

    private function setValidation(): bool {
        return !($this->attributesArray === null);
    }

    private function setInquiry(): void
    {
        $this->date_inquiry_begin = $this->setDateInquiryBegin();
        $this->date_inquiry_end = $this->setDateInquiryEnd();

        $now = new \DateTime();
        if($this->date_inquiry_end > $now && $this->date_inquiry_begin < $now) {
            $this->inquiry_active = true;
        }
        else {
            $this->inquiry_active = false;
        }
    }

    private function setDateInquiryBegin(): \DateTime|null|bool
    {
        $date = $this->attributesArray['date_debut_mpp'] ?? $this->attributesArray['datedebutmpp'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateInquiryEnd(): \DateTime|null|bool
    {
        $date = $this->attributesArray['date_fin_mpp'] ?? $this->attributesArray['datefinmpp'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setAdviceInstances(): array {
        $json_advices = $this->attributesArray['avis_instances'] ?? null;
        if(is_null($json_advices)) { return []; }
        $json_advices = json_decode($json_advices, true, 512, JSON_THROW_ON_ERROR);

        $instances['fr'] = [];
        $instances['nl'] = [];

        foreach($json_advices['data']['Case_Advice_Instance_List']['elements'] as $instance) {
            $instances['fr'][] = $instance['translations'][0]['label'];
            $instances['nl'][] = $instance['translations'][1]['label'];
        }

        return $instances;
    }

    private function setReferences(): array {
        $references['uuid'] = $this->attributesArray['uuid'] ?? null;
        $references['nova_seq'] = $this->attributesArray['nova_seq'] ?? $this->attributesArray['s_iddossier'] ?? null;
        $references['ref_com'] = $this->attributesArray['ref_com'] ?? $this->attributesArray['referencespecifique'] ?? null;
        $references['ref_mixed_permit'] = $this->attributesArray['ref_mixed_permit'] ?? $this->attributesArray['refmixedpermit'] ?? null;
        return $references;
    }

    private function setSubtype(): array {
        $subtype['code'] = $this->attributesArray['case_subtype'] ?? $this->attributesArray['typedossier'] ?? null;
        $subtype['fr'] = $this->attributesArray['case_subtype_fr'] ?? $this->attributesArray['typedossierfr'] ?? null;
        $subtype['nl'] = $this->attributesArray['case_subtype_nl'] ?? $this->attributesArray['typedossiernl'] ?? null;
        return $subtype;
    }

    private function setLanguage(): ?string {
        return $this->attributesArray['langue_demande'] ?? $this->attributesArray['languedemande'] ?? null;
    }

    private function setObject(): array {
        $object['fr']['standard'] = $this->attributesArray['object_fr'] ?? $this->attributesArray['objectfr'] ?? null;
        $object['nl']['standard'] = $this->attributesArray['object_nl'] ?? $this->attributesArray['objectnl'] ?? null;

        $object['fr']['real'] = $this->attributesArray['real_object_fr'] ?? $this->attributesArray['realobjectfr'] ?? null;
        $object['nl']['real'] = $this->attributesArray['real_object_nl'] ?? $this->attributesArray['realobjectnl'] ?? null;

        return $object;
    }

    private function setAreaTypology(): array
    {
        if($this->type === "PE") { return []; }
        $typology = [];

        foreach($this->attributesArray as $k => $v) {
            if(!is_null($v) && (str_contains($k, 'autorized') || str_contains($k, 'existing') || str_contains($k, 'projected'))) {
                $type = str_replace(['autorized', 'existing', 'projected'], '', $k);
                $subtype = str_replace($type, '', $k);
                $typology[$type][$subtype] = $v;
            }
        }
        return $typology;
    }

    private function setDateArc(): ?\DateTime
    {
        $date = $this->attributesArray['date_arc'] ?? $this->attributesArray['datearclast'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateAri(): ?\DateTime
    {
        $date = $this->attributesArray['date_ari'] ?? $this->attributesArray['datearilast'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateSubmission(): ?\DateTime
    {
        $date = $this->attributesArray['date_depot'] ?? $this->attributesArray['datedepot'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateCc(): ?\DateTime
    {
        $date = $this->attributesArray['date_cc'] ?? $this->attributesArray['datecc'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateNotification(): ?\DateTime
    {
        $date = $this->attributesArray['date_cc'] ?? $this->attributesArray['datenotifdecision'] ?? null;
        if(is_null($date)) { return null; }
        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    public function setAddress(): array
    {
        $address['streetname']['fr'] = $this->attributesArray['streetname_fr'] ?? $this->attributesArray['streetnamefr'] ?? null;
        $address['streetname']['nl'] = $this->attributesArray['streetname_nl'] ?? $this->attributesArray['streetnamenl'] ?? null;
        if($address['streetname']['fr'] === "") { $address['streetname']['fr'] = null; }
        if($address['streetname']['nl'] === "") { $address['streetname']['nl'] = null; }

        $address['number']['from'] = $this->attributesArray['number_from'] ?? $this->attributesArray['numberpartfrom'] ?? null;
        $address['number']['to'] = $this->attributesArray['number_to'] ?? $this->attributesArray['numberpartto'] ?? null;

        if($address['number']['from'] === "") { $address['number']['from'] = null; }
        if($address['number']['to'] === "") { $address['number']['to'] = null; }

        $address['number']['full'] = (!is_null($address['number']['from']) && !is_null($address['number']['to'])) ? $address['number']['from'].'-'.$address['number']['to'] : $address['number']['from'];

        $address['municipality']['fr'] = $this->attributesArray['municipality_fr'] ?? $this->attributesArray['municipalityfr'] ?? null;
        $address['municipality']['nl'] = $this->attributesArray['municipality_nl'] ?? $this->attributesArray['municipalitynl'] ?? null;

        $address['zipcode'] = (int)$this->attributesArray['zipcode'];

        return $address;
    }

    private function setAdvices(): array
    {
        $advices = [];

        $advices['college'] = $this->attributesArray['avis_cbe'] ?? $this->attributesArray['cbe'] ?? null;
        $advices['cc'] = $this->attributesArray['avis_cc'] ?? $this->attributesArray['aviscc'] ?? null;
        $advices['fd'] = $this->attributesArray['avis_fd'] ?? $this->attributesArray['avisfd'] ?? null;

        $advices['instances'] = $this->setAdviceInstances();

        return $advices;
    }


    public function getType(): string {
        if (str_contains($this->refnova, 'IPE')
            || str_contains($this->refnova, 'CL')
            || str_contains($this->refnova,'IRCE')
            || str_contains($this->refnova, 'ICE')
            || str_contains($this->refnova, 'C_')
            || str_contains($this->refnova, 'PLP')
            || str_contains($this->refnova, 'IRPE')) {
            return "PE";
        }

        return "PU";
    }

    private function getSource(): array {
        if ($this->type === 'PE') {
            $source = ['base_path' => 'https://geoservices-others.irisnet.be/geoserver/Nova/ows', 'layer_name' => 'Nova:vm_nova_pe', '' => ''];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=ref_nova=\''.$this->refnova.'\'';
        }
        else {
            $source = ['base_path' => 'https://geoservices-others.irisnet.be/geoserver/ows', 'layer_name' => 'Nova:vmnovaurbanview', 'query_url' => ''];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=refnova=\''.$this->refnova.'\'';
        }

        return $source;
    }

    public function getAttributesArray(): ?array {
        $wfs = new WfsLayer($this->source['base_path'], $this->source['layer_name']);
        $permits = $wfs->setCqlFilter(($this->type === 'PE' ? 'ref_nova' : 'refnova').'=\''.$this->refnova.'\'')
            ->setCount(1)
            ->setOutputSrs(4326)
            ->getPropertiesArray(true);

        return $permits[0] ?? null;
    }

    public function getAddress(): array {
      return $this->address;
    }
}
