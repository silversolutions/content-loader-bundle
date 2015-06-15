# ContentLoaderBundle

[![Build Status](https://travis-ci.org/silversolutions/content-loader-bundle.svg?branch=TravisConfig)](https://travis-ci.org/silversolutions/content-loader-bundle)

There is 2 main ideas behind this bundle:

 * Load collections of items (content types, user roles, users, content items) to eZ Publish database from straight-forward yaml files.
 * Load fixtures for tests.

You can describe an initial state of your application in yaml format and use it either for project installation or for running tests in a test environment.

Example. Lets consider you have a file Resources/example/article.yml:

```yaml
languages:
    english:
        language_code: eng-US
        name: English (US)
        
content_types:
  article:
      identifier: content_article
      names:
          eng-US: Article
      name_schema: <name>
      field_definitions:
          name:
              identifier: name
              field_type_identifier: ezstring
              names:
                  eng-US: Name
          ses_short_description:
              identifier: description
              field_type_identifier: ezxmltext
              names:
                  eng-US: 'Description'
                  
content:                  
    impressum:
      content_type: article
          fields:
              title:
                  eng-US: Impressum
              intro:
                  eng-US: <paragraph>This article describes how to use ContentLoaderBundle.</paragraph>
```

After you run a command:
```bash
ezpublish/console siso:fixtures:load /path/to/article.yml
```

... you'll have:
* new content language 'English (US)' with the code 'eng-US',
* new content type 'content_article',
* and new content item 'Impressum' created under the eZ Publish main node.


### Supported features
* Content languages
* Content types
* Roles
* User groups
* Users
* Content items

### Unsupported features
* Sections
* Object states

### Limitations
* Currently the bundle mainly useful for creating new items. Update of existing content is supported only for content types.
* DatabaseSchemaLoader supports only mysql

### See also
 * [eZ Publish](http://en.wikipedia.org/wiki/EZ_Publish)
 * [Old eZ Publish 4 package system](https://doc.ez.no/eZ-Publish/Technical-manual/4.x/Features/Packages)
 * [DoctrineFixturesBundle](http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) which was a source of inspiration for this bundle.
