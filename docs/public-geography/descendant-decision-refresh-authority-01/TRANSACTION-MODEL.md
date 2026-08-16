# Transaction model

Chaque rematerialization terminale utilise la transaction locale et le lock du writer Public Geography. Le fan-out n'est pas une transaction globale.

Outbox Geography et mutation owner sont atomiques dans leur transaction source; aucune transaction Geography + Public Geography + Projection.
