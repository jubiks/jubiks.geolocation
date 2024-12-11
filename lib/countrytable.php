<?php
namespace Jubiks\Geolocation;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class CountryTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ISO_CODE string(2) mandatory
 * <li> NAME string(255) mandatory
 * <li> NAME_EN string(255) optional
 * </ul>
 *
 * @package Bitrix\Geolocation
 **/

class CountryTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'jubiks_geolocation_country';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                    'title' => Loc::getMessage('COUNTRY_ENTITY_ID_FIELD')
                ]
            ),
            new StringField(
                'ISO_CODE',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateIsoCode'],
                    'title' => Loc::getMessage('COUNTRY_ENTITY_ISO_CODE_FIELD')
                ]
            ),
            new StringField(
                'NAME',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateName'],
                    'title' => Loc::getMessage('COUNTRY_ENTITY_NAME_FIELD')
                ]
            ),
            new StringField(
                'NAME_EN',
                [
                    'validation' => [__CLASS__, 'validateNameEn'],
                    'title' => Loc::getMessage('COUNTRY_ENTITY_NAME_EN_FIELD')
                ]
            ),
        ];
    }

    /**
     * Returns validators for ISO_CODE field.
     *
     * @return array
     */
    public static function validateIsoCode()
    {
        return [
            new LengthValidator(null, 2),
        ];
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for NAME_EN field.
     *
     * @return array
     */
    public static function validateNameEn()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }
}