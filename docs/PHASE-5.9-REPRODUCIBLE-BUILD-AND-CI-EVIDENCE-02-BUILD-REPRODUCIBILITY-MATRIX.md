# Build Reproducibility Matrix

| Maillon | Preuve | Statut initial |
|---|---|---|
| source immuable | baseline + tag annoté | PASS |
| runtime pinning | runtime lock + SHAs/digest | PASS |
| dépendances verrouillées | restore clean-room local PASS, CI externe absente | PARTIAL |
| CI réelle | workflow versionné | PARTIAL |
| clean-room | Unit/Feature PASS, Architecture FAIL | FAIL |
| artefact complet | spécification + script, exécution bloquée | BLOCKED |
| packaging déterministe | writer USTAR canonique | PASS conception |
| checksums | génération scriptée, exécution bloquée | BLOCKED |
| manifeste | schéma/générateur, aucune instance PASS | BLOCKED |
| reproduction indépendante | aucune exécution externe | MISSING |

Les statuts ne deviennent PASS qu'après preuves terminales.
