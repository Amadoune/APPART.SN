# F2 — Certification Note

## Verdict

**GO PROPOSÉ — F2 ADDRESS IDENTITY FOUNDATION IMPLEMENTATION 01.**

## Motif

`AddressIdentityIssuerV1` est exécutable et produit l’UUIDv5 certifié à partir des seuls `PropertyId` et `AddressIntentId`. La même intention reproduit exactement le même `AddressId` ; une nouvelle intention ou un autre Property produit une identité distincte.

L’autorité est pure : aucune persistence, migration, clock, source aléatoire, ledger, donnée Address, Geography, Authoring, Projection ou HTTP. Le Domain `AddressId`, `Address` et `ChangeAddress` reste inchangé.

Le statut `Collision` n’est pas inventé dans F2 : seule une future intégration avec un Aggregate/Registry réellement matérialisé pourra démontrer ce cas conformément au Blueprint.

## Gouvernance

F2 peut être fermée GO. F3 Business Year, F4 Authoring Completeness, F5 Source Completeness Recertification et RC2 Iteration 11 restent non ouvertes. Aucun staging, commit ou tag.
