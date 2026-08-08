# Administration Console — Dependency Matrix

| Dépendance | Sens | Autorisation |
|---|---|---|
| IdentityAccess | IAM → Console | Principal et autorisations uniquement |
| ModerationReports | Moderation → Console | Lectures et commandes versionnées |
| Notifications | Notifications → Console | Lectures certifiées uniquement |
| ContentSeo | ContentSeo → Console | Lectures certifiées uniquement |
| AdministrationAudit | Console → Audit | Append d'actions opérateur ; lecture audit versionnée |
| Persistence externe | Infrastructure → Console | Interdite |

Les futures commandes restent adressées au domaine propriétaire. La console ne
devient jamais une autorité de décision transversale.
