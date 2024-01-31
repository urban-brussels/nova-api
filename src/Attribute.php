<?php

namespace UrbanBrussels\NovaApi;

enum Attribute
{
    case CHARGES;
    case CUT_TREES;
    case DATE_ADDITIONAL_ELEMENTS;
    case DATE_ARC;
    case DATE_ARC_MODIFIED_PLANS_LAST;
    case DATE_ARI;
    case DATE_CC;
    case DATE_INQUIRY_BEGIN;
    case DATE_INQUIRY_END;
    case DATE_NOTIFICATION;
    case DATE_SUBMISSION;
    case DATE_VALIDITY;
    case DATE_WORK_BEGIN;
    case DATE_WORK_END;
    case GEOMETRY;
    case IS_MIXED;
    case LANGUAGE;
    case MANAGING_AUTHORITY_FR;
    case MANAGING_AUTHORITY_NL;
    case MANAGING_AUTHORITY_ID;
    case MODIFIED_TREES;
    case MUNICIPALITY_FR;
    case MUNICIPALITY_NL;
    case MUNICIPALITY_OWNER_FR;
    case MUNICIPALITY_OWNER_NL;
    case OBJECT_REAL_FR;
    case OBJECT_REAL_NL;
    case OBJECT_STANDARD_FR;
    case OBJECT_STANDARD_NL;
    case PROCESSING_TIME;
    case REFERENCE_FILE;
    case REFERENCE_MIXED_PERMIT;
    case REFERENCE_MUNICIPALITY;
    case REFERENCE_NOVA;
    case STREET_NAME_FR;
    case STREET_NAME_NL;
    case STREET_NUMBER_FROM;
    case STREET_NUMBER_TO;
    case SUBMISSION_TYPE;
    case SUBTYPE;
    case TIMEFRAME_GLOBAL_DAYS;
    case UUID;
    case VERSION;
    case WORK_MONTHS;
    case ZIPCODE;

    public function pu(): string
    {
        return match($this)
        {
            self::CHARGES => 'deliveredPermitTotalCharge',
            self::CUT_TREES => 'treesCutNumber',
            self::DATE_ADDITIONAL_ELEMENTS => 'dateAdditionalInformationLast',
            self::DATE_ARC => 'dateArCompleteCase',
            self::DATE_ARC_MODIFIED_PLANS_LAST => 'dateArcPmLast',
            self::DATE_ARI => 'dateAriFirst',
            self::DATE_CC => 'dateAdviceConsultationCommission',
            self::DATE_INQUIRY_BEGIN => 'dateInquiryStart',
            self::DATE_INQUIRY_END => 'dateInquiryEnd',
            self::DATE_NOTIFICATION => 'dateDecisionNotification',
            self::DATE_SUBMISSION => 'dateSubmission',
            self::DATE_VALIDITY => 'datePermitValidity',
            self::DATE_WORK_BEGIN => 'dateImplementationStart',
            self::DATE_WORK_END => 'dateImplementationEnd',
            self::GEOMETRY => 'geometry',
            self::IS_MIXED => 'isMixedCase',
            self::LANGUAGE => 'submissionLanguage',
            self::MANAGING_AUTHORITY_FR => 'managingAuthorityFr',
            self::MANAGING_AUTHORITY_NL => 'managingAuthorityNl',
            self::MANAGING_AUTHORITY_ID => 'idManagingAuthority',
            self::MODIFIED_TREES => 'treesModifiedNumber',
            self::MUNICIPALITY_FR => 'municipalityFr',
            self::MUNICIPALITY_NL => 'municipalityNl',
            self::MUNICIPALITY_OWNER_FR => 'municipalityOwnerFr',
            self::MUNICIPALITY_OWNER_NL => 'municipalityOwnerNl',
            self::OBJECT_REAL_FR => 'objectRealFr',
            self::OBJECT_REAL_NL => 'objectRealNl',
            self::OBJECT_STANDARD_FR => 'objectStandardFr',
            self::OBJECT_STANDARD_NL => 'objectStandardNl',
            self::PROCESSING_TIME => 'globalDuration',
            self::REFERENCE_FILE => 'caseId',
            self::REFERENCE_MIXED_PERMIT => 'referenceMixedCase',
            self::REFERENCE_MUNICIPALITY => 'referenceMunicipality',
            self::REFERENCE_NOVA => 'referenceNova',
            self::STREET_NAME_FR => 'streetNameFr',
            self::STREET_NAME_NL => 'streetNameNl',
            self::STREET_NUMBER_FROM => 'streetNumberFrom',
            self::STREET_NUMBER_TO => 'streetNumberTo',
            self::SUBMISSION_TYPE => 'typeSubmissionFr',
            self::SUBTYPE => 'caseSubtype',
            self::TIMEFRAME_GLOBAL_DAYS => 'globalDuration',
            self::UUID => 'uuid',
            self::VERSION => 'caseVersion',
            self::WORK_MONTHS => 'workDurationMonths',
            self::ZIPCODE => 'zipCode',
        };
    }
}