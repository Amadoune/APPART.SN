# Packaging Prerequisites

Historical packaging requires:

- annotated candidate tag resolving to checkout HEAD;
- source-base ancestry check;
- aligned runtime lock/workflow/build script;
- clean checkout;
- exact Composer/npm lock checksums;
- PHP 8.5.8, Composer 2.9.4, Node 24.17.0, npm 11.13.0;
- locked dependency restore;
- Unit, Feature, Architecture, Foundation, PostgreSQL, PHPStan and Pint gates;
- frontend production build;
- deterministic archive, tree manifest and checksums.

RC2 satisfies immutable identity only. Identity alignment must precede packaging.
