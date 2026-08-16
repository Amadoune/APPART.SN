# Store audit

Les deux tables productives existent.

- `public_geography.decisions`: PK `place_id`, version positive, causation, checksum de révision, payload JSONB, checksum de payload, timestamp; égalité des checksums contrainte.
- `public_media.decisions`: même modèle, PK `media_collection_id`.

RC2 : 0 ligne Geography, 0 ligne Media. Il s'agit de lignes absentes, non de tables absentes ou de données corrompues. Les reader bindings existent.
