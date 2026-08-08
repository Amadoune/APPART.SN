# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| fuite de secret, clé, PII ou contenu d'audit | schéma limité au statut public et aux métadonnées temporelles | faible |
| divergence concurrente | advisory lock, clé primaire, `FOR UPDATE`, checksum et classification fermée | faible |
| lecture temporelle ambiguë | ordre total documenté et index dédié | faible |
| corruption silencieuse | SHA-256 canonique vérifié au mapping | faible |
| rollback externe compromis | savepoint local sans commit de la transaction appelante | faible |
| extension implicite vers une Foundation ultérieure | tests Architecture et dépendances interdites | faible |
