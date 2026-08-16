# Hostname boundary

Le store Public Media conserve uniquement le locator relatif. Un `PublicMediaOrigin` configuré et validé par environnement produit l'URL absolue au read/render-time.

L'origine doit être scheme + host (+ port explicite si nécessaire), sans path/query/fragment. HTTPS est obligatoire hors environnement de test isolé; `appart.test` est l'origine locale attendue. Aucun host n'est hardcodé dans le domaine ou le payload persisté.
