<?php

namespace UrbanBrussels\NovaApi;

use DateTime;

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

    public function __construct(string $reference_nova)
    {
        $this->setReferenceNova($reference_nova);

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
        $this->reference_nova = strtoupper(trim($reference_nova));
    }

    /**
     * @return string
     */
    public function getType(): string
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
     * @return array
     */
    public function getSubtype(): array
    {
        return $this->subtype;
    }

    /**
     * @param array $subtype
     */
    public function setSubtype(array $subtype): void
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
     * @return array|null
     */
    public function getAttributesArray(): ?array
    {
        return $this->attributes_array;
    }

    /**
     * @param array|null $attributes_array
     */
    public function setAttributesArray(?array $attributes_array): void
    {
        $this->attributes_array = $attributes_array;
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
     * @return bool
     */
    public function isInquiryActive(): bool
    {
        return $this->inquiry_active;
    }

    /**
     * @param bool $inquiry_active
     */
    public function setInquiryActive(bool $inquiry_active): void
    {
        $this->inquiry_active = $inquiry_active;
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
     * @return array
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    /**
     * @param array $references
     */
    public function setReferences(array $references): void
    {
        $this->references = $references;
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
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param array $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
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
     * @return string|null
     */
    public function getAuthority(): ?string
    {
        return $this->authority;
    }

    /**
     * @param string|null $authority
     */
    public function setAuthority(?string $authority): void
    {
        $this->authority = $authority;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
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

}