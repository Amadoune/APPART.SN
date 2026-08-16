# Mutation Refresh Evidence

Le consumer Media lifecycle résout le Listing publié affecté à partir du MediaId, puis appelle le même matérialiseur. Le scope reste un média → une collection → un Listing publié ; aucun scan global.

Attach, retrait/archive, reorder, primary et changements de version/readiness sont capturés par les versions owner-side du vecteur. L'intégration PostgreSQL démontre revision 2→3 et retrait vers `SourceNotReady`.
