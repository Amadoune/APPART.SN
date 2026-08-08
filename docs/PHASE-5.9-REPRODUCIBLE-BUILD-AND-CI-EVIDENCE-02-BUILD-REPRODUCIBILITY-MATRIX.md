# Build Reproducibility Matrix

| Maillon | Preuve | Statut initial |
|---|---|---|
| source immuable | baseline + tag annoté | PASS |
| runtime pinning | runtime lock + SHAs/digest | PASS |
| dépendances verrouillées | lockfiles + commandes CI | PARTIAL |
| CI réelle | workflow versionné | PARTIAL |
| clean-room | script/procédure | PARTIAL |
| artefact complet | spécification + script | PARTIAL |
| packaging déterministe | writer USTAR canonique | PASS conception |
| checksums | génération scriptée | PARTIAL |
| manifeste | schéma/générateur | PARTIAL |
| reproduction indépendante | aucune exécution externe | MISSING |

Les statuts ne deviennent PASS qu'après preuves terminales.
