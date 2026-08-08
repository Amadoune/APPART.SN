# Risk Register — Legacy Migration Runtime

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| disponibilité interprétée comme readiness métier | critique | type dédié et diagnostics minimaux | faible |
| `Missing` transformé en `Empty` | élevé | réduction explicite vers `Available` uniquement | faible |
| panne technique masquée | élevé | exception et dépendance vers `DependencyUnavailable` | faible |
| corruption masquée | élevé | réduction fermée vers `Corrupted` | faible |
| fuite de diagnostics PostgreSQL | élevé | catalogue limité à trois champs | faible |
| dérive de migration 084 | critique | doubles empreintes SHA-256 en Architecture | faible |
| binding ambigu ou multiple | moyen | aliases nominatifs et enregistrement unique | faible |

Le risque résiduel n'autorise aucune décision de migration ou ouverture implicite d'une surface ultérieure.
