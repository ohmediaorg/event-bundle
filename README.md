# Installation

Update `composer.json` by adding this to the `repositories` array:

```json
{
    "type": "vcs",
    "url": "https://github.com/ohmediaorg/event-bundle"
}
```

Then run `composer require ohmediaorg/event-bundle:dev-main`.

Import the routes in `config/routes.yaml`:

```yaml
oh_media_event:
    resource: '@OHMediaEventBundle/config/routes.yaml'
```

Run `php bin/console make:migration` then run the subsequent migration.
