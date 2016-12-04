<?php

namespace Siso\Bundle\ContentLoaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Siso\Bundle\ContentLoaderBundle\Traits\YamlParserTrait;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\SectionService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query;

class TextModuleImporterCommand extends ContainerAwareCommand
{
    use YamlParserTrait;

    private $fileLocator;
    private $folderList = array();

    private $textModuleArray = array();


    /**
     * @var ContentService
     */
    private $contentService;
    /**
     * @var LocationService
     */
    private $locationService;
    /**
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;
    /**
     * @var SectionService
     */
    private $sectionService;
    /**
     * @var SearchService
     */
    private $searchService;

    private $ezpublishApiRepository;

    protected function configure()
    {
        $this
            ->setName('siso:textmodules:import')
            ->setDescription('Load textmdoules for silver.eShop')

            ->addArgument(
                'path',
                InputOption::VALUE_REQUIRED,
                <<<'EOD'
Path to the fixture file. It is allowed to use @Bundle shortcut syntax,
e.g. @SisoTestToolsBundle/Resources/fixtures/default/all.yml
EOD
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->fileLocator = $this->getContainer()->get('file_locator');
        $this->contentService = $this->getContainer()->get('ezpublish.api.service.content');
        $this->locationService = $this->getContainer()->get('ezpublish.api.service.location');
        $this->searchService = $this->getContainer()->get('ezpublish.api.service.search');
        $this->ezpublishApiRepository = $this->getContainer()->get('ezpublish.api.repository');
        $this->contentTypeService =  $this->getContainer()->get('ezpublish.api.service.content_type');

        $path = $input->getArgument('path');
        $textModuleData = $this->loadFromFile($path);

        $componentsId = $this->getLocationByUrl("/Components");

        if (!$this->getLocationByUrl("/Components/Textmodules")) {
            $componentsId = $this->getLocationByUrl("/Components");
            $this->createFolder($componentsId, 'Textmodules');
        }

        $this->ezpublishApiRepository->sudo(
            function () use ($textModuleData) {
                $this->extractFolders($textModuleData);
                $this->checkAndUpdateTextModule($textModuleData);
            }
        );


// print_r($this->textModuleArray);


    }


    /**
     * @inheritdoc
     */
    public function loadFromFile($path)
    {
        $path = $this->fileLocator->locate($path);
        $data = $this->loadYamlFile($path);
        return  $data;
    }

    public function checkAndUpdateTextModule($textModuleData)
    {
        $locationIdTextModules = $this->getLocationByUrl("/Components/Textmodules");
        foreach ($textModuleData['content'] as $location_code => $content) {
            if ($content['content_type'] == 'st_textmodule') {
                $name = $this->getField($content['fields'], 'name');
                $identifier = $this->getField($content['fields'], 'identifier');
                echo "Check $identifier .. \n";
                if (!$this->textModuleExists($identifier, $locationIdTextModules)) {
                    $locationId = $this->textModuleArray[$content['parent']];
                    echo "Missing textmodule for $identifier in $locationId .. \n";
                    $this->createTextmodule($locationId, $content['fields']);
                }
            }
        }
    }



    /**
     * @param array $textModuleData
     */
    public function extractFolders(array $textModuleData) {

        $locationIdTextModules = $this->getLocationByUrl("/Components/Textmodules");
        foreach ($textModuleData['content'] as $location_code => $content) {
             if ($content['content_type'] == 'folder') {
                 $name = $this->getField($content['fields'], 'name');
                 $folderId = $this->getLocationByName($locationIdTextModules, $name);
                 if (!$folderId) {
                     echo "Create new folder $name in $locationIdTextModules .. \n";
                     $folderId = $this->createFolder($locationIdTextModules, $name);
                 }
                 $this->textModuleArray[$location_code] = $folderId;
             }
        }
    }

    /**
     * @param $fields
     * @param $fieldId
     * @param bool $language
     * @return bool
     */
    private function getField($fields, $fieldId, $language=false)
    {
        if (!isset($fields[$fieldId])) {
            return false;
        }
        if ($language == false) {
            $languageList = array_keys($fields[$fieldId]);
            $language = $languageList[0];
        }
        if (isset($fields[$fieldId][$language])) {
            return $fields[$fieldId][$language];
        }
        return false;
    }

    /**
     * @param $locationId
     * @param $name
     * @return bool|mixed
     */
    private function getLocationByName($locationId, $name) {

        $contentTypeFolder = $this->contentTypeService->loadContentTypeByIdentifier('folder');

        $location = $this->locationService->loadLocation( $locationId );
        $childLocationList = $this->locationService->loadLocationChildren( $location, 0, 40 );
        // If offset and limit had been specified to something else then "all", then $childLocationList->totalCount contains the total count for iteration use
        foreach ( $childLocationList->locations as $childLocation ) {

            if (strtolower(trim($childLocation->contentInfo->name)) == strtolower(trim($name)) &&
                $contentTypeFolder->id == $childLocation->contentInfo->contentTypeId
            ) {
                return $childLocation->contentInfo->mainLocationId;
            }
        }
        return false;

    }

    /**
     * Checks if a update is required
     * @param $url
     * @return locationId
     */
    private function getLocationByUrl($url)
    {
        $urlAliasService = $this->ezpublishApiRepository->getURLAliasService();
        try {
            $urlLocation = $urlAliasService->lookup($url);
        } catch (\Exception $e) {
            return false;
        }
        return $urlLocation->destination;
    }

    /**
     * @param $locationId
     * @param $folderName
     * @return mixed
     */
    private function createFolder($locationId, $folderName)
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $contentCreateStruct = $this->contentService->newContentCreateStruct( $contentType, 'eng-GB' );

        $contentCreateStruct->setField( 'name', $folderName);
        $contentCreateStruct->alwaysAvailable = true;

        $locationCreateStruct = $this->locationService->newLocationCreateStruct( $locationId );
        $draft = $this->contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
        $result = $this->contentService->publishVersion( $draft->versionInfo );

        return $result->contentInfo->mainLocationId;
    }

    /**
     * @param $locationId
     * @param $data
     * @return mixed
     */
    private function createTextmodule($locationId, $data)
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier( 'st_textmodule' );

        $languageList = array_keys($data['name']);

        // Create doc in first language
        $contentCreateStruct = $this->contentService->newContentCreateStruct( $contentType, $languageList[0] );
        $identifier = $data['identifier'][$languageList[0]];
        $contentCreateStruct->setField( 'name', $data['name'][$languageList[0]]);
        $contentCreateStruct->setField( 'identifier', $identifier);

        $locationCreateStruct = $this->locationService->newLocationCreateStruct( $locationId );
        $draft = $this->contentService->createContent( $contentCreateStruct, array( $locationCreateStruct ) );
        $result = $this->contentService->publishVersion( $draft->versionInfo );



        // update all languages
        foreach ($languageList as $language) {
            
            $contentDraft = $this->contentService->createContentDraft( $result->contentInfo );

            $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
            $contentUpdateStruct->initialLanguageCode = $language; // set language for new version
            $contentUpdateStruct->setField( 'name', $data['name'][$language]);
            $contentUpdateStruct->setField( 'identifier', $identifier);
            $contentUpdateStruct->setField( 'context', $data['context'][$language]);
            $contentUpdateStruct->setField( 'content', $data['content'][$language]);

            $contentDraft = $this->contentService->updateContent( $contentDraft->versionInfo, $contentUpdateStruct );
            $this->contentService->publishVersion( $contentDraft->versionInfo );
        }

        return $result->contentInfo->mainLocationId;
    }

    /**
     * @param $textmoduleId
     * @param $textmoduleLocationId
     * @return bool
     */
    private function textModuleExists($textmoduleId, $textmoduleLocationId)
    {
        $query = new Query();

        /** @var \eZ\Publish\API\Repository\LocationService $locationService */
        $location = $this->locationService->loadLocation($textmoduleLocationId);
        $criteria = array(
            new Criterion\Subtree($location->pathString),
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\Field('identifier', Criterion\Operator::EQ, $textmoduleId),
        );

        $query->criterion = new Criterion\LogicalAnd($criteria);

        $languageFilter = array();
        $languageFilter['useAlwaysAvailable'] = true;
        try {
            $searchResults = $this->searchService->findContent($query, $languageFilter);
        } catch (\Exception $e) {
            return false;
        }

        if ($searchResults->totalCount !== 0) {
            return true;
        }
        return false;
    }

}