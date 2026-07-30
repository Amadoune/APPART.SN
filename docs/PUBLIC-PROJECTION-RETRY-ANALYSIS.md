# Public Projection Retry — analyse

## État contractuel audité

ADR-1009 et le contrat Outbox définissent déjà quatre classifications, un délai conceptuel, une décision avec autorisation, six raisons de quarantaine et un replay borné par curseur. Le Worker interprète exhaustivement les résultats Consumer et consulte exclusivement `PublicProjectionOutboxRetryPolicy`.

Restent implicites avant ce sprint : l'algorithme de backoff, le plafond d'essais, la différence entre refus et épuisement, les scopes message/Aggregate/module/high-watermark, l'autorisation de reprise, les règles de sortie de quarantaine et la forme des mesures techniques.

## Décisions

- backoff fixe ou exponentiel plafonné, sans jitter retenu afin de préserver le déterminisme ;
- Transient autorise un retry tant que `attempts < maximumAttempts` ;
- Permanent refuse tout retry ;
- SourceNotReady et SequenceGap restent bloqués sans consommer un budget de retry ;
- l'atteinte du plafond produit AttemptsExhausted ;
- tout replay porte un scope fermé et une référence d'autorisation ;
- UnsupportedEventType, UnsupportedPayloadVersion et DivergentPayload ne sortent jamais automatiquement de quarantaine ;
- PermanentFailure, AttemptsExhausted et DurableBlockage peuvent sortir uniquement après autorisation explicite et correction externe démontrée ;
- aucune policy ne persiste, ne boucle, ne lit PostgreSQL ou ne dépend d'un runtime.
