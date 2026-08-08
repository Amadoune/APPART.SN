# SecurityCompliance HTTP Foundation

La façade HTTP expose exclusivement cinq Readers publics V1 matérialisés : Secret Inventory, Security Audit, Incident, Privacy Policy et Compliance Control.

Elle comprend cinq Controllers, cinq Form Requests strictes, `SecurityComplianceResponseFactory`, `SecurityComplianceHttpRuntimeV1` et `SecurityComplianceHttpServiceProvider`. Les Controllers dépendent uniquement des Readers V1 correspondants.

Les façades Cryptography Policy, Data Retention et Data Export restent absentes faute de source owner-scoped. Aucun accès direct à l'Owner Source, au Runtime, à la Persistence ou à l'Infrastructure n'est autorisé.
