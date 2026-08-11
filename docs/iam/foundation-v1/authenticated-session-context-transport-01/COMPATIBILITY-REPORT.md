# Compatibility Report

## Compatibilité contractuelle

La décision est additive au modèle conceptuel : elle ne modifie ni le format du cookie, ni les routes, ni les réponses HTTP, ni la Security Policy. Le contexte reste invisible aux clients.

Une future implémentation devra faire évoluer de manière coordonnée :

- `IdentityAccessSessionInspection`, afin de porter le contexte uniquement lorsqu'elle est valide ;
- `RequireIdentityAccessSession`, afin de transporter l'objet typé plutôt que le seul AccountId ;
- `IdentityAccessHttpCommand`, afin de recevoir un contexte authentifié optionnel pour les opérations protégées ;
- la composition F2, afin de relire et comparer SessionId/AccountId avant Logout ou Rotation.

Cette évolution ne change pas la signature HTTP publique. Les opérations anonymes conservent un contexte nul. Les opérations non implémentées restent fail-closed.

## Compatibilité persistence et sécurité

- aucune migration requise ; `session_id` et `account_id` existent déjà ;
- aucune donnée historique à convertir ;
- aucune nouvelle clé ou preuve cryptographique ;
- les délais, rotation, concurrence et replay F1 restent inchangés ;
- toute discordance entre le contexte et la row relue est réduite fail-closed ;
- une session Rotated, Revoked ou Expired reste invalide même si son ancien contexte existe encore dans une requête concurrente.

## Compatibilité Controllers

Le Controller futur ne parse ni cookie ni SessionId et ne prend aucune décision. Il transporte mécaniquement un objet Application déjà validé. Le Runtime reste indépendant d'Illuminate et de Symfony.
