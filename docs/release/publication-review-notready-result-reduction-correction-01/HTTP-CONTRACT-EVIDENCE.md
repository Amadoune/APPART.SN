# HTTP Contract Evidence

The targeted real-adapter Feature test submits the existing approve form contract through the authenticated route and injects a runtime `NotReady` result.

Observed contract:

- route: `POST /publication-review/{queueItemId}/approve`;
- runtime result: `not_ready`;
- HTTP status: 200, unchanged;
- rendered state: non-success;
- success copy: absent.

Forbidden, NotFound, Conflict and DependencyUnavailable keep their existing exception/status mappings. No JSON taxonomy or API field changed.
