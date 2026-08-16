# Final implementation boundary

Le périmètre source serait fermé : DTO/mapper/readers V1/V2, hierarchy reader, assembler, initial/terminal materializers, JSONB lookup, transports, consumer, catch-up, bindings et tests.

Mais l'Implementation mandate interdit ContentSeo changes. Or le consumer actuel exige `ContentSeo\BreadcrumbItem(label, CanonicalUrl)`. Aucun changement Public Geography seul ne peut satisfaire V2 sans URL. Implementation 01 reste interdite.
