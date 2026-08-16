# Persisted versus derived URL

Persisté : MediaId, locator relatif, delivery revision et données publiques minimales. Dérivé : origine + locator → URL absolue.

Ce choix protège la portabilité environnement, les changements de host et de backend. La révocation ne repose pas sur la mutation d'une URL persistée; elle est réévaluée au GET.
