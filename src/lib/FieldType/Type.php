<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtype\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as FieldTypeValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\Row;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection;

class Type extends FieldType
{
    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var mixed
     */
    protected $settingsSchema = [
        'minimum_rows' => [
            'type' => 'integer',
            'default' => 1,
        ],
        'columns' => [
            'type' => 'hash',
            'default' => [],
        ],
    ];

    /** @var string */
    private $fieldTypeIdentifier;

    /**
     * @param string $fieldTypeIdentifier
     */
    public function __construct(string $fieldTypeIdentifier)
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * Return value is mixed. It should be something which is sensible for
     * sorting.
     *
     * It is up to the persistence implementation to handle those values.
     * Common string and integer values are safe.
     *
     * For the legacy storage it is up to the field converters to set this
     * value in either sort_key_string or sort_key_int.
     *
     * In case of multi value, values should be string and separated by "-" or ",".
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return mixed
     */
    protected function getSortInfo(FieldTypeValue $value)
    {
        return '';
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This method expects that given $fieldSettings are complete, for this purpose method
     * {@link self::applyDefaultSettings()} is provided.
     *
     * @param mixed $fieldSettings
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings): array
    {
        return [];
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     * If given $inputValue could not be converted or is already an instance of dedicate value object,
     * the method should simply return it.
     * This is an operation method for {@see acceptValue()}.
     * Example implementation:
     * <code>
     *  protected function createValueFromInput( $inputValue )
     *  {
     *      if ( is_array( $inputValue ) )
     *      {
     *          $inputValue = \eZ\Publish\Core\FieldType\CookieJar\Value( $inputValue );
     *      }
     *      return $inputValue;
     *  }
     * </code>.
     *
     * @param mixed $inputValue
     *
     * @return mixed the potentially converted input value
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Returns the field type identifier for this field type.
     * This identifier should be globally unique and the implementer of a
     * FieldType must take care for the uniqueness. It is therefore recommended
     * to prefix the field-type identifier by a unique string that identifies
     * the implementer. A good identifier could for example take your companies main
     * domain name as a prefix in reverse order.
     *
     * @return string
     */
    public function getFieldTypeIdentifier(): string
    {
        return $this->fieldTypeIdentifier;
    }

    /**
     * Returns a human readable string representation from the given $value.
     * It will be used to generate content name and url alias if current field
     * is designated to be used in the content name/urlAlias pattern.
     * The used $value can be assumed to be already accepted by {@link * acceptValue()}.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return string
     *
     * @deprecated Since 6.3/5.4.7, use \eZ\Publish\SPI\FieldType\Nameable
     */
    public function getName(SPIValue $value): string
    {
        return '';
    }

    /**
     * Returns the empty value for this field type.
     * This value will be used, if no value was provided for a field of this
     * type and no default value was specified in the field definition. It is
     * also used to determine that a user intentionally (or unintentionally) did not
     * set a non-empty value.
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function getEmptyValue(): SPIValue
    {
        $value = new Value([
            new Row([]),
        ]);

        return $value;
    }

    /**
     * Converts an $hash to the Value defined by the field type.
     * This is the reverse operation to {@link toHash()}. At least the hash
     * format generated by {@link toHash()} must be converted in reverse.
     * Additional formats might be supported in the rare case that this is
     * necessary. See the class description for more details on a hash format.
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\SPI\FieldType\Value
     */
    public function fromHash($hash): SPIValue
    {
        foreach ($hash as $row) {
            $rows[] = new Row($row);
        }

        return new Value($rows ?? []);
    }

    /**
     * Throws an exception if value structure is not of expected format.
     * Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     * This is an operation method for {@see acceptValue()}.
     * Example implementation:
     * <code>
     *  protected function checkValueStructure( Value $value )
     *  {
     *      if ( !is_array( $value->cookies ) )
     *      {
     *          throw new InvalidArgumentException( "An array of assorted cookies was expected." );
     *      }
     *  }
     * </code>.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     */
    protected function checkValueStructure(FieldTypeValue $value)
    {
        // Value is self-contained and strong typed
        return;
    }

    /**
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value): bool
    {
        return $value->getRows()->count() === 0;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return [];
        }

        $countNonEmptyRows = 0;

        foreach ($value->getRows() as $row) {
            if (!$row->isEmpty()) {
                ++$countNonEmptyRows;
            }
        }

        if ($countNonEmptyRows < $fieldDefinition->fieldSettings['minimum_rows']) {
            $validationErrors[] = new ValidationError(
                'Matrix must contain at least %minimum_rows% non-empty rows.',
                null,
                [
                    '%minimum_rows%' => $fieldDefinition->fieldSettings['minimum_rows'],
                ],
                $fieldDefinition->getName()
            );
        }

        return $validationErrors ?? [];
    }

    /**
     * Converts the given $value into a plain hash format.
     * Converts the given $value into a plain hash format, which can be used to
     * transfer the value through plain text formats, e.g. XML, which do not
     * support complex structures like objects. See the class level doc block
     * for additional information. See the class description for more details on a hash format.
     *
     * @param \eZ\Publish\SPI\FieldType\Value $value
     *
     * @return mixed
     */
    public function toHash(SPIValue $value)
    {
        /** @var RowsCollection $rows */
        $rows = $value->getRows();

        $hash['entries'] = [];

        foreach ($rows as $row) {
            $hash['entries'][] = $row->getCells();
        }

        return $hash;
    }
}
