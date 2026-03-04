<?php
/**
 * File containing the BinaryFile Type class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace Netgen\Bundle\EnhancedBinaryFileBundle\Core\FieldType\EnhancedBinaryFile;

use Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException;
use Ibexa\Contracts\Core\FieldType\BinaryBase\RouteAwarePathGenerator;
use Ibexa\Contracts\Core\IO\MimeTypeDetector;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\FieldType\BinaryFile\Type as BinaryFileType;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\IOMimeTypeDetector;

class Type extends BinaryFileType
{
    /**
     * A settings whitelist used in validateFieldSettings method.
     *
     * @var array
     */
    protected $settingsSchema = [
        'allowedTypes' => [
            'type' => 'string',
            'default' => null,
        ],
        'mimeTypesMessage' => [
            'type' => 'string',
            'default' => null,
        ],
    ];

    /**
     * @var \Ibexa\Contracts\Core\IOMimeTypeDetector
     */
    protected $mimeTypeDetector;

    /**
     * @var ConfigResolverInterface
     */
    protected $configResolver;

    public function __construct(MimeTypeDetector $mimeTypeDetector, ConfigResolverInterface $configResolver, array $validators, ?RouteAwarePathGenerator $routeAwarePathGenerator = null)
    {
        $this->mimeTypeDetector = $mimeTypeDetector;
        $this->configResolver = $configResolver;
        parent::__construct($validators, $routeAwarePathGenerator);
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \Ibexa\Core\FieldType\BinaryFile\Value
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Returns the field type identifier for this field type.
     *
     * @return string
     */
    public function getFieldTypeIdentifier(): string
    {
        return 'enhancedezbinaryfile';
    }

    /**
     * Validates a field based on the validators in the field definition.
     *
     *
     * @param FieldDefinition $fieldDefinition The field definition of the field
     * @param \Ibexa\Core\FieldType\BinaryBase\Value $fieldValue The field value for which an action is performed
     *
     * @throws InvalidArgumentException
     *
     * @return \Ibexa\Contracts\Core\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $fieldValue)
    {
        $errors = [];

        if ($this->isEmptyValue($fieldValue)) {
            return $errors;
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();
        $allowedExtensions = explode('|', $fieldSettings['allowedTypes']);

        $mimeType = $this->mimeTypeDetector->getFromPath($fieldValue->inputUri);

        foreach ($allowedExtensions as $allowedExtension) {
            if ($this->configResolver->hasParameter("{$allowedExtension}.Types", 'mime')) {
                $allowedMimeTypes = $this->configResolver->getParameter("{$allowedExtension}.Types", 'mime');

                if (in_array($mimeType, $allowedMimeTypes, true)) {
                    return parent::validate($fieldDefinition, $fieldValue);
                }
            }
        }

        return [
            new ValidationError(
                'This mimeType is not allowed %mimeType%.',
                'These mimeTypes are not allowed %mimeType%.',
                [
                    'mimeType' => $mimeType,
                ]
            ),
        ];
    }

    /**
     * Validates the fieldSettings of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct.
     *
     * This method expects that given $fieldSettings are complete, for this purpose method
     * {@link self::applyDefaultSettings()} is provided.
     *
     * @param mixed $fieldSettings
     *
     * @return \Ibexa\Contracts\Core\FieldType\ValidationError[]
     */
    public function validateFieldSettings($fieldSettings)
    {
        $validationErrors = [];

        if (!is_array($fieldSettings)) {
            $validationErrors[] = new ValidationError('Field settings must be in form of an array');

            return $validationErrors;
        }

        foreach ($fieldSettings as $name => $value) {
            switch ($name) {
                case 'mimeTypesMessage': // break omitted on purpose
                case 'allowedTypes':
                    // Nothing to validate, just recognize this setting as known
                    break;
                default:
                    $validationErrors[] = new ValidationError(
                        "Setting '%setting%' is unknown",
                        null,
                        [
                            'setting' => $name,
                        ]
                    );
                    break;
            }
        }

        return $validationErrors;
    }

    /**
     * Creates a specific value of the derived class from $inputValue.
     *
     * @param array $inputValue
     *
     * @return Value
     */
    protected function createValue(array $inputValue)
    {
        return new Value($inputValue);
    }
}
