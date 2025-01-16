# Laravel OpenProvider

A Laravel package for integrating with the OpenProvider API.

## Installation

You can install the package via composer:

```bash
composer require yourusername/laravel-openprovider
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="openprovider-config"
```

Add these environment variables to your .env file:

```env
OPENPROVIDER_URL=https://api.openprovider.eu
OPENPROVIDER_USERNAME=your_username
OPENPROVIDER_PASSWORD=your_password
OPENPROVIDER_HASH=your_hash_if_needed
```

## Usage

```php
use jacktalkc\LaravelOpenProvider\Facades\OpenProvider;

// Check domain availability
$result = OpenProvider::checkDomain('example.com');

// Search domains
$domains = OpenProvider::searchDomains([
    'extension' => 'com',
    'status' => 'active'
]);

// Get domain info
$info = OpenProvider::getDomainInfo('example.com');
```

## Testing

```bash
composer test
```
## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

// LICENSE.md
MIT License

Copyright (c) 2025 Dream Hunter

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
