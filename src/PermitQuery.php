<?php

namespace UrbanBrussels\NovaApi;

use DateTime;
use geoPHP\geoPHP;
use DateTimeZone;
use ici\ici_tools\WfsLayer;

class PermitQuery
{
    public const PU_PATH = 'https://geoservices-others.irisnet.be/geoserver/Nova/ows';
    public const PE_PATH = self::PU_PATH;
    public const PU_LAYER_NAME = 'Nova:PlanningPermits';
    public const PE_LAYER_NAME = 'Nova:EnvironmentalPermits';

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
        $this->cql_filter[] = 'caseId='.$id;

        return $this;
    }

    public function filterByIncidence(?int $year = null): self
    {
        $filter = '('.Attribute::HAS_IMPACT_REPORT->value.'=true or '.Attribute::HAS_IMPACT_REPORT->value.'=true)';
        if (!is_null($year)) {
            $filter .= " and ".Attribute::DATE_INQUIRY_BEGIN->value." >= '".$year."-01-01' and ".Attribute::DATE_INQUIRY_BEGIN->value." <= '".$year."-12-31'";
        }

        $this->cql_filter[] = $filter;

        return $this;
    }

    public function filterByInquiryDate(string $date = null): self
    {
        $filter = Attribute::DATE_INQUIRY_BEGIN->value." <= '".date("Y-m-d")."T23:59:59Z' AND ".Attribute::DATE_INQUIRY_END->value." >= '".date(
                "Y-m-d"
            )."T00:00:00Z' AND ".Attribute::DATE_INQUIRY_BEGIN->value." >= '".date(
                "Y-m-d",
                strtotime("-70 days")
            )."T10:00:00Z' AND ".Attribute::DATE_INQUIRY_END->value." <= '".date("Y-m-d", strtotime("70 days"))."T10:00:00Z'";

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

    public function filterByAttributeArray(Attribute $attribute, array $values, bool $in_array = true): self
    {
        $this->cql_filter[] = $this->contextAttribute($attribute) . " ".($in_array === false ? "NOT " : "")."IN ('" . implode("','", $values)."')";

        return $this;
    }

    public function filterByDataError(array $zipcodes = []): self
    {
        $filter = "(".Attribute::DATE_SUBMISSION->value.">'" . date("Y-m-d") . "T23:59:59Z' OR ".Attribute::DATE_ARC->value.">'" . date("Y-m-d") . "T23:59:59Z' OR ".Attribute::DATE_NOTIFICATION->value.">'" . date("Y-m-d") . "T23:59:59Z'";
        $filter .= " OR ".Attribute::DATE_SUBMISSION->value."<'1800-01-01'";
        $filter .= " OR ".Attribute::DATE_SUBMISSION->value.">".Attribute::DATE_NOTIFICATION->value;
        $filter .= " OR ".Attribute::DATE_CC->value." < ".Attribute::DATE_SUBMISSION->value;
        $filter .= " OR ".Attribute::STREET_NAME_FR->value." == '' OR ".Attribute::STREET_NAME_NL->value." == ''";
        $filter .= " OR (".Attribute::GEOMETRY->value." is null AND ".Attribute::DATE_SUBMISSION->value.">'2019-01-01T00:00:00Z')";
        $filter .= " OR ".Attribute::ZIPCODE->value." is null)";

        if(!empty($zipcodes)) {
            $filter .= " AND ".Attribute::ZIPCODE->value." in (".implode(',', $zipcodes).")";
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
        // Temporary fix
        if($this->type === 'PE') {
//            if($attribute === Attribute::DATE_NOTIFICATION) {
//                return 'decisionDate';
//            }
        }
        return $attribute->value ?? '';
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
        if(!$date_time) {
            return null;
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
            $permit->setDateArcModifiedPlansLast(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ARC_MODIFIED_PLANS_LAST)] ?? null));
            $permit->setDateAri(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ARI)]));
            $permit->setDateAdditionalElements(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_ADDITIONAL_ELEMENTS)] ?? null));
            $permit->setDateCc(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_CC)]));
            $permit->setDateInquiryBegin(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_INQUIRY_BEGIN)]));
            $permit->setDateInquiryEnd(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_INQUIRY_END)]));
            $permit->setDateNotification(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_NOTIFICATION)]));
            $permit->setDateValidity(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_VALIDITY)]));
            $permit->setDateWorkBegin(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_WORK_BEGIN)] ?? null));
            $permit->setDateWorkEnd(self::toDatetime($result[$this->contextAttribute(Attribute::DATE_WORK_END)] ?? null));
            $permit->setWorkMonths($result[$this->contextAttribute(Attribute::WORK_MONTHS)] ?? null);
            $permit->setAreaTypology($this->defineAreaTypologyFromAttributes($result));
            $permit->setAdvices($this->defineAdvicesFromAttributes($result));
            $permit->setAddress($this->defineAddressFromAttributes($result));
            $permit->setManagingAuthority($this->defineManagingAuthorityFromAttributes($result));
            $permit->setMunicipalityOwner($this->defineMunicipalityOwnerFromAttributes($result));
            $permit->setZipcode($this->sanitizeZipcode($result[$this->contextAttribute(Attribute::ZIPCODE)], FILTER_SANITIZE_NUMBER_INT));
            $permit->setSortingStreetname($result[$this->contextAttribute(Attribute::STREET_NAME_FR)]);
            $permit->setSortingNumber((int)$result[$this->contextAttribute(Attribute::STREET_NUMBER_FROM)]);
            $permit->setSource($this->defineSource($permit->getReferenceNova()));
            $permit->setProcessingTime($result[$this->contextAttribute(Attribute::PROCESSING_TIME)]);
            $permit->setSuspensions($this->defineSuspensions($result[$this->contextAttribute(Attribute::SUSPENSION_LIST)]));
            $permit->setUuid($result['uuid']);
            $permit->setReferenceFile($result[$this->contextAttribute(Attribute::REFERENCE_FILE)]);
            $permit->setReferenceMunicipality($result[$this->contextAttribute(Attribute::REFERENCE_MUNICIPALITY)]);
            $permit->setReferenceMixedPermit($this->defineReferenceMixedPermit($result[$this->contextAttribute(Attribute::IS_MIXED)], $result[$this->contextAttribute(Attribute::REFERENCE_MIXED_PERMIT)]));
            $permit->setChargesTotal($result[$this->contextAttribute(Attribute::CHARGES)] ?? 0);
            $permit->setObject($this->defineObjectFromAttributes($result));
            $permit->setStatus($this->defineStatusFromAttributes($result));
            $permit->setCutTrees($this->defineCountTrees($result[$this->contextAttribute(Attribute::CUT_TREES)] ?? 0));
            $permit->setModifiedTrees($this->defineCountTrees($result[$this->contextAttribute(Attribute::MODIFIED_TREES)] ?? 0));
            $permit->setQueryUrl($this->definePermitQueryUrl($permit->getReferenceNova()));
            $permit->setSubmissionType($result[$this->contextAttribute(Attribute::SUBMISSION_TYPE)]);
            $permit->setGeometry($this->defineGeometry($result[$this->contextAttribute(Attribute::GEOMETRY)]));
            $permit->setArea($this->defineArea($permit->getGeometry()));
            $permit->setRating($this->defineRating($permit->getArea(), count($permit->getAdvices()['fr'])));
            $this->permit_collection->addPermit($permit);
        }

        return $this->permit_collection;
    }

    public function getHits(): ?int
    {
        $wfs = new WfsLayer($this->path, $this->layer);
        $wfs->setCqlFilter($this->cqlFilterToString());

        return $wfs->getHits();
    }

    private function defineAreaTypologyFromAttributes(array $attributes): array
    {
        if ($this->type === "PE") {
            return [];
        }
        $typologies = [];

        foreach ($attributes as $k => $v) {
            if (!is_null($v)
                && (str_contains($k, 'Authorized') || str_contains($k, 'Existing') || str_contains($k, 'Projected'))
            ) {
                $type = str_replace(['Authorized', 'Existing', 'Projected', 'Area'], '', $k);
                $subtype = str_replace([$type, 'Area'], '', $k);
                $typologies[strtolower($type)][strtolower($subtype)] = $v;
            }
        }

        // Remove entries if none is above 0m²
        foreach ($typologies as $key => $subArray) {
            $allZeroOrLess = true;
            foreach ($subArray as $value) {
                if ($value > 0) {
                    $allZeroOrLess = false;
                    break;
                }
            }
            if ($allZeroOrLess) {
                unset($typologies[$key]);
            }
        }

        // Differences
        foreach ($typologies as &$typology) {
            if(isset($typology['authorized']) && isset($typology['existing'])) {
                $typology['difference'] = round($typology['authorized'] - $typology['existing'], 2);
            }
            elseif(isset($typology['projected']) && isset($typology['existing'])) {
                $typology['difference'] = round($typology['projected'] - $typology['existing'], 2);
            }
            else $typology['difference'] = null;
        }
        unset($typology);

        return $typologies;
    }

    /**
     * @throws \JsonException
     */
    private function defineAdvicesFromAttributes(array $attributes): array
    {
        $json_advices = $attributes['adviceInstances'] ?? null;
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

        $college = $attributes[$this->contextAttribute(Attribute::HAS_COLLEGE_OPINION)] ?? null;
        $cc = $attributes[$this->contextAttribute(Attribute::HAS_CC_OPINION)] ?? null;
        $fd = $attributes[$this->contextAttribute(Attribute::HAS_FD_OPINION)] ?? null;
        $crms = $attributes[$this->contextAttribute(Attribute::HAS_CRMS_OPINION)] ?? null;

        if($college === true) { $instances['fr'][] = 'college'; $instances['nl'][] = 'college'; }
        if($cc === true) { $instances['fr'][] = 'cc'; $instances['nl'][] = 'cc'; }
        if($fd === true) { $instances['fr'][] = 'fd'; $instances['nl'][] = 'fd'; }
        if($crms === true) { $instances['fr'][] = 'CRMS'; $instances['nl'][] = 'CRMS'; }

        $instances['fr'] = array_unique($instances['fr']);
        $instances['nl'] = array_unique($instances['nl']);

        return $instances;
    }

    public function defineManagingAuthorityFromAttributes(array $attributes): array
    {
        $authority_fr = $attributes[$this->contextAttribute(Attribute::MANAGING_AUTHORITY_FR)] ?? null;
        $authority_nl = $attributes[$this->contextAttribute(Attribute::MANAGING_AUTHORITY_NL)] ?? null;
        $authority_id = $attributes[$this->contextAttribute(Attribute::MANAGING_AUTHORITY_ID)] ?? null;

        if($authority_fr === 'BUP') {
            $authority_fr = 'Urban.brussels';
            $authority_nl = 'Urban.brussels';
        }

        return ['id' => $authority_id, 'fr' => $authority_fr, 'nl' => $authority_nl];
    }

    public function defineMunicipalityOwnerFromAttributes(array $attributes): array
    {
        $municipality_owner_fr = $attributes[$this->contextAttribute(Attribute::MUNICIPALITY_OWNER_FR)] ?? null;
        $municipality_owner_nl = $attributes[$this->contextAttribute(Attribute::MUNICIPALITY_OWNER_NL)] ?? null;

        return ['fr' => $municipality_owner_fr, 'nl' => $municipality_owner_nl];
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
        } else {
            $source = [
                'base_path' => self::PU_PATH,
                'layer_name' => self::PU_LAYER_NAME,
            ];
        }

        $source['query_url'] = $source['base_path'].'?service=WFS&version=2.0.0&request=GetFeature&typeName='.$source['layer_name'].'&outputFormat=application%2Fjson&count=1&cql_filter='.Attribute::REFERENCE_NOVA->value.'=\''.$reference_nova.'\'';

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
        $status_fr = $attributes[$this->contextAttribute(Attribute::CASE_STATUS_FR)] ?? null;
        $final_state = $attributes[$this->contextAttribute(Attribute::CASE_STATUS)] ?? null;

        if ($final_state === "R") {
            return 'appeal';
        }

        if ($status_fr === "Saisi par FD") {
            return 'referral';
        }

        if ($status_fr === "Octroyé") {
            return 'delivered';
        }

        if ($status_fr === "Annulé") {
            return 'canceled';
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

    private function defineCountTrees(int|string|null $trees): int
    {
        if(is_null($trees)) {
            return 0;
        }
        return (int)$trees;
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
            return geoPHP::load(json_encode($geometry), 'json')->out('wkt');
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

    public function defineArea(?string $wkt): ?float {
        if(is_null($wkt)) {
            return null;
        }

        $polygon = geoPHP::load($wkt,'wkt');
        return round($polygon->getArea(),2);
    }

    public function defineRating(?float $area, int $count_advices): int {
        $area = $area ?? 50;
        $rating = $area  * sqrt($count_advices);
        return floor($rating);
    }
}