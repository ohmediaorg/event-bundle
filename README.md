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

# Integration

The `event-bundle` is expected to integrate with the `page-bundle` via placing
the `events()` shortcode inside a page's WYSIWYG content.

## Listing Template

The listing template can be implemented by creating
`templates/OHMediaEventBundle/event_listing.html.twig`. This template is passed
two variables: `pagination` and `events_page_path`. Here is a basic implementation:

```twig
<div id="events">
{% for event in pagination.results %}
  {% set href = page_path(events_page_path ~ '/' ~ event.slug) %}
  <a href="{{ href }}">{{ event }}</a>

  {{ dump(event) }}
{% endfor %}
</div>

{{ bootstrap_pagination(pagination) }}
{{ bootstrap_pagination_info(pagination) }}
```

## Item Template

The item template can be implemented by creating
`templates/OHMediaEventBundle/event_item.html.twig`. This template is passed
two variables: `event` and `events_page_path`. Here is a basic implementation:

```twig
{{ dump(event) }}

<a href="page_path(events_page_path)">View All Events</a>
```
