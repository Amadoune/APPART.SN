# Unavailable and deleted semantics

Unknown, private, unready, detached, removed, archived, deleted, stale revision et Listing non Published retournent tous **HTTP 404**. Cette uniformité évite l'énumération et l'IDOR.

Une indisponibilité d'infrastructure interne peut produire 503 sans confirmer l'existence. 403 et 410 sont exclus de V1 car ils divulgueraient une distinction d'état.
