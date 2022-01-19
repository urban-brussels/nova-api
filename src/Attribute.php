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
    case OBJECT_FR;
    case OBJECT_NL;
    case REFNOVA;
    case STREET_NAME_FR;
    case STREET_NAME_NL;
    case SUBTYPE;

    public function pu(): string
    {
        return match($this)
        {
            self::CHARGES => 'deliveredpermittotalcharge',
            self::DATE_ADDITIONAL_ELEMENTS => 'dateelemcomplast',
            self::DATE_ARC => 'datearclast',
            self::DATE_ARI => 'datearilast',
            self::DATE_CC => 'datecc',
            self::DATE_INQUIRY_BEGIN => 'datedebutmpp',
            self::DATE_INQUIRY_END => 'datefinmpp',
            self::DATE_NOTIFICATION => 'datenotifdecision',
            self::DATE_SUBMISSION => 'datedepot',
            self::LANGUAGE => 'languedemande',
            self::OBJECT_FR => '',
            self::OBJECT_NL => '',
            self::REFNOVA => 'refnova',
            self::SUBTYPE => 'typedossier',
            self::STREET_NAME_FR => 'streetnamefr',
            self::STREET_NAME_NL => 'streetnamenl',
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
            self::OBJECT_FR => '',
            self::OBJECT_NL => '',
            self::REFNOVA => 'ref_nova',
            self::SUBTYPE => 'case_subtype',
            self::STREET_NAME_FR => 'streetname_fr',
            self::STREET_NAME_NL => 'streetname_nl',
        };
    }
}