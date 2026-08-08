# Phase 5.4A — Contracts Matrix

| Catégorie | Contrat | Entrée | Sortie |
|---|---|---|---|
| Command | `SubmitLeadIngressV1` | intent, checksum, Listing opaque, références opaques, instants | portée par le Command Port |
| Command Port | `LeadIngressCommandPortV1` | `SubmitLeadIngressV1` | `LeadIngressSubmissionResultV1` |
| Query | `ReadOwnLeadIngressReceiptV1` | LeadIngressId, requester opaque, observedAt | portée par le Query Port |
| Query Port | `LeadIngressQueryPortV1` | `ReadOwnLeadIngressReceiptV1` | `LeadIngressReadResultV1` |
| Receipt | `LeadIngressReceiptV1` | identifiant, statut terminal, recordedAt | payload minimal de `Found` |

## Catalogue Command

La Foundation certifie une seule mutation publique :

- soumettre une intention de contact.

Les commandes de delivery, reprise et quarantaine restent hors périmètre
jusqu'aux Foundations correspondantes.

## Catalogue Query

La Foundation certifie une seule lecture :

- lire son propre accusé d'ingress.

La boîte de leads et le statut de delivery restent hors périmètre, car leurs
sources et règles d'autorisation ne sont pas encore ouvertes.

## Frontières owner certifiées

| Frontière | Décision | Règle de compatibilité |
|---|---|---|
| Listing Contactability | Listing contactable ou fermé | jamais recalculée |
| Listing Contact Principal | principal Account opaque | jamais assimilé au destinataire final |
| Professional Lead Recipient | Professional éligible ou fermé | jamais déduit du Status ou Mandate |
