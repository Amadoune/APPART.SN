# Risk Register

| Risque | Maîtrise | Résiduel |
|---|---|---|
| endpoint créé pour un Reader sans source | trois surfaces interdites vérifiées par Architecture | faible |
| dépendance directe à Persistence | Controllers limités aux cinq Readers V1 | faible |
| fuite de données sensibles | réponse fermée à status et observedAt | faible |
| mise en cache ou sniffing | headers no-store et nosniff systématiques | faible |
| champs inconnus acceptés | validation stricte des query parameters | faible |
| mapping incomplet | match exhaustif sur chaque enum fermée | faible |
