# Lead Lifecycle Event Routing Recovery Strategy

Les messages sont repris exclusivement dans l'ordre `(status, message_id)`. Le statut initial `pending` indique un transfert durable non encore traité par une future capacité.

`Deferred` signale une destination indisponible et n'acquitte rien. `RetryableFailure` conserve la possibilité d'un rejeu. `Rejected` exige une disposition durable future ou une quarantaine ; il n'est jamais silencieusement consommé.

Le sprint ne met en œuvre aucune boucle de reprise, quarantaine, consommation ou changement de statut. Ces responsabilités appartiennent aux étapes ultérieures.
