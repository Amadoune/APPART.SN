# V1/V2 Compatibility

Le delta soutient la matérialisation Public Geography V2 tout en conservant la lecture V1, mais il ne constitue pas un doublon V1/V2 du même type.

`place.lifecycle.renamed@1` est un transport dédié de mutation Rename vers le materializer/refresh V2. Sa payload version reste 1 et son identité est distincte des événements Geography existants. Public Media V2, Search et ContentSeo n'ajoutent pas ce 55e enregistrement.
