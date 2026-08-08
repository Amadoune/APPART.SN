# Notifications — Certification Note

Le Blueprint recommande `Notifications` comme owner unique. Identity conserve
l'autorité des comptes et endpoints ; Moderation, Reservations, ContentSeo et
Search conservent leurs faits métier. Notifications possède exclusivement les
préférences notificationnelles, modèles, canaux, intents, retries et suppressions.

La notification est découplée : son échec est observable et réessayable, mais ne
remet jamais en cause le fait confirmé du domaine source.

Aucun code, contrat, test, Runtime, HTTP, Event, Delivery, Outbox, Persistence,
migration ou SQL n'est créé. Le Blueprint est proposé pour certification.
