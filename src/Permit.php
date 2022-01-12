<?php

namespace UrbanBrussels\NovaApi;

use ici\ici_tools\WfsLayer;

class Permit
{
    public string $refnova;
    public string $type;
    public array $subtype;
    public array $object;
    private ?array $attributes_array;
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
    public array $links;

    public function __construct(string $refnova)
    {
        $this->refnova = strtoupper(trim($refnova));
        $this->type = $this->setType();
        $this->source = $this->getSource();
        $this->attributes_array = $this->getAttributesArray();
        $this->setAttributes();
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
        $this->language = $this->setLanguage();
        $this->address = $this->setAddress();
        $this->area_typology = $this->setAreaTypology();
    }

    private function setValidation(): bool
    {
        return !($this->attributes_array === null);
    }

    private function setInquiry(): void
    {
        $this->date_inquiry_begin = $this->setDateInquiryBegin();
        $this->date_inquiry_end = $this->setDateInquiryEnd();

        $now = new \DateTime();
        if ($this->date_inquiry_end > $now && $this->date_inquiry_begin < $now) {
            $this->inquiry_active = true;
        } else {
            $this->inquiry_active = false;
        }
    }

    private function setDateInquiryBegin(): \DateTime|null|bool
    {
        $date = $this->attributes_array['date_debut_mpp'] ?? $this->attributes_array['datedebutmpp'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateInquiryEnd(): \DateTime|null|bool
    {
        $date = $this->attributes_array['date_fin_mpp'] ?? $this->attributes_array['datefinmpp'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setAdviceInstances(): array
    {
        $json_advices = $this->attributes_array['avis_instances'] ?? null;
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
        $references['uuid'] = $this->attributes_array['uuid'] ?? null;
        $references['nova_seq'] = $this->attributes_array['nova_seq'] ?? $this->attributes_array['s_iddossier'] ?? null;
        $references['ref_com'] = $this->attributes_array['ref_com'] ?? $this->attributes_array['referencespecifique'] ?? null;
        $references['ref_mixed_permit'] = $this->attributes_array['ref_mixed_permit'] ?? $this->attributes_array['refmixedpermit'] ?? null;

        return $references;
    }

    private function setLinks(): array
    {
        $links['openpermits']['fr'] = 'https://openpermits.brussels/fr/_'.$this->refnova;
        $links['openpermits']['nl'] = 'https://openpermits.brussels/nl/_'.$this->refnova;
        $link['nova'] = 'https://nova.brussels/nova-ui/page/open/request/AcmDisplayCase.xhtml?ids=&id='.$this->references['nova_seq'].'&uniqueCase=true';
        $link['nova_api'] = $this->source['query_url'];

        return $links;
    }

    private function setType(): string
    {
        if (str_contains($this->refnova, 'IPE')
            || str_contains($this->refnova, 'CL')
            || str_contains($this->refnova, 'IRCE')
            || str_contains($this->refnova, 'ICE')
            || str_contains($this->refnova, 'C_')
            || str_contains($this->refnova, 'PLP')
            || str_contains($this->refnova, 'IRPE')) {
            return "PE";
        }
        return "PU";
    }

    private function setSubtype(): array
    {
        $subtype['code'] = $this->attributes_array['case_subtype'] ?? $this->attributes_array['typedossier'] ?? null;
        $subtype['fr'] = $this->attributes_array['case_subtype_fr'] ?? $this->attributes_array['typedossierfr'] ?? null;
        $subtype['nl'] = $this->attributes_array['case_subtype_nl'] ?? $this->attributes_array['typedossiernl'] ?? null;

        return $subtype;
    }

    private function setLanguage(): ?string
    {
        return $this->attributes_array['langue_demande'] ?? $this->attributes_array['languedemande'] ?? null;
    }

    private function setObject(): array
    {
        $object['fr']['standard'] = $this->attributes_array['object_fr'] ?? $this->attributes_array['objectfr'] ?? null;
        $object['nl']['standard'] = $this->attributes_array['object_nl'] ?? $this->attributes_array['objectnl'] ?? null;

        $object['fr']['real'] = $this->attributes_array['real_object_fr'] ?? $this->attributes_array['realobjectfr'] ?? null;
        $object['nl']['real'] = $this->attributes_array['real_object_nl'] ?? $this->attributes_array['realobjectnl'] ?? null;

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

    private function setDateArc(): ?\DateTime
    {
        $date = $this->attributes_array['date_arc'] ?? $this->attributes_array['datearclast'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateAri(): ?\DateTime
    {
        $date = $this->attributes_array['date_ari'] ?? $this->attributes_array['datearilast'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateSubmission(): ?\DateTime
    {
        $date = $this->attributes_array['date_depot'] ?? $this->attributes_array['datedepot'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateCc(): ?\DateTime
    {
        $date = $this->attributes_array['date_cc'] ?? $this->attributes_array['datecc'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    private function setDateNotification(): ?\DateTime
    {
        $date = $this->attributes_array['date_notif_decision'] ?? $this->attributes_array['datenotifdecision'] ?? null;
        if (is_null($date)) {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date, new \DateTimeZone('Europe/Brussels'));
    }

    public function setAddress(): array
    {
        $address['streetname']['fr'] = $this->attributes_array['streetname_fr'] ?? $this->attributes_array['streetnamefr'] ?? null;
        $address['streetname']['nl'] = $this->attributes_array['streetname_nl'] ?? $this->attributes_array['streetnamenl'] ?? null;
        if ($address['streetname']['fr'] === "") {
            $address['streetname']['fr'] = null;
        }
        if ($address['streetname']['nl'] === "") {
            $address['streetname']['nl'] = null;
        }

        $address['number']['from'] = $this->attributes_array['number_from'] ?? $this->attributes_array['numberpartfrom'] ?? null;
        $address['number']['to'] = $this->attributes_array['number_to'] ?? $this->attributes_array['numberpartto'] ?? null;

        if ($address['number']['from'] === "") {
            $address['number']['from'] = null;
        }
        if ($address['number']['to'] === "") {
            $address['number']['to'] = null;
        }

        $address['number']['full'] = (!is_null($address['number']['from']) && !is_null(
                $address['number']['to']
            )) ? $address['number']['from'].'-'.$address['number']['to'] : $address['number']['from'];

        $address['municipality']['fr'] = $this->attributes_array['municipality_fr'] ?? $this->attributes_array['municipalityfr'] ?? null;
        $address['municipality']['nl'] = $this->attributes_array['municipality_nl'] ?? $this->attributes_array['municipalitynl'] ?? null;

        $address['zipcode'] = (int)$this->attributes_array['zipcode'];

        return $address;
    }

    private function setAdvices(): array
    {
        $advices = [];

        $advices['college'] = $this->attributes_array['avis_cbe'] ?? $this->attributes_array['cbe'] ?? null;
        $advices['cc'] = $this->attributes_array['avis_cc'] ?? $this->attributes_array['aviscc'] ?? null;
        $advices['fd'] = $this->attributes_array['avis_fd'] ?? $this->attributes_array['avisfd'] ?? null;

        $advices['instances'] = $this->setAdviceInstances();

        return $advices;
    }

    public function getSource(): array
    {
        if ($this->type === 'PE') {
            $source = [
                'base_path' => 'https://geoservices-others.irisnet.be/geoserver/Nova/ows',
                'layer_name' => 'Nova:vm_nova_pe',
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=ref_nova=\''.$this->refnova.'\'';
        } else {
            $source = [
                'base_path' => 'https://geoservices-others.irisnet.be/geoserver/ows',
                'layer_name' => 'Nova:vmnovaurbanview',
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=refnova=\''.$this->refnova.'\'';
        }

        return $source;
    }

    public function getAttributesArray(): ?array
    {
        $wfs = new WfsLayer($this->source['base_path'], $this->source['layer_name']);
        $permits = $wfs->setCqlFilter(($this->type === 'PE' ? 'ref_nova' : 'refnova').'=\''.$this->refnova.'\'')
            ->setCount(1)
            ->setOutputSrs(4326)
            ->getPropertiesArray(true);

        return $permits[0] ?? null;
    }

    public function getRefnova(): string
    {
        return $this->refnova;
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

    public function getDateInquiryBegin(): ?\DateTime
    {
        return $this->date_inquiry_begin;
    }

    public function getDateInquiryEnd(): ?\DateTime
    {
        return $this->date_inquiry_end;
    }

    public function getDateCc(): ?\DateTime
    {
        return $this->date_cc;
    }

    public function getDateArc(): ?\DateTime
    {
        return $this->date_arc;
    }

    public function getDateAri(): ?\DateTime
    {
        return $this->date_ari;
    }

    public function getDateSubmission(): ?\DateTime
    {
        return $this->date_submission;
    }

    public function getDateNotification(): ?\DateTime
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
}
