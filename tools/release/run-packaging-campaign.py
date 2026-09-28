"""Sequential independent Packaging A/B using the existing builder and manifest contract."""
import hashlib
import importlib.util
import json
import os
import pathlib
import re
import shutil
import subprocess
import tarfile
import tempfile


class CampaignFailure(RuntimeError):
    pass


def require(condition, guard):
    if not condition:
        raise CampaignFailure(guard)


def digest(path):
    with path.open('rb') as stream:
        return hashlib.file_digest(stream, 'sha256').hexdigest()


def load(path):
    return json.loads(path.read_text(encoding='utf-8'))


def command(args, cwd, env):
    # Preserve the runner's valid noninteractive stdin pipe; no Windows launch machinery.
    subprocess.run(args, cwd=cwd, env=env, check=True)


def certification(room, identity):
    out = room / 'dist/release'
    manifest = load(out / 'release-manifest.json')
    require(manifest['schema'] == 'appart.release-manifest.v1', 'MANIFEST_SCHEMA')
    for field, key in [('candidateCommitSha', 'BUILD_SHA'), ('buildCommitSha', 'BUILD_SHA'),
                       ('sourceBaseCommitSha', 'SOURCE_BASE_SHA'), ('candidateTag', 'CANDIDATE_TAG'),
                       ('buildDateUtc', 'PACKAGING_CAMPAIGN_BUILDDATEUTC')]:
        require(manifest[field] == identity[key], 'MANIFEST_IDENTITY_' + field)
    require(manifest['phpVersion'] == '8.5.8' and '2.9.4' in manifest['composerVersion']
            and manifest['nodeVersion'] == 'v24.17.0' and manifest['npmVersion'] == '11.13.0', 'RUNTIME_IDENTITY')
    for field, name in [('composerLockSha256', 'composer.lock'), ('packageLockSha256', 'package-lock.json')]:
        require(manifest[field] == digest(room / name), 'LOCK_IDENTITY')
    inventory = {}
    for line in (out / 'tree-sha256.txt').read_text(encoding='utf-8').splitlines():
        match = re.fullmatch(r'([0-9a-f]{64})  (.+)', line)
        require(match is not None, 'INVENTORY_FORMAT')
        value, name = match.groups()
        path = pathlib.PurePosixPath(name)
        require(not path.is_absolute() and '..' not in path.parts and name not in inventory, 'INVENTORY_PATH')
        inventory[name] = value
    actual = {}
    total_bytes = 0
    for path in (out / 'root').rglob('*'):
        require(not path.is_symlink(), 'ROOT_SYMLINK')
        if path.is_file():
            actual[path.relative_to(out / 'root').as_posix()] = digest(path)
            total_bytes += path.stat().st_size
    require(actual == inventory and manifest['fileCount'] == len(actual), 'INVENTORY_EXACT')
    archive = {}
    with tarfile.open(out / 'appart-release.tar', 'r:') as tar:
        for member in tar:
            name = member.name.removeprefix('./')
            parts = pathlib.PurePosixPath(name).parts
            require(not member.name.startswith('/') and '..' not in parts
                    and (member.isfile() or member.isdir()) and not member.issym() and not member.islnk(), 'ARCHIVE_PATH')
            if not member.isfile():
                continue
            require(name not in archive, 'ARCHIVE_DUPLICATE')
            payload = tar.extractfile(member).read()
            archive[name] = hashlib.sha256(payload).hexdigest()
            require(not any(part.lower() in ['.env', 'auth.json', 'composerhome', 'stdout.log',
                        'stderr.log', 'metadata.json', 'execution.claim'] for part in parts), 'ARTIFACT_LOCAL_STATE')
            require(re.search(rb'-----BEGIN (?:RSA |EC |OPENSSH |DSA |ENCRYPTED )?PRIVATE KEY-----|gh[pousr]_[A-Za-z0-9]{30,}|github_pat_[A-Za-z0-9_]{30,}', payload) is None, 'ARTIFACT_SECRET_PATTERN')
    require(archive == actual, 'ARCHIVE_EXACT')
    require((out / 'root/vendor/autoload.php').is_file()
            and not any((out / 'root/vendor/composer').glob('tmp*')), 'COMPLETE_VENDOR')
    require(not any(any(token in name.lower() for token in ['packaging-harness', 'appart-opaque-holding', 'qualification-state']) for name in actual), 'NO_DIAGNOSTICS')
    for field, name in [('artifactSha256', 'appart-release.tar'), ('treeHashSha256', 'tree-sha256.txt')]:
        require(manifest[field] == digest(out / name), 'MANIFEST_DIGEST')
    for sidecar, name in [('artifact-sha256.txt', 'appart-release.tar'), ('tree-root-sha256.txt', 'tree-sha256.txt')]:
        require((out / sidecar).read_text().split()[0] == digest(out / name), 'CHECKSUM_SIDECAR')
    return dict(status='CERTIFIED', file_count=len(actual), byte_count=total_bytes,
                manifest_sha256=digest(out / 'release-manifest.json'), artifact_sha256=digest(out / 'appart-release.tar'),
                inventory_sha256=digest(out / 'tree-sha256.txt'), identity={k:identity[k] for k in [
                    'BUILD_SHA', 'SOURCE_BASE_SHA', 'CANDIDATE_TAG', 'PACKAGING_CAMPAIGN_ID',
                    'PACKAGING_CAMPAIGN_BUILDDATEUTC', 'PACKAGING_CAMPAIGN_SHA256']})


def compare(a, b):
    require(a['status'] == b['status'] == 'CERTIFIED', 'BOTH_CERTIFIED')
    require(a['identity'] == b['identity'], 'CAMPAIGN_IDENTITY_EQUAL')
    for key in ['file_count', 'byte_count', 'manifest_sha256', 'artifact_sha256', 'inventory_sha256']:
        require(a[key] == b[key], 'REPRODUCIBILITY_' + key)


def execute_campaign(rooms, state_dirs, identity, run_build, certify, verify_campaign):
    paths = [path.resolve() for path in [*rooms, *state_dirs]]
    require(len(paths) == len(set(paths)) and not any(a in b.parents for a in paths for b in paths if a != b), 'INDEPENDENT_PATHS')
    require(all(identity.get(k) for k in ['BUILD_SHA', 'SOURCE_BASE_SHA', 'CANDIDATE_TAG',
                'PACKAGING_CAMPAIGN_ID', 'PACKAGING_CAMPAIGN_BUILDDATEUTC', 'PACKAGING_CAMPAIGN_SHA256']), 'CAMPAIGN_INPUTS')
    require(re.fullmatch(r'\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ', identity['PACKAGING_CAMPAIGN_BUILDDATEUTC']) is not None, 'CAMPAIGN_TIMESTAMP')
    results = []
    for label, room, state in zip(['A', 'B'], rooms, state_dirs):
        verify_campaign()
        run_build(label, room, state, dict(identity))
        verify_campaign()
        result = certify(room, identity)
        require(result['status'] == 'CERTIFIED', label + '_CERTIFICATION')
        # In particular, B cannot start until the append below follows A certification.
        results.append(result)
    compare(*results)
    return dict(status='PASS', A=results[0], B=results[1])


def main():
    source = pathlib.Path.cwd().resolve()
    env = dict(os.environ)
    require(all(env.get(k) for k in ['BUILD_SHA', 'SOURCE_BASE_SHA', 'CANDIDATE_TAG', 'RUNNER_TEMP', 'GITHUB_RUN_ID', 'GITHUB_RUN_ATTEMPT']), 'WORKFLOW_INPUTS')
    require(subprocess.check_output(['git', 'rev-parse', 'HEAD'], cwd=source, text=True).strip() == env['BUILD_SHA'], 'SOURCE_HEAD')
    require(not subprocess.check_output(['git', 'status', '--porcelain'], cwd=source, text=True).strip(), 'SOURCE_CLEAN')
    spec = importlib.util.spec_from_file_location('campaign', source / 'tools/release/campaign-controller.py')
    controller = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(controller)
    root = pathlib.Path(tempfile.mkdtemp(prefix='appart-packaging-', dir=env['RUNNER_TEMP']))
    record = root / 'campaign.json'
    campaign_id = 'github-' + env['GITHUB_RUN_ID'] + '-' + env['GITHUB_RUN_ATTEMPT']
    record_digest = controller.create(record, campaign_id, env['BUILD_SHA'], env['SOURCE_BASE_SHA'], env['CANDIDATE_TAG'])
    identity = controller.context(record, record_digest)
    rooms = [root / 'A', root / 'B']
    states = [root / 'state-A', root / 'state-B']
    def verify():
        require(controller.context(record, record_digest) == identity, 'IMMUTABLE_CAMPAIGN')
    def build(label, room, state, inputs):
        require(not room.exists() and not state.exists(), 'FRESH_BUILD_STATE')
        state.mkdir()
        child_env = {**env, **inputs, 'COMPOSER_HOME':str(state / 'composer'),
                     'COMPOSER_CACHE_DIR':str(state / 'composer-cache'), 'npm_config_cache':str(state / 'npm-cache'),
                     'TMPDIR':str(state / 'tmp'), 'PYTHONDONTWRITEBYTECODE':'1'}
        (state / 'tmp').mkdir()
        command(['git', 'clone', '--local', '--no-hardlinks', '--no-checkout', str(source), str(room)], source, child_env)
        command(['git', 'checkout', '--detach', inputs['BUILD_SHA']], room, child_env)
        require(not (room / '.git/objects/info/alternates').exists(), 'INDEPENDENT_OBJECT_STORE')
        command(['npm', 'ci', '--ignore-scripts'], room, child_env)
        command(['npm', 'run', 'build'], room, child_env)
        command(['bash', 'tools/release/build-release.sh'], room, child_env)
        for args in [['diff', '--exit-code'], ['diff', '--cached', '--exit-code']]:
            command(['git', *args], room, child_env)
    result = execute_campaign(rooms, states, identity, build, certification, verify)
    destination = source / 'dist/release'
    require(not destination.exists(), 'FRESH_EVIDENCE_DESTINATION')
    destination.mkdir(parents=True)
    for label, room in zip(['A', 'B'], rooms):
        target = destination / label
        target.mkdir()
        for name in ['appart-release.tar', 'release-manifest.json', 'tree-sha256.txt', 'artifact-sha256.txt', 'tree-root-sha256.txt']:
            shutil.copyfile(room / 'dist/release' / name, target / name)
    shutil.copyfile(record, destination / 'campaign.json')
    (destination / 'campaign-result.json').write_text(json.dumps(result, indent=2)+'\n', encoding='utf-8')


if __name__ == '__main__':
    main()
