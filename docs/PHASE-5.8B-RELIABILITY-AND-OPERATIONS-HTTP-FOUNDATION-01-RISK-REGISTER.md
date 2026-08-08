# Phase 5.8B — Reliability & Operations — HTTP Risk Register

| Risque | Contrôle | Résiduel |
|---|---|---|
| exposition d'un payload opérationnel | deux champs fermés | faible |
| cache d'un état sensible | no-store | faible |
| content sniffing | nosniff | faible |
| paramètre inconnu | validation stricte | faible |
| mapping implicite | enums exhaustifs sans default | faible |
| accès direct OwnerSource | Controllers typés Reader V1 | faible |
| ouverture Event implicite | interdiction normative | faible |
| modification migration 088 | preuve SHA-256 | faible |
