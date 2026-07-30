# Media Item Lifecycle Event Routing Sequence

1. Le routeur reçoit une `MediaItemLifecycleTransportEnvelope`.
2. Il reconstruit uniquement la représentation technique attendue depuis son payload opaque.
3. Il vérifie `messageId`, type, version, source, `eventId`, checksum et sérialisation canonique.
4. Il délègue une seule fois à `MediaItemLifecycleInboxStore`.
5. Le repository acquiert un verrou advisory transactionnel dérivé de `messageId`.
6. Il insère l'enveloppe ou compare byte-for-byte le rejeu avec la ligne existante.
7. Le résultat fermé est restitué sans interprétation métier.

Une transaction locale est ouverte seulement en l'absence de transaction appelante. Une transaction externe est réutilisée sans commit interne.
