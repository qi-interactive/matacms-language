MATA CMS Language
==========================================

![MATA CMS Module](https://s3-eu-west-1.amazonaws.com/qi-interactive/assets/mata-cms/gear-mata-logo%402x.png)


Language module manages languages for entities.


Installation
------------

- Add the module using composer:

```json
"matacms/matacms-language": "~1.0.0"
```

Changelog
---------

## 1.0.1.1-alpha, October 30, 2015

- Updated getSupportedLanguages() method to return default supported language from the application as associative array

## 1.0.1-alpha, October 8, 2015

- Added LanguageMapping with migration
- Added LanguageBehavior for saving and deleting LanguageMapping
- Updated Bootstrap with ActiveQuery::EVENT_BEFORE_PREPARE_STATEMENT to be used only for yii\web\Application on models with LanguageBehavior

## 1.0.0-alpha, September 18, 2015

- Initial release.
