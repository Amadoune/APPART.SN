# Notifications HTTP Foundation

La façade publique expose trois endpoints GET owner-scoped : `/api/notifications/preference`, `/api/notifications/template` et `/api/notifications/channel`. Chaque contrôleur dépend exclusivement de son Reader public V1 et accepte uniquement `subjectKey` et `observedAt`.

Aucune résolution, décision métier ou lecture technique n'est réalisée dans HTTP.
