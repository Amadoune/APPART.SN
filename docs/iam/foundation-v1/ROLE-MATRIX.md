# Publication Review — Role Matrix

| Option | Ownership | Auditabilité | Compatibilité | Évolutivité | Séparation | Décision |
|---|---|---|---|---|---|---|
| A — réutiliser `moderator` | ambigu entre Report Moderation et Publication Review | faible : un même rôle masque deux finalités | incompatible avec le Discovery | couplage permanent | responsabilités confondues | REJETÉE |
| B — rôle dédié `publication_reviewer` | IdentityAccess explicite | forte : affectation et capacité traçables | additive, sans modifier `moderator` | capacités attribuables séparément | frontières préservées | RETENUE |
| C — droits ad hoc sans rôle qualifié | IdentityAccess possible mais représentation non stabilisée | partielle | risque d'assignments implicites | flexible mais non gouvernée | séparation non lisible | REJETÉE |

## Attribution V1

| Rôle | Read Queue | Claim | Begin Review | Approve Publication |
|---|---:|---:|---:|---:|
| `publication_reviewer` | oui | oui | oui | oui |
| `moderator` | non | non | non | non |

Un même rôle exerce Begin et Approve en V1. L'autorisation demeure distincte pour chaque capacité ; aucune équivalence globale « reviewer = tout autorisé » n'est exposée par le Reader.
