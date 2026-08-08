# Notifications — Source Inventory

## Sources owner-locales candidates

- préférences notificationnelles et consentements par finalité ;
- catalogue versionné des modèles ;
- politique de canaux ;
- journal d'intents d'émission et clés d'idempotence ;
- état de tentative, retry et terminalité ;
- registre de suppression et rétention.

## Sources publiques entrantes candidates

- `IdentityAccess` : disponibilité du compte et endpoint vérifié minimal ;
- Moderation : décision confirmée nécessitant information ;
- Reservations : changement confirmé et publiable ;
- `ContentSeo` : publication ou retrait éditorial confirmé ;
- Search : fait public de recherche sauvegardée ou résultat qualifié, si ouvert
  ultérieurement.

Sont interdits comme sources : Aggregate, base, projection interne, Runtime Health,
cache ou reconstruction libre depuis les Events d'un autre owner.
