# Otium - Documentation Generator
Генерация документации API без использования Swagger, OpenApi комментариев в коде

~~Работает поверх пакета l5-swagger (пока что)~~

![compare](compare.png)

## Install 
```shell script
composer require loot/otium
```
Then run:
```
php artisan vendor:publish --provider="Loot\Otium\ServiceProvider"
```
### Config
Настройку можно сделать в файле config/otium.php

### Usage
После команды `php artisan l5-swagger:generate`, запустить:
```shell script
php artisan loot:generate-docs
```

### Otium аннотации
#### @param-otium-hint
Т.к для GET запросов не используются FormRequest, otium не может получить информацию 
для документирования. 

Для того, чтобы задокументировать параметры, которых нет в FormRequest, используйте аннотацию **@param-otium-hint**:

```php
/**
 * @param-otium-hint {"name": "search", "description": "keyword for search", "in": "query", "required": false}
 */
```
#### @param-otium-extra
В случаях, когда необходимо добавить кастомные свойства в документацию, используйте **@param-otium-extra**:
```php
/**
 * @param-otium-extra {"ENV": "LOCAL"}
 * @param-otium-extra {"params": {"timeout": 300} }
 */
```

### Todo
Roadmap доступен тут https://trello.com/b/XNh0t5g0/otiums-roadmap
