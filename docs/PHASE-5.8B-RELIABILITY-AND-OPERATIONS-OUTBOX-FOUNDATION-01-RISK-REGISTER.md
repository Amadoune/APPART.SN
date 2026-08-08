# Phase 5.8B — Reliability & Operations — Outbox Risk Register

| Risque | Maîtrise | État |
|---|---|---|
| Duplication d'une Delivery | messageId déterministe et AlreadyApplied | Couvert |
| Même identité, contenu divergent | checksum canonique et DivergentMessage | Couvert |
| Double claim concurrent | FOR UPDATE SKIP LOCKED | Couvert |
| Retry infini | borne stricte de dix tentatives | Couvert |
| Altération du journal | séparation journal append-only / état technique | Couvert |
| Commit d'une transaction appelante | savepoints locaux et rollback externe préservé | Couvert |
| Désordre de publication | tri disponibleAt, createdAt, messageId | Couvert |
| Altération des migrations gelées | empreintes SHA-256 084–088 vérifiées | Couvert |
| Ouverture aval implicite | aucun Transport, Routing ou Consumer | Couvert |

