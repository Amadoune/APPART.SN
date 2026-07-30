# Lead Lifecycle Outbox Compatibility Analysis

## Extension certifiée

Le catalogue Delivery générique ajoute les trois types 4.4E sous `ContactsLeads / LeadLifecycle / V1`. Le mapper PostgreSQL unique restaure `LeadLifecycleDeliveryPayload` par sa méthode stricte, sans mapper parallèle.

Le Consumer vérifie type, version, owner, aggregate, identité, version et index avant de reconstruire l'enveloppe 4.4F. Une divergence ne rejoint jamais le routeur. Le résultat du routeur est converti exclusivement par la politique 4.4G-R1.

## Worker générique

Le registre existant passe de 38 à 41 couples type/version. Les trois inscriptions Lead utilisent le même Consumer de transport. Aucun Worker spécifique n'est créé.

## Frontière

Aucun événement n'est produit. Le sprint ne relie pas workflow et Outbox, ne modifie pas les migrations 025/026 et n'introduit aucun HTTP ou accès aux catalogues d'éligibilité.
