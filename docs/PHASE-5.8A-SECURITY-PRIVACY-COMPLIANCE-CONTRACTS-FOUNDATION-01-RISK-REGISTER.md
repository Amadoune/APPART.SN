# Risk Register — SecurityCompliance Contracts

| Risque | Impact | Maîtrise | Résiduel |
|---|---|---|---|
| secret ou clé exposé dans un Result | critique | deux propriétés strictes et tests structurels | faible |
| contenu d'audit ou politique détaillée exposé | critique | statut public minimal uniquement | faible |
| confusion entre catalogues | élevé | enum et Result dédiés par Reader | faible |
| fallback silencieux | élevé | catalogues fermés sans valeur par défaut | faible |
| SubjectKey utilisé comme PII | élevé | clé opaque ; contenu métier interdit par contrat | moyen |
| timestamp non canonique | moyen | Value Object UTC microseconde | faible |
| transfert d'autorité métier | critique | états descriptifs sans commande ni décision | faible |
| dépendance à une capacité gelée | critique | enclave Application autonome | faible |

Aucun risque résiduel n'autorise une implémentation ou l'ouverture d'une Foundation ultérieure.
