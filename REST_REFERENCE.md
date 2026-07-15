# REST Reference

## Recently Viewed Products

Endpoint:

```text
POST /wp-json/agency-commerce-core/v1/recently-viewed
```

Request body:

```json
{
  "ids": [123, 456]
}
```

Response:

```json
{
  "html": "<ul class=\"products columns-2\">...</ul>"
}
```

Validation:

- `ids` must be an array.
- Each ID must be a positive integer.
- The request accepts at most 24 submitted IDs.
- Runtime output is limited by the `recently_viewed.count` setting.

Privacy:

- Viewed IDs are stored in browser `localStorage`.
- The endpoint renders supplied product IDs only.
- No personal browsing history is stored in the database by this feature.

Errors:

- Invalid IDs return `400` with code
  `agency_commerce_core_invalid_recently_viewed_ids`.
