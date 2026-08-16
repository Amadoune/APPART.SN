# Self-Identity Consistency

Historical model: **C — identity controls were aligned before the candidate was materialized**.

R5 proves the sequence:

1. edit runtime lock, workflow and packaging identity together;
2. commit them in candidate commit `9801d9e`;
3. create annotated R5 tag resolving that commit;
4. execute build guards from that immutable checkout.

RC2 materialization occurred before equivalent RC2 alignment. The existing candidate cannot be retrofitted while retaining the same commit/tree/tag.
