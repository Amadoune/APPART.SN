# Catch-up Model

Le catch-up doit accepter uniquement un ListingId, appeler le même matérialiseur productif et employer les mêmes readers, policy identity, canonical authority, decision time, version rules et writer.

Il est interdit d’injecter un snapshot, d’utiliser SQL direct, une fixture ou une branche RC2. Tant que le canonical path n’est pas autorisé, le catch-up retourne `CanonicalUnresolved` et n’écrit rien.
