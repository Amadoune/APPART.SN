# Current Storage Audit

| Stockage | Owner / clé | Contenu | Aggregate reconstructible |
|---|---|---|---|
| geography.place_lifecycle_transitions | Geography lifecycle / placeId+version | transitions, états lifecycle, contexte/intent | Non : nom, code, parent, coordonnées, aliases absents |
| geography.place_lifecycle_event_inbox | consumer routing / message | livraisons lifecycle | Non |
| geography.public_projection_outbox_* | producer Geography vers Projection / messages | transport et replay | Non ; pas une source Aggregate |
| public_geography.decisions | Public Geography/Projection / placeId+revision | locality, breadcrumb public | Non ; dérivé aval |

Aucune table `geography.places` ou aliases n'est présente. Les fixtures/fakes et valeurs locales ne sont pas des données autoritatives.

Même si les tables lifecycle ou Projection contiennent des lignes locales, celles-ci ne portent pas un snapshot complet et ne sont pas migrables automatiquement en Aggregate.
