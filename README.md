# Laravel Helpers

Helper Generator for Laravel

## Installation

Install via composer

```bash
composer require devtical/laravel-helpers
```

#### Optional

Publish the config:

```bash
php artisan vendor:publish --tag=helpers-config
```

Configure your helper directory:

```bash
# In your .env file
HELPER_DIRECTORY=Helpers
HELPER_LOG_ERRORS=true
HELPER_STRICT=false
```

## Usage

Create your first helper file:

```bash
php artisan make:helper <NAME>
```

Add your helper functions:

```php
<?php

declare(strict_types=1);

if (! function_exists('str_slug')) {
    function str_slug($text, $separator = '-')
    {
        return Str::slug($text, $separator);
    }
}
```

Use your helper functions anywhere:

```php
// In your controllers, models, views, etc.
$slug = str_slug('Hello World'); // Returns: hello-world
```

## Commands

| Command           | Description                                                  |
| ----------------- | ------------------------------------------------------------ |
| `make:helper`     | Create a new helper file                                     |
| `helper:list`     | List all helper files and their load status                  |
| `helper:validate` | Check helper files for syntax errors and duplicate functions |
| `helper:reload`   | Reload helper files (useful during development)              |

Please see the [changelog](CHANGELOG.md) for more information on what has changed recently.

## Testing

```bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email author instead of using the issue tracker.

## Credits

- [W Kristianto](https://github.com/kristories)
- [All contributors](https://github.com/devtical/laravel-helpers/graphs/contributors)

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

If you have any questions or need help, please open an issue on GitHub.
