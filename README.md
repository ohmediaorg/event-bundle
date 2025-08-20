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

Create `config/packages/oh_media_event.yaml`, enable/disable tags based on site requirements:

```yaml
oh_media_event:
  event_tags: true
  page_template: App\Form\Page\EventPage
```

Run `php bin/console make:migration` then run the subsequent migration.

## Listing Template

The listing template can be implemented by creating
`templates/OHMediaEventBundle/event_listing.html.twig`. This template is passed
three variables: `pagination`, `event_page_path` and `tags`. Here is a basic implementation:

```twig
{% if tags %}
  <div id="tags">
    {% for tag in tags %}
      <a href="{{ tag.href }}" {%if tag.active %}class='active'{% endif %}>{{ tag.name }}</a>
    {% endfor %}

  </div>
{% endif %}

<div id="event">
  {% if pagination.results|length > 0 %}
    {% for event in pagination.results %}
      {{ dump(event) }}
    {% endfor %}
  {% else %}
    <p>No events</p>
  {% endif %}
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
