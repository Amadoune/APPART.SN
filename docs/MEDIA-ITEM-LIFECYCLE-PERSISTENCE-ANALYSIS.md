# Media Item Lifecycle Persistence Analysis

4.6B matérialise exclusivement les décisions du workflow 4.6A dans un journal append-only. Le repository ne décide jamais d'une transition et ne connaît ni `MediaCollection`, ni le média principal, ni son remplacement, ni l'ordre, ni le checksum du contenu.

L'identité applicative `MediaItemLifecycleId` reste opaque. La version constitue l'ordre métier ; aucun timestamp ne participe au journal.
