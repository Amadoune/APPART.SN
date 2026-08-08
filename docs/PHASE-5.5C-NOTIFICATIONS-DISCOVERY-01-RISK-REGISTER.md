# Notifications — Risk Register

| Risque | Niveau | Maîtrise recommandée |
|---|---|---|
| Double envoi | Critique | Identité d'intent et idempotence owner-locales |
| Envoi après opt-out | Critique | Préférence observée avant chaque tentative |
| Fuite de PII | Critique | Minimisation, chiffrement et diagnostics fermés |
| Modèle incompatible avec les variables | Élevé | Modèles versionnés et schéma fermé futur |
| Retry infini | Élevé | Budget, backoff et terminalité owner-locales |
| Suppression prématurée | Élevé | Politique explicite de rétention |
| Notification bloquant le domaine source | Critique | Transaction et échec découplés |
| Dépendance directe aux Persistences sources | Critique | Frontières publiques minimales |
| Confusion préférence/endpoint Identity | Élevé | Ownership séparé et nominatif |
| Abus ou amplification | Critique | Quotas, suppression et audit futurs |

Les risques de conformité, rétention et consentement exigent un jalon d'autorité
dédié avant toute Persistence.
