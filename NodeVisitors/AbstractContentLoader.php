<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

/**
 * Abstract loader for features based on content model: content, users, user groups.
 */
abstract class AbstractContentLoader extends AbstractValueObjectLoader
{
    /**
     * @inheritdoc
     */
    public function fillValueObject($valueObject, &$data, $excludedProperties =[])
    {
        parent::fillValueObject($valueObject, $data, array_merge(['fields'], $excludedProperties));
        $valueObject->mainLanguageCode = $this->getContentDataMainLanguage($data);
        foreach ($data['fields'] as $fieldIdentifier => $translations) {
            foreach ($translations as $languageCode => $fieldValue) {
                try {
                    if (!is_null($fieldValue))
                    {
                        $valueObject->setField($fieldIdentifier, $fieldValue, $languageCode);
                    }

                } catch (\Exception $e) {
                    echo "Field error with ".$fieldIdentifier. "\n";
                }

            }
        }
    }

    /**
     * Get main language from content data definitions.
     * By agreement it is the first language of the first field.
     *
     * @param array $data
     * @return string
     */
    private function getContentDataMainLanguage($data)
    {
        // Get first language of the first field
        $fieldNames = array_keys($data['fields']);
        $languages = array_keys($data['fields'][$fieldNames[0]]);

        return $languages[0];
    }
}