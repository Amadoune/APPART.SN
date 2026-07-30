# Lead Lifecycle Persistence Analysis

## Décision

Le workflow 4.4A demeure l'unique propriétaire des décisions. La persistance reçoit exclusivement un état initial ou une `LeadLifecycleTransition` déjà construite et ne possède aucune matrice métier PHP.

Le journal est append-only, ordonné par une version strictement positive et protégé par un verrou transactionnel PostgreSQL dérivé du `lead_id`. Une écriture concurrente identique converge vers `Applied` puis `AlreadyApplied`.

## Propriété

La table `contacts_leads.lead_lifecycle_transitions` appartient à ContactsLeads. Elle est indépendante de l'Aggregate `Lead` existant et n'accède ni aux sources d'éligibilité, ni au Runtime, ni à l'Outbox.

## Intégrité

PostgreSQL protège la forme des lignes, les états fermés et les quatre transitions certifiées. Le checksum SHA-256 protège la reconstruction exacte. Aucun timestamp ne définit l'ordre métier.
