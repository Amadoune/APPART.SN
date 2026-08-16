# Modèle de preuve UI

Captures minimales :

| Checkpoint | État visible attendu |
|---|---|
| Workspace | actor authentifié et assistant Authoring |
| Upload | média réel sélectionné puis associé |
| Preview | données du nouveau Property/Listing et média réel |
| Submitted | résultat Submit et état Submitted |
| Queue | candidature identifiée |
| Claim | affectation au reviewer |
| UnderReview | état de revue visible |
| Published | confirmation de publication |

Chaque capture porte une référence de manifest et évite secrets, valeurs de cookie et credential. La capture UI est corroborative ; elle ne remplace jamais les stores autoritatifs.
