# Lead Lifecycle Outbox Owner Isolation Matrix

| Opération | Owner ciblé | Autres owners |
|---|---|---|
| écriture `ContactsLeads` | `contacts_leads` uniquement | aucune ligne |
| lecture `ContactsLeads` | filtre `source_module = ContactsLeads` | copies étrangères ignorées |
| rollback 026 | quatre tables ContactsLeads supprimées | inchangés |
| migration 026 | quatre tables et deux index créés | inchangés |

Une copie portant la même identité dans un autre schéma ne peut pas être restaurée comme message ContactsLeads par le Reader.
