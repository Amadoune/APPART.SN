# Environment boundary

Le locator est portable entre local, test, staging et production. Chaque environnement fournit explicitement son `PublicMediaOrigin`; absence, origine invalide ou HTTP non autorisé → fail-closed.

Une base copiée entre environnements ne transporte donc pas un host local. Les tests injectent une origine dédiée, sans dépendre de `APP_URL` implicite.
