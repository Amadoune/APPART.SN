# Root Cause

Chaîne démontrée :

`AuthoringDraftResumeHttpTest`
→ `GET /authoring/workspace/{listingId}`
→ `AuthoringWorkspaceController`
→ vue `authoring-workspace`
→ directive `@vite`
→ lecture de `public/build/manifest.json`
→ `ViteManifestNotFoundException`.

Le résultat Authoring Resume est préparé correctement avant le rendu. La première divergence n'est ni IAM, ni Resume, ni DB, ni fixture, ni cache : elle est exclusivement l'ordre du pipeline Release.
