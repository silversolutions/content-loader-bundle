<?php

namespace Siso\Bundle\ContentLoaderBundle\Tests\TestCases;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;

/**
 * Base TestCase for testing classes that use eZ Publish repository services
  */
abstract class RepositoryTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $parameters
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentServiceMock($parameters)
    {
        $contentItem = $this->getMock('eZ\Publish\Core\Repository\Values\Content\Content');
        $contentItem
            ->expects($this->any())
            ->method('__get')
            ->with($this->equalTo('versionInfo'))
            ->willReturn($this->getMock('eZ\Publish\API\Repository\Values\Content\VersionInfo'));

        $contentService = $this->getMock('eZ\Publish\API\Repository\ContentService');
        $contentService
            ->expects($this->getMethodExpectation($parameters, 'new_content_create_struct_call'))
            ->method('newContentCreateStruct')
            ->willReturn($this->getMock('eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct'));

        $contentService
            ->expects($this->any())
            ->method('createContent')
            ->willReturn($contentItem);

        return $contentService;
    }

    /**
     * @param $parameters
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getLocationServiceMock($parameters)
    {
        $locationService = $this->getMock('eZ\Publish\API\Repository\LocationService');
        $locationService
            ->expects($this->getMethodExpectation($parameters, 'new_location_create_struct_call'))
            ->method('newLocationCreateStruct')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\Values\Content\LocationCreateStruct'));

        return $locationService;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentTypeServiceMock($parameters)
    {
        $contentTypeService = $this->getMock('eZ\Publish\API\Repository\ContentTypeService');

        if(isset($parameters['loaded_content_type_kind']) && $parameters['loaded_content_type_kind'] == 'empty') {
            $contentTypeService
                ->expects($this->any())
                ->method('loadContentTypeByIdentifier')
                ->willReturn([new ContentType(['fieldDefinitions' => []])]);
        } else {
            $contentTypeService
                ->expects($this->any())
                ->method('loadContentTypeByIdentifier')
                ->willThrowException(new NotFoundException('No content type', 'dummy'));
        }

        $contentTypeService
            ->expects($this->getMethodExpectation($parameters, 'new_content_type_create_struct_call'))
            ->method('newContentTypeCreateStruct')
            ->willReturn($this->getMock('eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct'));

        $contentTypeService
            ->expects($this->getMethodExpectation($parameters, 'create_content_type_call'))
            ->method('createContentType')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft'));

        $contentTypeService
            ->expects($this->getMethodExpectation($parameters, 'new_field_definition_create_struct_call'))
            ->method('newFieldDefinitionCreateStruct')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct'));

        return $contentTypeService;
    }

    /**
     * @param $parameters
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getRoleServiceMock($parameters)
    {
        $roleService = $this->getMock('eZ\Publish\API\Repository\RoleService');
        $roleService
            ->expects($this->getMethodExpectation($parameters, 'create_role_call'))
            ->method('createRole');

        $roleService
            ->expects($this->getMethodExpectation($parameters, 'new_role_create_struct_call'))
            ->method('newRoleCreateStruct')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\Values\User\RoleCreateStruct'));

        return $roleService;
    }

    /**
     * @param $parameters
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getContentLanguageServiceMock($parameters)
    {
        $contentLanguageService = $this->getMock('eZ\Publish\API\Repository\LanguageService');

        $contentLanguageService
            ->expects($this->any())
            ->method('loadLanguages')
            ->willReturn([]);

        $contentLanguageService
            ->expects($this->getMethodExpectation($parameters, 'create_language_call'))
            ->method('createLanguage');

        $contentLanguageService
            ->expects($this->getMethodExpectation($parameters, 'new_language_create_struct_call'))
            ->method('newLanguageCreateStruct')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct'));

        return $contentLanguageService;
    }    
    
    /**
     * @param array $parameters
     * @param string $parameter
     * @return \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private function getMethodExpectation(array $parameters, $parameter)
    {
        return isset($parameters[$parameter]) ? $parameters[$parameter] : $this->any();
    }
}
