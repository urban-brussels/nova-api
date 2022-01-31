<?php

namespace UrbanBrussels\NovaApi;

enum Attribute
{
    case CHARGES;
    case DATE_ADDITIONAL_ELEMENTS;
    case DATE_ARC;
    case DATE_ARI;
    case DATE_CC;
    case DATE_INQUIRY_BEGIN;
    case DATE_INQUIRY_END;
    case DATE_NOTIFICATION;
    case DATE_SUBMISSION;
    case LANGUAGE;
    case MUNICIPALITY_FR;
    case MUNICIPALITY_NL;
    case OBJECT_REAL_FR;
    case OBJECT_REAL_NL;
    case OBJECT_STANDARD_FR;
    case OBJECT_STANDARD_NL;
    case REFERENCE_FILE;
    case REFERENCE_MIXED_PERMIT;
    case REFERENCE_MUNICIPALITY;
    case REFERENCE_NOVA;
    case STREET_NAME_FR;
    case STREET_NAME_NL;
    case STREET_NUMBER_FROM;
    case STREET_NUMBER_TO;
    case SUBTYPE;
    case ZIPCODE;

    public function pu(): string
    {
        return match($this)
        {
            self::CHARGES => 'deliveredpermittotalcharge',
            self::DATE_ADDITIONAL_ELEMENTS => 'dateelemcomplast',
            self::DATE_ARC => 'datearclast',
            self::DATE_ARI => 'datearifirst',
            self::DATE_CC => 'datecc',
            self::DATE_INQUIRY_BEGIN => 'datedebutmpp',
            self::DATE_INQUIRY_END => 'datefinmpp',
            self::DATE_NOTIFICATION => 'datenotifdecision',
            self::DATE_SUBMISSION => 'datedepot',
            self::LANGUAGE => 'languedemande',
            self::MUNICIPALITY_FR => 'municipalityfr',
            self::MUNICIPALITY_NL => 'municipalitynl',
            self::OBJECT_REAL_FR => 'realobjectfr',
            self::OBJECT_REAL_NL => 'realobjectnl',
            self::OBJECT_STANDARD_FR => 'objectfr',
            self::OBJECT_STANDARD_NL => 'objectnl',
            self::REFERENCE_FILE => 's_iddossier',
            self::REFERENCE_MIXED_PERMIT => 'refmixedpermit',
            self::REFERENCE_MUNICIPALITY => 'referencespecifique',
            self::REFERENCE_NOVA => 'refnova',
            self::SUBTYPE => 'typedossier',
            self::STREET_NAME_FR => 'streetnamefr',
            self::STREET_NAME_NL => 'streetnamenl',
            self::STREET_NUMBER_FROM => 'numberpartfrom',
            self::STREET_NUMBER_TO => 'numberpartto',
            self::ZIPCODE => 'zipcode',
        };
    }

    public function pe(): string
    {
        return match($this)
        {
            self::CHARGES => '',
            self::DATE_ADDITIONAL_ELEMENTS => '',
            self::DATE_ARC => 'date_arc',
            self::DATE_ARI => 'date_ari',
            self::DATE_CC => 'date_cc',
            self::DATE_INQUIRY_BEGIN => 'date_debut_mpp',
            self::DATE_INQUIRY_END => 'date_fin_mpp',
            self::DATE_NOTIFICATION => 'date_notif_decision',
            self::DATE_SUBMISSION => 'date_depot',
            self::LANGUAGE => 'langue_demande',
            self::MUNICIPALITY_FR => 'municipality_fr',
            self::MUNICIPALITY_NL => 'municipality_nl',
            self::OBJECT_REAL_FR => '',
            self::OBJECT_REAL_NL => '',
            self::OBJECT_STANDARD_FR => 'object_fr',
            self::OBJECT_STANDARD_NL => 'object_nl',
            self::REFERENCE_FILE => 'nova_seq',
            self::REFERENCE_MIXED_PERMIT => 'ref_mixed_permit',
            self::REFERENCE_MUNICIPALITY => 'ref_com',
            self::REFERENCE_NOVA => 'ref_nova',
            self::SUBTYPE => 'case_subtype',
            self::STREET_NAME_FR => 'streetname_fr',
            self::STREET_NAME_NL => 'streetname_nl',
            self::STREET_NUMBER_FROM => 'number_from',
            self::STREET_NUMBER_TO => 'number_to',
            self::ZIPCODE => 'zipcode',
        };
    }
}