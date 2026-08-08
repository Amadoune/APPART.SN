# Build Reproducibility Matrix

| Exigence | Preuve observée | Statut | Écart/action requise |
|---|---|---|---|
| commit candidat exact | HEAD 12cf192 mais état courant non commité | BLOCKED | autorité distincte nécessaire pour créer un commit candidat propre |
| worktree propre | 1 236 entrées porcelain | BLOCKED | aucun candidat possible avant assainissement autorisé |
| locks PHP/JS | composer.lock et package-lock.json présents | PASS | préserver les hashes |
| Composer déterministe | validate strict et platform requirements PASS | PARTIAL | clean install vierge non exécuté |
| npm déterministe | lockfile v3, npm ls PASS | PARTIAL | npm ci vierge non démontré |
| build front-end | deux builds locaux identiques | PASS | preuve seulement locale et provisoire |
| build PHP/package release | aucun script de packaging | MISSING | spécifier un artefact complet |
| clean-room | aucun Dockerfile, image ou procédure exécutable | MISSING | matérialisation technique séparée requise |
| CI | aucun .github/workflows ni autre pipeline | MISSING | pipeline déterministe à autoriser séparément |
| archivage | public/build ignoré et aucun artifact store | MISSING | définir rétention et provenance |
| secret scan | aucune chaîne sensible trouvée dans public/build | PARTIAL | scan du paquet complet impossible |
| reproduction tierce | prérequis incomplets et sources non commitées | BLOCKED | chaîne probatoire non démontrable |

