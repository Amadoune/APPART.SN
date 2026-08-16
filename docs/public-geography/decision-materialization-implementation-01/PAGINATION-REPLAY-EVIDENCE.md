# Pagination and replay evidence

Ordre `place_id ASC`; nextCursor opaque; pas de progress registry. Un retry recommence page 1. Le writer retourne AlreadyApplied pour les terminaux déjà écrits et poursuit les pages restantes. Chaque terminal conserve sa transaction owner-locale.
