# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| disponibilité confondue avec un état métier | catalogue Runtime distinct et réduction mécanique | faible |
| exposition de données sensibles | diagnostics limités à ID, version et disponibilité | faible |
| accès direct à PostgreSQL depuis le Runtime | dépendance Application exclusive au port Owner Source | faible |
| fallback implicite | catalogue fermé et tests exhaustifs | faible |
| modification de Persistence | empreintes SHA-256 et preuve Architecture | faible |
| Provider multiple ou eager | enregistrement unique et singletons lazy nominatifs | faible |
