# Build/CI Identity Alignment

`build/runtime.lock.json`, le workflow et le script packaging portent désormais uniquement :

- source base connue R4 : `984c0de2462cc6e34c77ac82bd9f695c72c94ebf`;
- tag futur symbolique : `appart-sn-release-candidate-rc2-r5`.

Les huit gardes `BuildCiSourceIdentityArchitectureTest` sont PASS (73 assertions). `bash -n` et le mode `APPART_ALIGNMENT_CHECK_ONLY=1` sont PASS. Aucun SHA/tree futur auto-référent n'est embarqué.
