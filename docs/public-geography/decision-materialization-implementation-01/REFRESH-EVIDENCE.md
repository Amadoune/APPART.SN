# Refresh evidence

`RefreshPublicGeographyTerminalV2` réutilise le hierarchy reader, l'assembler et le writer. `PublicGeographyMutationRefreshConsumer` parcourt les pages et dérive chaque causalité depuis sourceEventId, terminalPlaceId et `refresh-contract-v1`.

Rename recalcule les labels; Disable/Merge produisent Unavailable; Enable ne produit Available que lorsque toute la chaîne est valide.
