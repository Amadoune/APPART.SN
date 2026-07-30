# Public Projection Retry and Quarantine

## Retry, classification et backoff

`PublicProjectionDeterministicRetryPolicy` implémente le port Outbox existant. Elle reçoit un nombre maximal d'essais et une stratégie de backoff. Les stratégies disponibles sont fixe et exponentielle plafonnée. Le même attempt produit toujours le même délai ; aucun jitter n'est retenu.

Les dispositions explicites sont RetryAllowed, RetryDenied, AttemptsExhausted et BlockedWithoutAttemptBudget. Transient utilise le budget. Permanent est refusé. SourceNotReady et SequenceGap restent des blocages distincts sans boucle temporelle et sans épuisement artificiel.

## Quarantaine

Chaque entrée conserve l'une des six raisons contractuelles : PermanentFailure, UnsupportedEventType, UnsupportedPayloadVersion, DivergentPayload, AttemptsExhausted ou DurableBlockage. Les incompatibilités de type/version et les divergences ne peuvent pas être rejouées sous le même contrat. Les autres raisons exigent une autorisation de reprise traçable et une correction extérieure ; aucune sortie automatique n'existe.

## Replay

Les scopes fermés sont Message, Aggregate, Module, Range et HighWatermark. Une requête immutable porte consumerId, scope, cible et référence d'autorisation. Le replay conserve les identités existantes, ne crée aucune causalité et ne constitue pas un rebuild.

## Observabilité

`PublicProjectionDeliveryTechnicalMetrics` décrit retry count/lag, taille et âge maximal de quarantaine, nombre et durée de replay, blocages readiness et sequence gap. Toutes les valeurs sont positives ou nulles. Aucun collecteur, monitoring, SQL ou stockage n'est créé dans ce sprint.

## Invariants et limites

Une décision est calculée une fois par échec ; aucune boucle de retry n'existe. Aucun échec n'est transformé silencieusement en succès. Les Fakes de tests démontrent planification, refus, épuisement, replay et sortie autorisée. Aucun Worker, Dispatcher, scheduler, Job, Queue, Laravel, HTTP ou infrastructure réelle n'est ajouté.

Projection Updater Integration reste bloquée jusqu'à la création de son Consumer réel, de la résolution autorisée des sources et des marqueurs idempotents durables. Ces responsabilités n'appartiennent pas à Retry and Quarantine.
