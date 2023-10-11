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
            self::CHARGES => 'deliveredpermittotalcharge',
            self::CUT_TREES => 'nb_arbres_abattus',
            self::DATE_ADDITIONAL_ELEMENTS => 'dateelemcomplast',
            self::DATE_ARC => 'datearclast',
            self::DATE_ARC_MODIFIED_PLANS_LAST => 'dateArcPmLast',
            self::DATE_ARI => 'datearifirst',
            self::DATE_CC => 'datecc',
            self::DATE_INQUIRY_BEGIN => 'datedebutmpp',
            self::DATE_INQUIRY_END => 'datefinmpp',
            self::DATE_NOTIFICATION => 'datenotifdecision',
            self::DATE_SUBMISSION => 'datedepot',
            self::DATE_VALIDITY => 'date_validite_permis',
            self::DATE_WORK_BEGIN => 'date_debut_travaux',
            self::DATE_WORK_END => 'date_fin_travaux',
            self::GEOMETRY => 'geometry',
            self::IS_MIXED => 'mixedpermit',
            self::LANGUAGE => 'languedemande',
            self::MANAGING_AUTHORITY_FR => 'managing_authority_fr',
            self::MANAGING_AUTHORITY_NL => 'managing_authority_nl',
            self::MANAGING_AUTHORITY_ID => 'idmanagingauthority',
            self::MODIFIED_TREES => 'nb_arbres_transformes',
            self::MUNICIPALITY_FR => 'municipalityfr',
            self::MUNICIPALITY_NL => 'municipalitynl',
            self::OBJECT_REAL_FR => 'realobjectfr',
            self::OBJECT_REAL_NL => 'realobjectnl',
            self::OBJECT_STANDARD_FR => 'objectfr',
            self::OBJECT_STANDARD_NL => 'objectnl',
            self::PROCESSING_TIME => 'delaiglobal',
            self::REFERENCE_FILE => 's_iddossier',
            self::REFERENCE_MIXED_PERMIT => 'refmixedpermit',
            self::REFERENCE_MUNICIPALITY => 'referencespecifique',
            self::REFERENCE_NOVA => 'refnova',
            self::STREET_NAME_FR => 'streetnamefr',
            self::STREET_NAME_NL => 'streetnamenl',
            self::STREET_NUMBER_FROM => 'numberpartfrom',
            self::STREET_NUMBER_TO => 'numberpartto',
            self::SUBMISSION_TYPE => 'typedepot_fr',
            self::SUBTYPE => 'typedossier',
            self::TIMEFRAME_GLOBAL_DAYS => 'delaiglobal',
            self::UUID => 'uuid',
            self::VERSION => 'version',
            self::WORK_MONTHS => 'mois_duree_travaux',
            self::ZIPCODE => 'zipcode',
        };
    }

    public function pe(): string
    {
        return match($this)
        {
            self::CHARGES => '',
            self::CUT_TREES => '',
            self::DATE_ADDITIONAL_ELEMENTS => '',
            self::DATE_ARC => 'date_arc',
            self::DATE_ARC_MODIFIED_PLANS_LAST => '',
            self::DATE_ARI => 'date_ari',
            self::DATE_CC => 'date_cc',
            self::DATE_INQUIRY_BEGIN => 'date_debut_mpp',
            self::DATE_INQUIRY_END => 'date_fin_mpp',
            self::DATE_NOTIFICATION => 'date_decision',
            self::DATE_SUBMISSION => 'date_depot',
            self::DATE_VALIDITY => 'date_echeance_permis',
            self::DATE_WORK_BEGIN => '',
            self::DATE_WORK_END => '',
            self::GEOMETRY => 'geometry',
            self::IS_MIXED => 'is_mixed_permit',
            self::LANGUAGE => 'langue_demande',
            self::MANAGING_AUTHORITY_FR => '',
            self::MANAGING_AUTHORITY_NL => '',
            self::MANAGING_AUTHORITY_ID => 'id_managing_authority',
            self::MODIFIED_TREES => '',
            self::MUNICIPALITY_FR => 'municipality_fr',
            self::MUNICIPALITY_NL => 'municipality_nl',
            self::OBJECT_REAL_FR => '',
            self::OBJECT_REAL_NL => '',
            self::OBJECT_STANDARD_FR => 'object_fr',
            self::OBJECT_STANDARD_NL => 'object_nl',
            self::PROCESSING_TIME => 'delai_global',
            self::REFERENCE_FILE => 'nova_seq',
            self::REFERENCE_MIXED_PERMIT => 'ref_mixed_permit',
            self::REFERENCE_MUNICIPALITY => 'ref_com',
            self::REFERENCE_NOVA => 'ref_nova',
            self::STREET_NAME_FR => 'streetname_fr',
            self::STREET_NAME_NL => 'streetname_nl',
            self::STREET_NUMBER_FROM => 'number_from',
            self::STREET_NUMBER_TO => 'number_to',
            self::SUBMISSION_TYPE => 'typedepot_fr',
            self::SUBTYPE => 'case_subtype',
            self::TIMEFRAME_GLOBAL_DAYS => 'delaiglobal',
            self::UUID => 'uuid',
            self::VERSION => '',
            self::WORK_MONTHS => '',
            self::ZIPCODE => 'zipcode',
        };
    }
}