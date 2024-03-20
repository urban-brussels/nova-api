<?php

namespace UrbanBrussels\NovaApi;

enum Attribute: string
{
    case CASE_TYPE = 'procedureType';
    case CHARGES = 'deliveredPermitTotalCharges';
    case CUT_TREES = 'cutTreesNumber';
    case DATE_ADDITIONAL_ELEMENTS = 'additionalInformationReceptionLastDate';
    case DATE_ARC = 'completeAcknowledgeFirstDate';
    case DATE_ARC_MODIFIED_PLANS_LAST = 'modifiedProjectCompleteAcknowledgeLastDate';
    case DATE_ARI = 'uncompleteAcknowledgeFirstDate';
    case DATE_CC = 'consultationCommitteesDate';
    case DATE_INQUIRY_BEGIN = 'inquiryStartDate';
    case DATE_INQUIRY_END = 'dateInquiryEnd'; /// To be modified ///
    case DATE_NOTIFICATION = 'decisionNotificationDate';
    case DATE_SUBMISSION = 'requestSendingDate';
    case DATE_VALIDITY = 'permitValidityDate';
    case DATE_WORK_BEGIN = 'implementationStartDate';
    case DATE_WORK_END = 'implementationEndDate';
    case GEOMETRY = 'geometry';
    case IS_MIXED = 'isMixedCase';
    case HAS_IMPACT_REPORT = 'hasImpactReport';
    case HAS_IMPACT_STUDY = 'hasImpactStudy';
    case LANGUAGE = 'caseLanguage';
    case MANAGING_AUTHORITY_FR = 'managingAuthorityFrenchName';
    case MANAGING_AUTHORITY_NL = 'managingAuthorityDutchName';
    case MANAGING_AUTHORITY_ID = 'managingAuthorityIdentifier';
    case MODIFIED_TREES = 'modifiedTreesNumber';
    case MUNICIPALITY_FR = 'addressMunicipalityFrenchName';
    case MUNICIPALITY_NL = 'addressMunicipalityDutchName';
    case MUNICIPALITY_OWNER_FR = 'caseMainMunicipalityFrenchName';
    case MUNICIPALITY_OWNER_NL = 'caseMainMunicipalityDutchName';
    case OBJECT_REAL_FR = 'caseFrenchObject';
    case OBJECT_REAL_NL = 'caseDutchObject';
    case OBJECT_STANDARD_FR = 'permitFrenchObject';
    case OBJECT_STANDARD_NL = 'permitDutchObject';
    case PROCESSING_TIME = 'instructionGlobalDuration';
    case REFERENCE_FILE = 'caseIdentifier';
    case REFERENCE_MIXED_PERMIT = 'mixedCaseReference';
    case REFERENCE_MUNICIPALITY = 'mainMunicipalityReference';
    case REFERENCE_NOVA = 'novaReference';
    case STREET_NAME_FR = 'streetFrenchName';
    case STREET_NAME_NL = 'streetDutchName';
    case STREET_NUMBER_FROM = 'streetNumberFrom';
    case STREET_NUMBER_TO = 'streetNumberTo';
    case SUBMISSION_TYPE = 'submissionTypeFrenchName';
    case SUBTYPE = 'caseSubType'; /// PE: caseSubtype
    case UUID = 'uuid';
    case VERSION = 'caseVersion';
    case WORK_MONTHS = 'workDurationInMonth';
    case ZIPCODE = 'zipCode';
}