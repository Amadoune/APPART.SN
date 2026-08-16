# Public URL semantics

La sémantique normative est **C — route applicative stable**. Le contrat persiste un locator relatif versionné; l'URL absolue HTTPS est résolue au read-time.

Ce n'est ni une URL temporaire signée, ni une URL directe de storage, ni une URL CDN. Sa validité dépend d'une décision d'éligibilité relue à chaque GET.
