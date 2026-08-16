# Final implementation boundary 03

`PUBLIC GEOGRAPHY DECISION MATERIALIZATION IMPLEMENTATION 01` est autorisée exclusivement pour :

- source/hierarchy reader V2;
- canonical representation assembler;
- support writer/reader/mapper V2 avec compatibilité V1;
- materializer initial et catch-up commun;
- terminal refresh materializer;
- affected-terminal JSONB reader paginé;
- admission PlaceRenamed dans le transport/outbox Geography existant;
- mutation refresh consumer;
- providers, bindings et tests ciblés.

Interdits : refaire ContentSeo/Blade/consumer alignment, Public Media, ActiveGeneration bootstrap, Projection RC2, Search, migration ou transaction distribuée. Une régression démontrée peut autoriser uniquement sa correction strictement causale.
