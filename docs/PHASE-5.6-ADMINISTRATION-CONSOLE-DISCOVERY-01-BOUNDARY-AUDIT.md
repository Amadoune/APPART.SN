# Administration Console — Boundary Audit

Owner recommandé : `AdministrationConsole`.

La capacité possède l'expérience opérateur, la navigation, la composition des
vues administratives, les filtres et la coordination d'intentions. Elle ne
possède aucune décision métier des domaines administrés.

IAM conserve l'identité, l'authentification et l'autorisation. Moderation conserve
les cas, décisions et files de modération. Notifications conserve préférences,
modèles et canaux. ContentSeo conserve les décisions éditoriales et SEO.
`AdministrationAudit` conserve l'audit opérationnel durable.

La console ne lit aucun Aggregate, aucune table ni aucun Outbox directement.
