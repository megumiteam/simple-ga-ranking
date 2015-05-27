# gapiwp

Google API Library wrapper for WordPress.


## How to use

### Install

Install this libary in your theme or plugin via Composer.

To do so, you need write `commposer.json` like below.

```json
{
  "name": "your-name/your-theme",
  "description": "WordPress theme",
  "require": {
    "hametuha/gapiwp": "1.0.x"
  }
}
```

Now you can execute `composer install`. [Google API Client](https://github.com/google/google-api-php-client) is a bit bigger library, `--no-dev` option is recommended.

```
composer install --no-dev
```

### Load library

In your entry point( theme's functions.php or plugin's base file), initialize library.

```php
// Load auto loader.
include __DIR__.'/vendor/autoload.php';
// Initialize library
\Hametuha\GapiWP\Loader::load();
```

# Googla Analytics

Currently, only Google Analytics API is supported. You can now easily contact with Google Analytics data.

After initliazing the library, you can see setting screen on admin panel. Go to **Setting > Analytics Setting**.

What you should enter is...

- Client ID
- Client secret

You can get them on [Google Developers console](https://console.developers.google.com). Besides that, you have to save your admin screen URL(e.g. `http://local.sample.in/wp-admin//options-general.php?page=gapiwp-analytics`) as redirect URI. It will be treated as white-listed.

```php
// Get Google Analytics client.
$ga = \Hametuha\GapiWP\Loader::analytics();
// Get top 100 pave views of this year.
$result = $ga->fetch('2015-01-01', date_i18n('Y-m-d'), 'ga:pageviews', array(
	'max-results' => 100,
	'dimensions'  => 'ga:pagePath',
	'sort' => '-ga:pageviews'
));
// See what is retrieved.
var_dump($result);
exit;
```

[![GapiWP](http://img.youtube.com/vi/a8gMBq1Z3ZA/0.jpg)](https://www.youtube.com/watch?v=a8gMBq1Z3ZA)

## Lisence

Released under MIT lisence. See LISENCE.md.
