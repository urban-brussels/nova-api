<?php

namespace UrbanBrussels\NovaApi;

use DateTime;
use JetBrains\PhpStorm\Pure;

class Permit
{
    public string $reference_nova;
    public string $type;
    public string $subtype;
    public ?string $language;
    public string $uuid;
    public int $reference_file;
    public ?string $reference_municipality;
    public ?string $reference_mixed_permit;

    public ?DateTime $date_submission;
    public ?DateTime $date_arc;
    public ?DateTime $date_ari;
    public ?DateTime $date_additional_elements;
    public ?DateTime $date_inquiry_begin;
    public ?DateTime $date_inquiry_end;
    public ?DateTime $date_cc;
    public ?DateTime $date_notification;
    public array $object;
    public array $advices;
    public array $source;
    public bool $validation;
    public array $address;
    public array $area_typology;
    public ?string $status;
    public ?int $charges;
    public array $suspensions;
    public string $query_url;

    public function __construct(string $reference_nova)
    {
        $this->setReferenceNova(self::sanitizeReference($reference_nova));
    }

    /**
     * @return string
     */
    public function getReferenceNova(): string
    {
        return $this->reference_nova;
    }

    /**
     * @param string $reference_nova
     */
    public function setReferenceNova(string $reference_nova): void
    {
        $this->reference_nova = $reference_nova;
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getSubtype(): string
    {
        return $this->subtype;
    }

    /**
     * @param string $subtype
     */
    public function setSubtype(string $subtype): void
    {
        $this->subtype = $subtype;
    }

    /**
     * @return array
     */
    public function getObject(): array
    {
        return $this->object;
    }

    /**
     * @param array $object
     */
    public function setObject(array $object): void
    {
        $this->object = $object;
    }


    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     */
    public function setUuid(string $uuid): void
    {
        $this->uuid = $uuid;
    }

    /**
     * @return int
     */
    public function getReferenceFile(): int
    {
        return $this->reference_file;
    }

    /**
     * @param int $reference_file
     */
    public function setReferenceFile(int $reference_file): void
    {
        $this->reference_file = $reference_file;
    }

    /**
     * @return string|null
     */
    public function getReferenceMunicipality(): ?string
    {
        return $this->reference_municipality;
    }

    /**
     * @param string|null $reference_municipality
     */
    public function setReferenceMunicipality(?string $reference_municipality): void
    {
        $this->reference_municipality = $reference_municipality;
    }

    /**
     * @return string|null
     */
    public function getReferenceMixedPermit(): ?string
    {
        return $this->reference_mixed_permit;
    }

    /**
     * @param string|null $reference_mixed_permit
     */
    public function setReferenceMixedPermit(?string $reference_mixed_permit): void
    {
        $this->reference_mixed_permit = $reference_mixed_permit;
    }

    /**
     * @return array
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @param array $source
     */
    public function setSource(array $source): void
    {
        $this->source = $source;
    }

    /**
     * @return bool
     */
    public function isValidation(): bool
    {
        return $this->validation;
    }

    /**
     * @param bool $validation
     */
    public function setValidation(bool $validation): void
    {
        $this->validation = $validation;
    }

    /**
     * @return DateTime|null
     */
    public function getDateInquiryBegin(): ?DateTime
    {
        return $this->date_inquiry_begin;
    }

    /**
     * @param DateTime|null $date_inquiry_begin
     */
    public function setDateInquiryBegin(?DateTime $date_inquiry_begin): void
    {
        $this->date_inquiry_begin = $date_inquiry_begin;
    }

    /**
     * @return DateTime|null
     */
    public function getDateInquiryEnd(): ?DateTime
    {
        return $this->date_inquiry_end;
    }

    /**
     * @param DateTime|null $date_inquiry_end
     */
    public function setDateInquiryEnd(?DateTime $date_inquiry_end): void
    {
        $this->date_inquiry_end = $date_inquiry_end;
    }

    /**
     * @return array
     */
    public function getAdvices(): array
    {
        return $this->advices;
    }

    /**
     * @param array $advices
     */
    public function setAdvices(array $advices): void
    {
        $this->advices = $advices;
    }

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @param string|null $language
     */
    public function setLanguage(?string $language): void
    {
        $this->language = $language;
    }

    /**
     * @return array
     */
    public function getAddress(): array
    {
        return $this->address;
    }

    /**
     * @param array $address
     */
    public function setAddress(array $address): void
    {
        $this->address = $address;
    }

    /**
     * @return array
     */
    public function getAreaTypology(): array
    {
        return $this->area_typology;
    }

    /**
     * @param array $area_typology
     */
    public function setAreaTypology(array $area_typology): void
    {
        $this->area_typology = $area_typology;
    }

    /**
     * @return DateTime|null
     */
    public function getDateArc(): ?DateTime
    {
        return $this->date_arc;
    }

    /**
     * @param DateTime|null $date_arc
     */
    public function setDateArc(?DateTime $date_arc): void
    {
        $this->date_arc = $date_arc;
    }

    /**
     * @return DateTime|null
     */
    public function getDateAri(): ?DateTime
    {
        return $this->date_ari;
    }

    /**
     * @param DateTime|null $date_ari
     */
    public function setDateAri(?DateTime $date_ari): void
    {
        $this->date_ari = $date_ari;
    }

    /**
     * @return DateTime|null
     */
    public function getDateSubmission(): ?DateTime
    {
        return $this->date_submission;
    }

    /**
     * @param DateTime|null $date_submission
     */
    public function setDateSubmission(?DateTime $date_submission): void
    {
        $this->date_submission = $date_submission;
    }

    /**
     * @return DateTime|null
     */
    public function getDateCc(): ?DateTime
    {
        return $this->date_cc;
    }

    /**
     * @param DateTime|null $date_cc
     */
    public function setDateCc(?DateTime $date_cc): void
    {
        $this->date_cc = $date_cc;
    }

    /**
     * @return DateTime|null
     */
    public function getDateNotification(): ?DateTime
    {
        return $this->date_notification;
    }

    /**
     * @param DateTime|null $date_notification
     */
    public function setDateNotification(?DateTime $date_notification): void
    {
        $this->date_notification = $date_notification;
    }

    /**
     * @return DateTime|null
     */
    public function getDateAdditionalElements(): ?DateTime
    {
        return $this->date_additional_elements;
    }

    /**
     * @param DateTime|null $date_additional_elements
     */
    public function setDateAdditionalElements(?DateTime $date_additional_elements): void
    {
        $this->date_additional_elements = $date_additional_elements;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int|null
     */
    public function getCharges(): ?int
    {
        return $this->charges;
    }

    /**
     * @param int|null $charges
     */
    public function setCharges(?int $charges): void
    {
        $this->charges = $charges;
    }

    /**
     * @return array
     */
    public function getSuspensions(): array
    {
        return $this->suspensions;
    }

    /**
     * @param array $suspensions
     */
    public function setSuspensions(array $suspensions): void
    {
        $this->suspensions = $suspensions;
    }

    public function findErrors(): array
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

    /**
     * @return string
     */
    public function getQueryUrl(): string
    {
        return $this->query_url;
    }

    /**
     * @param string $query_url
     */
    public function setQueryUrl(string $query_url): void
    {
        $this->query_url = $query_url;
    }

    #[Pure] public function hasInquiry():bool
    {
        return !is_null($this->getDateInquiryEnd());
    }

    public function hasActiveInquiry():bool
    {
        $now = new DateTime();

        return $this->getDateInquiryEnd() > $now && $this->getDateInquiryBegin() < $now;
    }

    public function getLinks(): array
    {
        $links['openpermits']['fr'] = 'https://openpermits.brussels/fr/_'.$this->getReferenceNova();
        $links['openpermits']['nl'] = 'https://openpermits.brussels/nl/_'.$this->getReferenceNova();
        $links['nova'] = 'https://nova.brussels/nova-ui/page/open/request/AcmDisplayCase.xhtml?ids=&id='.$this->getReferenceFile().'&uniqueCase=true';

        return $links;
    }

    public function getReferences(): array
    {
        $references['uuid'] = $this->getUuid();
        $references['file'] = $this->getReferenceFile();
        $references['municipality'] = $this->getReferenceMunicipality();
        $references['mixed_permit'] = $this->getReferenceMixedPermit();

        return $references;
    }

    public function getAuthority(): ?string
    {
        $subtype = $this->getSubtype();
        if (in_array(
            $this->getSubtype(),
            ["PFD", "PFU", "SFD", "ECO", "SOC", "CPFD", "GOU_PU", "LPFD", "LPFU", "CPFU", "LCFU", "LSFD"]
        )) {
            return "region";
        }

        if (!is_null($subtype)) {
            return "municipality";
        }

        return null;
    }

    #[Pure] public static function guessPermitType(string $reference_nova): string
    {
        $reference_nova = self::sanitizeReference($reference_nova);

        if (str_contains($reference_nova, 'IPE')
            || str_contains($reference_nova, 'CL')
            || str_contains($reference_nova, 'IRCE')
            || str_contains($reference_nova, 'ICE')
            || str_contains($reference_nova, 'C_')
            || str_contains($reference_nova, 'PLP')
            || str_contains($reference_nova, 'IRPE'))
        {
            return "PE";
        }

        return "PU";
    }

    public static function sanitizeReference(string $reference): string
    {
        return strtoupper(trim($reference));
    }

    public function isMixed(): bool
    {
        return !is_null($this->getReferenceMixedPermit());
    }
}