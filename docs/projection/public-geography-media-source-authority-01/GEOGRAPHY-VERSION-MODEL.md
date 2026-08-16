# Geography version model

La version initiale est positive. Une version supérieure est `Advance`; identité complète identique à version égale est `AlreadyStable`; version inférieure est `Obsolete`; même version avec checksum ou causalité différente est `Divergent`.

Le writer implémente `Applied`, `AlreadyApplied`, `RejectedObsolete`, `Divergent`. La stratégie future doit dériver la séquence d'une révision Geography owner certifiée, sans compteur Projection.
