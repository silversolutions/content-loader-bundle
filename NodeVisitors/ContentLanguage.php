<?php

namespace Siso\Bundle\ContentLoaderBundle\NodeVisitors;

use Siso\Bundle\ContentLoaderBundle\Interfaces\TreeNodeInterface;
use eZ\Publish\API\Repository\LanguageService;

/**
 * Loader for content languages
 */
class ContentLanguage extends AbstractValueObjectLoader
{
    /**
     * @var LanguageService
     */
    private $languageService;

    /**
     * @param LanguageService $languageService
     */
    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedPath()
    {
        return '/languages/*';
    }

    /**
     * @inheritdoc
     */
    public function visit(TreeNodeInterface $node, &$data)
    {
        $languages = $this->languageService->loadLanguages();

        $existingLanguageCodes = [];
        foreach ($languages as $language) {
            $existingLanguageCodes[] = $language->languageCode;
        }

        // If language already exists, skip it
        if(in_array($data['language_code'], $existingLanguageCodes)) {
            return;
        }

        $struct = $this->languageService->newLanguageCreateStruct();
        $this->fillValueObject($struct, $data);
        $this->languageService->createLanguage($struct);
    }
}