# MLBB Query

Standalone module for querying Mobile Legends: Bang Bang game data. Built on top of [mobaguides/mobile-legends-api](https://github.com/mobaguides/mobile-legends-api) and intended for later integration into the Wasit admin dashboard.

## Requirements

- PHP 8.2+
- Composer
- Internet access to `mapi.mobilelegends.com`

## Installation

From the project root:

```bash
cd mlbb-query
composer install
```

## CLI usage

```bash
# List all heroes
php bin/mlbb heroes

# Hero details by ID
php bin/mlbb hero 1

# Search heroes by name or title
php bin/mlbb search miya

# Resolve an icon key from the ML image map
php bin/mlbb image HeroHead001.png

# Hero avatar URL by hero ID
php bin/mlbb avatar 1
```

## PHP usage

```php
<?php

require __DIR__.'/mlbb-query/vendor/autoload.php';

use Wasit\MlbbQuery\MlbbQueryService;

$mlbb = new MlbbQueryService();

$heroes = $mlbb->listHeroes();
$hero = $mlbb->getHero(1);
$results = $mlbb->searchHeroes('miya');
$avatar = $mlbb->getHeroAvatarUrl(1);
```

## What the upstream package supports

Per the upstream library:

- Heroes list
- Hero details (skills, skins, story, etc.)
- Image/icon URL lookup

Not currently supported upstream:

- Equipment/items
- Emblems
- Map data
- User/account data

## Laravel integration (later)

When you are ready to wire this into the admin dashboard, you can either:

1. Add a path repository in the main app's `composer.json` and autoload `Wasit\MlbbQuery`
2. Bind `MlbbQueryService` in a Laravel service provider and expose Filament actions or API routes

This folder stays isolated until that integration step.
