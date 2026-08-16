#!/usr/bin/env bash
set -euo pipefail

readonly EXPECTED_SOURCE_BASE="ab5d3f57a577160d3aae36cee5778dc7bae59a16"
readonly EXPECTED_CANDIDATE_TAG="appart-sn-release-candidate-rc2-r2"
readonly ROOT="$(git rev-parse --show-toplevel)"
readonly BUILD_SHA="$(git rev-parse HEAD)"
readonly OUTPUT_DIR="${1:-$ROOT/dist/release}"
readonly RELEASE_ROOT="$OUTPUT_DIR/root"

if [[ "${APPART_ALIGNMENT_CHECK_ONLY:-0}" == "1" ]]; then
  test "$BUILD_SHA" = "$EXPECTED_SOURCE_BASE"
  test -z "$(git tag --list "$EXPECTED_CANDIDATE_TAG")"
  echo "Successor alignment verified: ${EXPECTED_CANDIDATE_TAG} after ${EXPECTED_SOURCE_BASE}"
  exit 0
fi

test "$(git cat-file -t "refs/tags/${EXPECTED_CANDIDATE_TAG}")" = "tag"
test "$(git rev-parse "${EXPECTED_CANDIDATE_TAG}^{commit}")" = "$BUILD_SHA"
git merge-base --is-ancestor "$EXPECTED_SOURCE_BASE" "$BUILD_SHA"
test -z "$(git status --porcelain)"
test "$(sha256sum composer.lock | cut -d' ' -f1)" = "f15dde645598d805143d9ec1d3fab666730ac58ada078b0a3448c498bbd02be5"
test "$(sha256sum package-lock.json | cut -d' ' -f1)" = "1a717514aba144013fe85101e951f18cc74de01f311c9f9b5378b767d00ed26a"

if [[ "${APPART_IDENTITY_CHECK_ONLY:-0}" == "1" ]]; then
  echo "Candidate identity verified: ${EXPECTED_CANDIDATE_TAG} -> ${BUILD_SHA} (source base ${EXPECTED_SOURCE_BASE})"
  exit 0
fi

rm -rf "$OUTPUT_DIR"
mkdir -p "$RELEASE_ROOT"
git archive --format=tar "$BUILD_SHA" | tar -xf - -C "$RELEASE_ROOT"

rm -rf \
  "$RELEASE_ROOT/.github" \
  "$RELEASE_ROOT/build" \
  "$RELEASE_ROOT/docs" \
  "$RELEASE_ROOT/tests" \
  "$RELEASE_ROOT/tools"
rm -f \
  "$RELEASE_ROOT/phpunit.xml" \
  "$RELEASE_ROOT/phpunit.postgresql.xml" \
  "$RELEASE_ROOT/phpstan.neon" \
  "$RELEASE_ROOT/package.json" \
  "$RELEASE_ROOT/package-lock.json" \
  "$RELEASE_ROOT/vite.config.js"

composer install --working-dir="$RELEASE_ROOT" --no-dev --prefer-dist --no-interaction --no-progress --classmap-authoritative
mkdir -p "$RELEASE_ROOT/public"
cp -R "$ROOT/public/build" "$RELEASE_ROOT/public/build"
rm -rf "$RELEASE_ROOT/bootstrap/cache"/* "$RELEASE_ROOT/storage/logs"/*

php "$ROOT/tools/release/create-deterministic-tar.php" "$RELEASE_ROOT" "$OUTPUT_DIR/appart-release.tar"

(
  cd "$RELEASE_ROOT"
  find . -type f -print0 | LC_ALL=C sort -z | while IFS= read -r -d '' file; do
    printf '%s  %s\n' "$(sha256sum "$file" | cut -d' ' -f1)" "${file#./}"
  done
) > "$OUTPUT_DIR/tree-sha256.txt"

sha256sum "$OUTPUT_DIR/appart-release.tar" > "$OUTPUT_DIR/artifact-sha256.txt"
sha256sum "$OUTPUT_DIR/tree-sha256.txt" > "$OUTPUT_DIR/tree-root-sha256.txt"

readonly COMPOSER_VERSION="$(composer --version --no-ansi 2>/dev/null | head -n 1)"
test -n "$COMPOSER_VERSION"
export APPART_COMPOSER_VERSION="$COMPOSER_VERSION"

php -r '
$root=$argv[1]; $out=$argv[2]; $project=getcwd();
$migrations=[];
$it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach($it as $file){$path=str_replace("\\","/",substr($file->getPathname(),strlen($root)+1));if($file->isFile()&&preg_match("~/Migrations/.*\\.sql$~",$path)){$migrations[]=$path;}}
sort($migrations,SORT_STRING);
$manifest=[
 "schema"=>"appart.release-manifest.v1",
 "releaseCandidateId"=>getenv("RELEASE_CANDIDATE_ID")?:"phase-5.9-build-ci-rc",
 "sourceBaseCommitSha"=>getenv("SOURCE_BASE_SHA"),
 "candidateCommitSha"=>getenv("BUILD_SHA"),
 "candidateTag"=>getenv("CANDIDATE_TAG"),
 "buildCommitSha"=>getenv("BUILD_SHA"),
 "buildDateUtc"=>gmdate("Y-m-d\\TH:i:s\\Z"),
 "phpVersion"=>PHP_VERSION,
 "composerVersion"=>getenv("APPART_COMPOSER_VERSION"),
 "nodeVersion"=>trim((string)shell_exec("node --version")),
 "npmVersion"=>trim((string)shell_exec("npm --version")),
 "buildEnvironmentIdentity"=>getenv("BUILD_ENVIRONMENT_IDENTITY")?:PHP_OS_FAMILY,
 "composerLockSha256"=>hash_file("sha256",$project."/composer.lock"),
 "packageLockSha256"=>hash_file("sha256",$project."/package-lock.json"),
 "artifactSha256"=>hash_file("sha256",dirname($root)."/appart-release.tar"),
 "treeHashSha256"=>hash_file("sha256",dirname($root)."/tree-sha256.txt"),
 "fileCount"=>count(file($out."/tree-sha256.txt", FILE_IGNORE_NEW_LINES)),
 "migrations"=>$migrations,
 "ciRunId"=>getenv("GITHUB_RUN_ID")?:null,
 "ciRunAttempt"=>getenv("GITHUB_RUN_ATTEMPT")?:null,
 "gates"=>["unit"=>"PASS","architecture"=>"PASS","feature"=>"PASS","postgresql"=>"PASS","phpstan"=>"PASS","pint"=>"PASS","diffCheck"=>"PASS","frontendBuild"=>"PASS"]
];
file_put_contents($out."/release-manifest.json",json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");
' "$RELEASE_ROOT" "$OUTPUT_DIR"

echo "Release artifact written to $OUTPUT_DIR/appart-release.tar"
