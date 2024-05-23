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

# Events

Information for multiple Events can be managed, include name, event info,
and hours.

A Event can be marked as primary, making it available via the `event_primary()`
Twig function.

All Events can be retrieved using the `events()` Twig function.

## Schema

All Events schema can be output via `{{ events_schema() }}`.

A single Event's schema can be output via `{{ event_schema(event) }}`.

A particular Event's schema will not be output more than once.

## Event Data

The Event has the following properties available in the template:

```twig
{{ event.name }}
{{ event.address }}
{{ event.city }}
{{ event.province }} {# 2 letter code if Canada or US #}
{{ event.country }} {# 3 letter code #}
{{ event.postalCode }}
{{ event.email }}
{{ event.phone }}
{{ event.primary }} {# true|false indicating if this event is primary #}
```

The only values that can be blank are `email` and `phone`.

### Displaying Hours

```twig
{% for day, hours in event.hoursFormatted %}
<p><b>{{ day }}:</b> {{ hours }}</p>
{% endif %}
```

### Displaying Today's Hours

If a client wants to display the current hours for the main event in the
website header, that would look something like this:

```twig
{% set primary_event = event_primary() %}

{% if primary_event %}
  {% set hours_formatted = primary_event.hoursFormatted %}
  {% set today = "now"|datetime('l') %}
  <p><b>Today's hours:</b> {{ hours_formatted[today] }}</p>
{% endif %}
```

# Event Form

The event form can be output using `{{ event_form() }}`. It will include a
recipient selection based on the backend settings as well as each Event that
has an email populated.
