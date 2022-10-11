<?php

namespace UrbanBrussels\NovaApi;

use DateTime;
use DateTimeZone;
use ici\ici_tools\WfsLayer;

class PermitQuery
{
    public const PU_PATH = 'https://geoservices-others.irisnet.be/geoserver/Nova/ows';
    public const PE_PATH = self::PU_PATH;
    public const PU_LAYER_NAME = 'Nova:vmnovaurbanview';
    public const PE_LAYER_NAME = 'Nova:vm_nova_pe';

    public string $path;
    public string $layer;
    public array $cql_filter;
    public string $type;
    public int $limit = 1000;
    public array $order;
    public string $query_url;

    public PermitCollection $permit_collection;

    public function __construct(string $type)
    {
        $this->type = $type;
        $this->permit_collection = new PermitCollection();
        if ($this->type === "PE") {
            $this->path = self::PE_PATH;
            $this->layer = self::PE_LAYER_NAME;
        } else {
            $this->path = self::PU_PATH;
            $this->layer = self::PU_LAYER_NAME;
        }
        $this->cql_filter = [];
    }

    public function filterById(int $id): self
    {
        $id_dossier = ($this->type === "PE") ? 'nova_seq' : 's_iddossier';
        $this->cql_filter[] = $id_dossier.'='.$id;

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

        $this->cql_filter[] = $filter;

        return $this;
    }

    public function filterByInquiryDate(string $date = null): self
    {
        if ($this->type === "PE") {
            $filter = "date_debut_mpp <= '".date("Y-m-d")."T23:59:59Z' AND date_fin_mpp >= '".date(
                    "Y-m-d"
                )."T00:00:00Z' AND date_debut_mpp >= '".date(
                    "Y-m-d",
                    strtotime("-40 days")
                )."T10:00:00Z' AND date_fin_mpp <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
        } else {
            $filter = "datedebutmpp <= '".date("Y-m-d")."T23:59:59Z' AND datefinmpp >= '".date(
                    "Y-m-d"
                )."T00:00:00Z' AND datedebutmpp >= '".date(
                    "Y-m-d",
                    strtotime("-40 days")
                )."T10:00:00Z' AND datefinmpp <= '".date("Y-m-d", strtotime("40 days"))."T10:00:00Z'";
        }

        $this->cql_filter[] = $filter;

        return $this;
    }

    public function filterByRawCQL(string $filter): self
    {
        $this->cql_filter[] = $filter;

        return $this;
    }

    public function filterByAttribute(Attribute $attribute, $value): self
    {
        $this->cql_filter[] = $this->contextAttribute($attribute) . " = '". $value."'";

        return $this;
    }

    public function filterByAttributeArray(Attribute $attribute, array $values): self
    {
        $this->cql_filter[] = $this->contextAttribute($attribute) . " IN ('" . implode("','", $values)."')";

        return $this;
    }

    public function filterByDataError(): self
    {
        if ($this->type === "PE") {
            $filter = "date_depot>'" . date("Y-m-d") . "T23:59:59Z' OR date_arc>'" . date("Y-m-d") . "T23:59:59Z' OR date_decision>'" . date("Y-m-d") . "T23:59:59Z'";
            $filter .= " OR date_depot<'1800-01-01'";
            $filter .= " OR date_depot is null";
            $filter .= " OR date_depot>date_decision";
            $filter .= " OR date_cc < date_depot";
            $filter .= " OR streetname_fr == '' OR streetname_nl == ''";
            $filter .= " OR (geometry is null AND date_depot>'2019-01-01T00:00:00Z')";
            $filter .= " OR zipcode is null";
        }
        else {
            $filter = "(datedepot>'" . date("Y-m-d") . "T23:59:59Z' OR dateardosscomplet>'" . date("Y-m-d") . "T23:59:59Z' OR datenotifdecision>'" . date("Y-m-d") . "T23:59:59Z'";
            $filter .= " OR datedepot<'1800-01-01'";
            $filter .= " OR datedepot is null";
            $filter .= " OR datedepot>datenotifdecision";
            $filter .= " OR datecc < datedepot";
            $filter .= " OR streetnamefr == '' OR streetnamenl == ''";
            $filter .= " OR (geometry is null AND datedepot>'2019-01-01T00:00:00Z')";
            $filter .= " OR zipcode == '') ";
        }

        $this->cql_filter[] = $filter;

        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOrder(Attribute $attribute, string $order = 'DESC'): self
    {
        $this->order = [$this->contextAttribute($attribute), $order];
        return $this;
    }

    public function contextAttribute(Attribute $attribute): string
    {
        return $this->type === "PU" ? $attribute->pu() : $attribute->pe();
    }

    public static function toDatetime(?string $date): ?DateTime {
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

    /**
     * @throws \JsonException
     */
    public function getResults(): PermitCollection
    {
        $wfs = new WfsLayer($this->path, $this->layer);
        $wfs->setCqlFilter($this->cqlFilterToString())
            ->setCount($this->limit);

        if(!empty($this->order)) {
            $wfs->setSortBy($this->order[0], $this->order[1]);
        }
        $this->query_url = $wfs->getQueryUrl();
        $results = $wfs->getPropertiesArray(true);

        foreach ($results as $result) {
            // Check if Refnova is null... sigh
            if(is_null($result[$this->contextAttribute(Attribute::REFERENCE_NOVA)])) { break; }
            $permit = new Permit($result[$this->contextAttribute(Attribute::REFERENCE_NOVA)]);
            $permit->setLanguage($result[$this->contextAttribute(Attribute::LANGUAGE)]);
            $permit->setVersion($result[$this->contextAttribute(Attribute::VERSION)] ?? null);
            $permit->setType($this->type);
            $permit->setSubtype($result[$this->contextAttribute(Attribute::SUBTYPE)]);
            $permit->setDateSubmission($this->defineSubmissionDate(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_SUBMISSION)])));
            $permit->setDateArc(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ARC)]));
            $permit->setDateAri(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ARI)]));
            $permit->setDateAdditionalElements(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ADDITIONAL_ELEMENTS)] ?? null));
            $permit->setDateCc(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_CC)]));
            $permit->setDateInquiryBegin(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_INQUIRY_BEGIN)]));
            $permit->setDateInquiryEnd(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_INQUIRY_END)]));
            $permit->setDateNotification(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_NOTIFICATION)]));
            $permit->setAreaTypology($this->defineAreaTypologyFromAttributes($result));
            $permit->setAdvices($this->defineAdvicesFromAttributes($result));
            $permit->setAddress($this->defineAddressFromAttributes($result));
            $permit->setZipcode($this->sanitizeZipcode($result[$this->contextAttribute(Attribute::ZIPCODE)], FILTER_SANITIZE_NUMBER_INT));
            $permit->setSortingStreetname($result[$this->contextAttribute(Attribute::STREET_NAME_FR)]);
            $permit->setSortingNumber((int)$result[$this->contextAttribute(Attribute::STREET_NUMBER_FROM)]);
            $permit->setSource($this->defineSource($permit->getReferenceNova()));
            $permit->setSuspensions($this->defineSuspensions($result['suspensions'] ?? null));
            $permit->setUuid($result['uuid']);
            $permit->setReferenceFile($result[$this->contextAttribute(Attribute::REFERENCE_FILE)]);
            $permit->setReferenceMunicipality($result[$this->contextAttribute(Attribute::REFERENCE_MUNICIPALITY)]);
            $permit->setReferenceMixedPermit($this->defineReferenceMixedPermit($result[$this->contextAttribute(Attribute::IS_MIXED)], $result[$this->contextAttribute(Attribute::REFERENCE_MIXED_PERMIT)]));
            $permit->setChargesTotal($result['deliveredpermittotalcharge'] ?? null);
            $permit->setObject($this->defineObjectFromAttributes($result));
            $permit->setStatus($this->defineStatusFromAttributes($result));
            $permit->setQueryUrl($this->definePermitQueryUrl($permit->getReferenceNova()));
            $permit->setSubmissionType($result[$this->contextAttribute(Attribute::SUBMISSION_TYPE)]);
            $permit->setGeometry($this->defineGeometry($result[$this->contextAttribute(Attribute::GEOMETRY)]));
            $this->permit_collection->addPermit($permit);
        }

        return $this->permit_collection;
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
        $json_advices = $attributes['avis_instances'] ?? null;
        if(!is_null($json_advices)) {
            $json_advices = json_decode($json_advices, true, 512, JSON_THROW_ON_ERROR);
        }

        $instances['fr'] = [];
        $instances['nl'] = [];

        $advice_instances = $json_advices['data']['Case_Advice_Instance_List']['elements'] ?? [];

        foreach ($advice_instances as $instance) {
            $instances['fr'][] = $instance['translations'][0]['label'];
            $instances['nl'][] = $instance['translations'][1]['label'];
        }

        $college = $attributes['avis_cbe'] ?? $attributes['aviscbe'] ?? null;
        $cc = $attributes['avis_cc'] ?? $attributes['aviscc'] ?? null;
        $fd = $attributes['avis_fd'] ?? $attributes['avisfd'] ?? null;
        $crms = $attributes['avis_crms'] ?? $attributes['aviscrms'] ?? null;

        if($college === true) { $instances['fr'][] = 'college'; $instances['nl'][] = 'college'; }
        if($cc === true) { $instances['fr'][] = 'cc'; $instances['nl'][] = 'cc'; }
        if($fd === true) { $instances['fr'][] = 'fd'; $instances['nl'][] = 'fd'; }
        if($crms === true) { $instances['fr'][] = 'CRMS'; $instances['nl'][] = 'CRMS'; }

        $instances['fr'] = array_unique($instances['fr']);
        $instances['nl'] = array_unique($instances['nl']);

        return $instances;
    }

    public function defineAddressFromAttributes(array $attributes): array
    {
        $streetname_fr = $attributes[$this->contextAttribute(Attribute::STREET_NAME_FR)] ?? null;
        $streetname_nl = $attributes[$this->contextAttribute(Attribute::STREET_NAME_NL)] ?? null;

        $address['streetname']['fr'] = !is_null($streetname_fr) ? ucfirst($streetname_fr) : $streetname_fr;
        $address['streetname']['nl'] = $streetname_nl;
        if (empty($address['streetname']['fr'])) {
            $address['streetname']['fr'] = null;
        }
        if (empty($address['streetname']['nl'])) {
            $address['streetname']['nl'] = null;
        }

        $address['number']['from'] = $attributes[$this->contextAttribute(Attribute::STREET_NUMBER_FROM)] ?? null;
        $address['number']['to'] = $attributes[$this->contextAttribute(Attribute::STREET_NUMBER_TO)] ?? null;

        if (empty($address['number']['from'])) {
            $address['number']['from'] = null;
        }
        if (empty($address['number']['to'])) {
            $address['number']['to'] = null;
        }

        $address['number']['full'] = (
            !is_null($address['number']['from'])
            && !is_null($address['number']['to'])
            && ($address['number']['from'] !== $address['number']['to'])
        )
            ? $address['number']['from'].'-'.$address['number']['to']
            : $address['number']['from'];

        $address['municipality']['fr'] = $attributes[$this->contextAttribute(Attribute::MUNICIPALITY_FR)];
        $address['municipality']['nl'] = $attributes[$this->contextAttribute(Attribute::MUNICIPALITY_NL)];

        return $address;
    }

    public function defineSource($reference_nova): array
    {
        if ($this->type === 'PE') {
            $source = [
                'base_path' => self::PE_PATH,
                'layer_name' => self::PE_LAYER_NAME,
            ];
            $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter=ref_nova=\''.$reference_nova.'\'';
        } else {
            $source = [
                'base_path' => self::PU_PATH,
                'layer_name' => self::PU_LAYER_NAME,
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
            $new['fr'] = $suspension[0]['suspension']['motif-fr'];
            $new['nl'] = $suspension[0]['suspension']['motif-nl'];
            $new['from'] = !is_null($suspension[0]['suspension']['date-from']) ? DateTime::createFromFormat('Y-m-d', $suspension[0]['suspension']['date-from'], new DateTimeZone('Europe/Brussels')) : false;
            $new['to'] = !is_null($suspension[0]['suspension']['date-to']) ? DateTime::createFromFormat('Y-m-d', $suspension[0]['suspension']['date-to'], new DateTimeZone('Europe/Brussels')) : false;
            $suspensions[] = $new;
        }

        return $suspensions;
    }

    private function defineObjectFromAttributes(array $attributes): array
    {
        $object['fr']['standard'] = $attributes[$this->contextAttribute(Attribute::OBJECT_STANDARD_FR)] ?? null;
        $object['nl']['standard'] = $attributes[$this->contextAttribute(Attribute::OBJECT_STANDARD_NL)] ?? null;

        $fr_real = $attributes[$this->contextAttribute(Attribute::OBJECT_REAL_FR)] ?? null;
        $nl_real = $attributes[$this->contextAttribute(Attribute::OBJECT_REAL_NL)] ?? null;

        $object['fr']['real'] = !is_null($fr_real) ? ucfirst($fr_real) : null;
        $object['nl']['real'] = !is_null($nl_real) ? ucfirst($nl_real) : null;

        return $object;
    }

    private function defineStatusFromAttributes(array $attributes): ?string
    {
        $status_fr = $attributes['statutpermisfr'] ?? null;
        $final_state = $attributes['statut_dossier'] ?? $attributes['etatfinal'] ?? null;

        if ($status_fr === "Saisi par FD") {
            return 'referral';
        }

        if ($status_fr === "Octroyé") {
            return 'delivered';
        }

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
        $wfs = new WfsLayer($this->path, $this->layer);
        $wfs->setCqlFilter($this->contextAttribute(Attribute::REFERENCE_NOVA)."='".$reference_nova."'")
            ->setCount(1);

        return $wfs->getQueryUrl();
    }

    private function defineReferenceMixedPermit(?bool $is_mixed, ?string $mixed_reference): ?string
    {
        if($is_mixed !== true) {
            return null;
        }
        return $mixed_reference ?? ''; // Return empty reference to make the distinction with non mixed permits
    }

    private function defineSubmissionDate(?DateTime $date): ?DateTime
    {
        $oldest_date = new DateTime('1800-01-01');

        if ($date < $oldest_date) {
            return null;
        }
        return $date;
    }

    private function defineGeometry(?\stdClass $geometry): ?string
    {
        if (!is_null($geometry)) {
            return \geoPHP::load(json_encode($geometry), 'json')->out('wkt');
        }
        return null;
    }

    private function sanitizeZipcode(?string $zipcode): ?int
    {
        if(is_null($zipcode)) {
            return null;
        }
        return (int)filter_var($zipcode, FILTER_SANITIZE_NUMBER_INT);
    }

    public function cqlFilterToString(): ?string {
        $nb = count($this->cql_filter);

        if ($nb === 0) {
            return null;
        }

        if($nb > 1) {
            return "(".implode(") AND (", $this->cql_filter).")";
        }

        return $this->cql_filter[0];
    }
}