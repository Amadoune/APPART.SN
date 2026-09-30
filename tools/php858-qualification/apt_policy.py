"""Gate59 source normalization. Pure parsers; no package-manager mutation."""
import itertools
from diagnostics import option_context, source_context
import re
import shlex
import urllib.parse

ARCHIVE_KEY = "F6ECB3762474EDA9D21B7022871920D1991BC93C"
SUITES = {"noble", "noble-updates", "noble-security"}
COMPONENTS = {"main", "restricted", "universe", "multiverse"}
HOSTS = {"archive.ubuntu.com", "security.ubuntu.com", "azure.archive.ubuntu.com"}
FIELDS = {"types", "uris", "suites", "components", "architectures", "signed-by", "enabled", "check-date", "check-valid-until", "trusted", "allow-insecure", "allow-weak", "allow-downgrade-to-insecure"}
OPTIONS = {"arch": "architectures", "signed-by": "signed-by", "trusted": "trusted", "allow-insecure": "allow-insecure", "allow-weak": "allow-weak", "allow-downgrade-to-insecure": "allow-downgrade-to-insecure", "check-date": "check-date", "check-valid-until": "check-valid-until"}


class PolicyFailure(ValueError):
    """Only fixed, safe rejection classes are emitted; never echo source text."""


def require(condition, rule):
    if not condition:
        raise PolicyFailure(rule)


def uri(value):
    require(not any(c in value for c in "%\\\r\n\x00"), "ambiguous source URI")
    parsed = urllib.parse.urlsplit(value)
    require(parsed.scheme.lower() in {"http", "https"}, "unapproved URI scheme")
    require(parsed.username is None and parsed.password is None, "credential-bearing source URI")
    require(not parsed.query and not parsed.fragment, "source URI query or fragment")
    require(parsed.hostname in HOSTS, "active non-Ubuntu or unapproved source")
    try:
        port = parsed.port
    except ValueError:
        raise PolicyFailure("malformed source port") from None
    require(port in {None, 80 if parsed.scheme.lower() == "http" else 443}, "nonstandard source port")
    require(parsed.path in {"/ubuntu", "/ubuntu/"}, "unapproved source path")
    return parsed.scheme.lower() + "://" + parsed.hostname.lower() + "/ubuntu/"


@source_context('DEB822')
def deb822(text):
    records = []
    stanza = {}
    previous = None
    for line in text.splitlines() + [""]:
        if not line.strip():
            if stanza:
                records.append(stanza)
            stanza, previous = {}, None
            continue
        if line.lstrip().startswith("#"):
            continue
        if line[0].isspace():
            require(previous is not None, "orphan source continuation")
            stanza[previous] += " " + line.strip()
            continue
        require(":" in line, "invalid deb822 source field")
        key, value = line.split(":", 1)
        key = key.lower()
        with option_context(key, value):
            require(key in FIELDS and key not in stanza, "unknown or duplicate source field")
            require("-----BEGIN" not in value, "embedded key unsupported")
            stanza[key] = value.strip()
        previous = key
    return records


@source_context('ONE_LINE')
def lists(text):
    records = []
    for line in text.splitlines():
        if not line.strip() or line.lstrip().startswith("#"):
            continue
        # A comment inside options is not a supported option value.
        require(not re.search(r"\[[^\]]*#", line), "ambiguous list comment")
        line = line.split("#", 1)[0].strip()
        match = re.fullmatch(r"(deb(?:-src)?)\s+(?:\[([^\]]*)\]\s+)?(\S+)\s+(\S+)\s+(.+)", line)
        require(match is not None, "invalid one-line source")
        kind, options, endpoint, suite, components = match.groups()
        record = dict(types=kind, uris=endpoint, suites=suite, components=components)
        if options:
            require(not any(c in options for c in "\\\"\'"), "unsupported quoted or escaped source option")
            for token in options.split():
                with option_context(token.split('=', 1)[0], token.split('=', 1)[1] if '=' in token else None, 'VALUE_PRESENT' if '=' in token else 'VALUELESS'):
                    require("=" in token, "malformed source option")
                    key, value = token.split("=", 1)
                    require(key in OPTIONS, "unknown source option")
                    key = OPTIONS[key]
                    require(key not in record, "duplicate source option")
                    record[key] = value.replace(",", " ")
        records.append(record)
    return records


@source_context('OTHER_EXACT')
def normalize(records, mirror_reader):
    result = []
    for record in records:
        enabled = trust_boolean(record.get("enabled", "yes"))
        if not enabled:
            result.append({"enabled": False})
            continue
        require(all(record.get(k) for k in ("types", "uris", "suites", "components")), "incomplete active source")
        for key in ("trusted", "allow-insecure", "allow-weak", "allow-downgrade-to-insecure"):
            require(trust_boolean(record.get(key, "no")) is False, "APT_UNSAFE_SOURCE_TRUST_OPTION")
        for key in ("check-date", "check-valid-until"):
            require(trust_boolean(record.get(key, "yes")) is True, "APT_UNSAFE_SOURCE_TRUST_OPTION")
        architectures = record.get("architectures", "amd64").split()
        require("amd64" in architectures and all(re.fullmatch(r"[a-z0-9]+(?:-[a-z0-9]+)*", a) for a in architectures), "unqualified source architecture")
        components = record["components"].split()
        require(set(components) <= COMPONENTS, "unqualified source component")
        signers = record.get("signed-by", "").split()
        for signer in signers:
            require(signer.startswith("/") or bool(re.fullmatch(r"[0-9A-Fa-f]{40}!{0,1}", signer)), "invalid Signed-By binding")
        for kind, endpoint, suite in itertools.product(record["types"].split(), record["uris"].split(), record["suites"].split()):
            require(kind in {"deb", "deb-src"}, "unknown source type")
            require(suite in SUITES, "unqualified source suite")
            endpoints = mirror_reader() if endpoint == "mirror+file:/etc/apt/apt-mirrors.txt" else [endpoint]
            for expanded in endpoints:
                result.append(dict(enabled=True, kind=kind, uri=uri(expanded), suite=suite, components=sorted(set(components)), architectures=sorted(set(architectures)), signed_by=signers))
    identities = {}
    for row in result:
        if not row["enabled"]:
            continue
        key = (row["kind"], row["uri"], row["suite"], tuple(row["components"]), tuple(row["architectures"]))
        require(key not in identities or identities[key] == row["signed_by"], "conflicting duplicate source trust")
        identities[key] = row["signed_by"]
    require(any(r.get("kind") == "deb" and r.get("suite") == "noble" and "main" in r.get("components", []) for r in result), "missing noble main binary source")
    return result


# Gate63: finite positive recognition. Unknown keys, namespaces and values fail.
# APT 2.7.14 configuration names are case-insensitive; punctuation is significant.
# This catalogue does not claim to admit every default/optional runner setting.
CONFIG_PATHS = {
    "dir": "/",
    "dir::etc": "etc/apt",
    "dir::etc::sourcelist": "sources.list",
    "dir::etc::sourceparts": "sources.list.d",
    "dir::etc::main": "apt.conf",
    "dir::etc::parts": "apt.conf.d",
    "dir::etc::trusted": "trusted.gpg",
    "dir::etc::trustedparts": "trusted.gpg.d",
}
CONFIG_FALSE = {
    "apt::get::allowunauthenticated",
    "acquire::allowinsecurerepositories",
    "acquire::allowweakrepositories",
    "acquire::allowdowngradetoinsecurerepositories",
}
CONFIG_TRUE = {
    "acquire::check-date",
    "acquire::check-valid-until",
    "acquire::https::verify-peer",
    "acquire::https::verify-host",
}
# apt-transport-https also supports host-specific verification controls.
# Only the already governed endpoints may appear in that scope.
for _host in HOSTS:
    CONFIG_TRUE.update({
        "acquire::https::verify-peer::" + _host,
        "acquire::https::verify-host::" + _host,
    })
CONFIG_FIXED = {"apt::architecture": "amd64", "apt::architectures::": "amd64"}
CONFIG_NON_TRUST = {"acquire::languages", "acquire::languages::"}


def trust_boolean(value):
    """No APT default fallback for unknown, empty or malformed boolean values."""
    require(isinstance(value, str), "APT_BOOLEAN_MALFORMED")
    # Do not trim quoted configuration contents: lexical whitespace is parsed
    # separately. APT's word aliases do not treat padded words as equivalent.
    folded = value.lower()
    if folded in {"true", "yes", "1", "on", "with", "enable"}:
        return True
    if folded in {"false", "no", "0", "off", "without", "disable"}:
        return False
    raise PolicyFailure("APT_BOOLEAN_MALFORMED")


@source_context('APT_CONFIG')
def _explicit_configuration_policy(text):
    """Validate effective apt-config dump output, not arbitrary apt.conf syntax.

    Admission requires an exact catalogue rule. Known empty namespace nodes
    are admitted only as containers; their children are always checked.
    Repeated Languages list elements are documented ordered metadata. Other
    duplicate/case-colliding keys fail rather than selecting a winning value.
    """
    require(isinstance(text, str) and "\x00" not in text, "APT_CONFIG_PARSE_FAILED")
    catalog = set(CONFIG_PATHS) | CONFIG_FALSE | CONFIG_TRUE | set(CONFIG_FIXED) | CONFIG_NON_TRUST
    containers = set()
    for key in catalog:
        parts = key.rstrip(":").split("::")
        for index in range(1, len(parts)):
            containers.add("::".join(parts[:index]))
    settings = {}
    for line in text.splitlines():
        if not line.strip():
            continue
        match = re.fullmatch(r'\s*([A-Za-z0-9_.:-]+)\s+"([^"\\\r\n]*)"\s*;\s*', line)
        require(match is not None, "APT_CONFIG_PARSE_FAILED")
        raw_key, value = match.groups()
        key = raw_key.lower()
        with option_context(key, value, boolean=key in CONFIG_TRUE or key in CONFIG_FALSE):
            require(key not in settings or key == "acquire::languages::", "APT_OPTION_DUPLICATE_OR_COLLISION")
            if key in CONFIG_PATHS:
                require(value == CONFIG_PATHS[key], "APT_CONFIGURATION_PATH_UNGOVERNED")
            elif key in CONFIG_FALSE:
                require(trust_boolean(value) is False, "APT_UNSAFE_TRUST_OPTION")
            elif key in CONFIG_TRUE:
                require(trust_boolean(value) is True, "APT_UNSAFE_TRUST_OPTION")
            elif key in CONFIG_FIXED:
                require(value == CONFIG_FIXED[key], "APT_CONFIGURATION_VALUE_UNGOVERNED")
            elif key in CONFIG_NON_TRUST:
                # Translation language selection does not change package signature,
                # key, repository-origin or package-hash authentication. No wildcard
                # acceptance of other Acquire properties is implied.
                require(value == "" and key == "acquire::languages" or bool(re.fullmatch(r"[A-Za-z]{2,3}(?:_[A-Za-z]{2})?|none|environment", value)), "APT_LANGUAGE_METADATA_MALFORMED")
            elif key in containers:
                require(value == "", "APT_NAMESPACE_VALUE_UNGOVERNED")
            else:
                raise PolicyFailure("APT_UNKNOWN_OPTION")
        settings[key] = value
    return settings


# Gate78: reviewed evidence, never a baseline learned from the observed dump.
BASELINE_SHA256 = "a7cdfa262800a446d42d02e59079c053f782e9d50638f3dc611d981d37bfee9e"
PROVENANCE_DOMAINS = frozenset({
    "APT_CONFIG_FILE", "RUNNER_IMAGE_PROVISIONED", "BASE_IMAGE_PROVISIONED",
    "WORKFLOW_INJECTED", "LOCAL_ACTION_INJECTED", "THIRD_PARTY_ACTION_INJECTED",
    "ENVIRONMENT_INJECTED", "COMMAND_LINE_INJECTED", "REPOSITORY_CONTROLLED",
})


def baseline_document():
    import hashlib
    import json
    from pathlib import Path

    raw = Path(__file__).with_name("apt-intrinsic-baseline.json").read_bytes()
    require(len(raw) <= 1048576, "APT_PROVENANCE_INVALID")
    require(hashlib.sha256(raw).hexdigest() == BASELINE_SHA256, "APT_PROVENANCE_INVALID")

    def unique_pairs(pairs):
        result = {}
        for key, value in pairs:
            require(key not in result, "APT_PROVENANCE_INVALID")
            result[key] = value
        return result

    try:
        document = json.loads(raw, object_pairs_hook=unique_pairs)
    except (ValueError, TypeError):
        raise PolicyFailure("APT_PROVENANCE_INVALID") from None
    return document


def _provenance_key(key):
    require(isinstance(key, str) and len(key) <= 256, "APT_PROVENANCE_INVALID")
    require(re.fullmatch(r"[a-z0-9_.-]+(?:::[a-z0-9_.-]+)*(?:::)?", key), "APT_PROVENANCE_INVALID")
    return key


def _provenance_value(record, value):
    require(isinstance(value, str), "APT_PROVENANCE_INVALID")
    if record["kind"] == "boolean":
        return "1" if trust_boolean(value) else "0"
    return value


def _provenance_state(document, provenance):
    require(isinstance(document, dict) and document.get("schema") == 1, "APT_PROVENANCE_INVALID")
    require(isinstance(provenance, dict), "APT_PROVENANCE_ABSENT")
    require(set(provenance) == {"identity", "command", "environment", "inventory_complete", "artifacts"}, "APT_PROVENANCE_INVALID")
    require(provenance["identity"] == document["identity"], "APT_VERSION_MISMATCH")
    require(document["identity"] == {"distribution": "ubuntu-noble", "version": "2.8.3", "libapt_version": "2.8.3", "architecture": "amd64", "build": "Ubuntu apt/libapt-pkg6.0t64 2.8.3"}, "APT_VERSION_MISMATCH")
    require(provenance["command"] == ["/usr/bin/apt-config", "dump"], "APT_PROVENANCE_UNQUALIFIED")
    require(provenance["environment"] == "CONTROLLED_NO_APT_CONFIG", "APT_PROVENANCE_UNQUALIFIED")
    require(provenance["inventory_complete"] is True, "APT_PROVENANCE_ABSENT")
    require(document["source"]["sha256"] == "088522b3613b28fdbcfa61f1f7e476bf6dc6b0120a8f74409e9527580c9f9d3b", "APT_PROVENANCE_INVALID")
    build = document.get("build_evidence")
    require(isinstance(build, dict) and build == QUALIFIED_BUILD_EVIDENCE, "APT_PROVENANCE_INVALID")
    records = {}
    for row in document["intrinsic"]:
        require(set(row) == {"key", "kind", "values", "source", "phase", "condition"}, "APT_PROVENANCE_INVALID")
        key = _provenance_key(row["key"])
        require(key not in records and row["kind"] in {"scalar", "boolean", "list", "container"}, "APT_PROVENANCE_INVALID")
        require(row["phase"] == "PRE_CONFIG_FILES" and bool(row["source"]) and bool(row["condition"]), "APT_PROVENANCE_INVALID")
        values = row["values"]
        require(isinstance(values, list) and values and all(isinstance(v, str) for v in values), "APT_PROVENANCE_INVALID")
        require(row["kind"] == "list" or len(values) == 1, "APT_PROVENANCE_INVALID")
        require((row["kind"] == "list") == key.endswith("::"), "APT_PROVENANCE_INVALID")
        require(row["kind"] != "container" or values == [""], "APT_PROVENANCE_INVALID")
        records[key] = dict(row, origin="TRUSTED_APT_INTRINSIC")
    # Explicit parent records only; no namespace wildcard or unknown descendants.
    for key in records:
        parts = key.rstrip(":").split("::")
        for i in range(1, len(parts)):
            parent = "::".join(parts[:i])
            require(parent in records and records[parent]["kind"] in {"container", "scalar"}, "APT_PROVENANCE_INVALID")
        if key.endswith("::"):
            require(key[:-2] in records and records[key[:-2]]["kind"] == "container", "APT_PROVENANCE_INVALID")
    certificates = document["external_certificates"]
    require(isinstance(certificates, list), "APT_PROVENANCE_INVALID")
    certs = {}
    for cert in certificates:
        require(set(cert) == {"path", "sha256", "source_class", "version", "effects", "review"}, "APT_PROVENANCE_INVALID")
        require(cert["path"] not in certs and cert["source_class"] in PROVENANCE_DOMAINS, "APT_PROVENANCE_INVALID")
        require(cert["path"] == "/etc/apt/apt.conf" or re.fullmatch(r"/etc/apt/apt\.conf\.d/[A-Za-z0-9_-]+(?:\.conf)?", cert["path"]), "APT_PROVENANCE_INVALID")
        require(cert["version"] == document["identity"]["version"] and bool(cert["review"]), "APT_PROVENANCE_INVALID")
        require(re.fullmatch(r"[0-9a-f]{64}", cert["sha256"]), "APT_PROVENANCE_INVALID")
        certs[cert["path"]] = cert
    artifacts = provenance["artifacts"]
    require(isinstance(artifacts, list) and len(artifacts) <= 128, "APT_PROVENANCE_INVALID")
    paths = [artifact["path"] for artifact in artifacts]
    require(paths == sorted(paths, key=lambda path: (path == "/etc/apt/apt.conf", path.encode())), "APT_PROVENANCE_INVALID")
    seen = set()
    for artifact in artifacts:
        require(isinstance(artifact, dict) and set(artifact) == {"path", "sha256"}, "APT_PROVENANCE_INVALID")
        path = artifact["path"]
        require(path not in seen, "APT_PROVENANCE_INVALID")
        seen.add(path)
        require(path in certs, "APT_PROVENANCE_UNQUALIFIED")
        cert = certs[path]
        require(artifact["sha256"] == cert["sha256"], "APT_PROVENANCE_UNQUALIFIED")
        for effect in cert["effects"]:
            require(set(effect) == {"operation", "key", "kind", "values"}, "APT_PROVENANCE_INVALID")
            key = _provenance_key(effect["key"])
            require(effect["operation"] in {"set", "clear", "append"}, "APT_PROVENANCE_INVALID")
            if effect["operation"] == "clear":
                require(effect["values"] == [], "APT_PROVENANCE_INVALID")
                for target in list(records):
                    if target == key or target.startswith(key + "::"):
                        del records[target]
                continue
            require(effect["kind"] in {"scalar", "boolean", "container", "list"}, "APT_PROVENANCE_INVALID")
            values = effect["values"]
            require(isinstance(values, list) and values and all(isinstance(v, str) for v in values), "APT_PROVENANCE_INVALID")
            require(effect["kind"] == "list" or len(values) == 1, "APT_PROVENANCE_INVALID")
            require((effect["kind"] == "list") == key.endswith("::"), "APT_PROVENANCE_INVALID")
            require(effect["kind"] != "container" or values == [""], "APT_PROVENANCE_INVALID")
            if effect["operation"] == "append":
                require(key in records and records[key]["kind"] == "list", "APT_PROVENANCE_INVALID")
                values = records[key]["values"] + values
            records[key] = dict(key=key, kind=effect["kind"], values=values, origin="TRUSTED_EXPLICITLY_QUALIFIED_EXTERNAL", source=path, source_class=cert["source_class"], source_sha256=cert["sha256"], certificate_reference=cert["review"])
    return records


@source_context('APT_CONFIG')
def configuration_policy(text, provenance=None):
    """Admit effective records only with independent identity and input proof."""
    try:
        document = baseline_document()
        expected = _provenance_state(document, provenance)
        require(isinstance(text, str) and "\x00" not in text, "APT_CONFIG_PARSE_FAILED")
        observed = {}
        catalog = set(CONFIG_PATHS) | CONFIG_FALSE | CONFIG_TRUE | set(CONFIG_FIXED) | CONFIG_NON_TRUST
        for line in text.splitlines():
            if not line.strip():
                continue
            match = re.fullmatch(r'\s*([A-Za-z0-9_.:-]+)\s+"([^"\\\r\n]*)"\s*;\s*', line)
            require(match is not None, "APT_CONFIG_PARSE_FAILED")
            raw_key, value = match.groups()
            key = raw_key.lower()
            with option_context(key, value, boolean=key in CONFIG_TRUE or key in CONFIG_FALSE):
                # Existing unsafe/malformed value predicates always take priority.
                if key in catalog:
                    _explicit_configuration_policy(line)
                require(key in expected, "APT_UNKNOWN_OPTION")
                row = expected[key]
                require(key not in observed or row["kind"] == "list", "APT_OPTION_DUPLICATE_OR_COLLISION")
                observed.setdefault(key, []).append(_provenance_value(row, value))
                require(observed[key] == row["values"][:len(observed[key])], "APT_PROVENANCE_VALUE_MISMATCH")
        require(set(observed) == set(expected), "APT_PROVENANCE_INCOMPLETE")
        require(all(values == expected[key]["values"] for key, values in observed.items()), "APT_PROVENANCE_VALUE_MISMATCH")
        return QualifiedSettings({key: values[-1] for key, values in observed.items()}, decision_records(document, provenance, expected))
    except PolicyFailure:
        raise
    except Exception:
        raise PolicyFailure("APT_PROVENANCE_INVALID") from None


QUALIFIED_BUILD_EVIDENCE = {'authority': 'https://launchpad.net/ubuntu/+source/apt/2.8.3/+build/30594802', 'build_log_sha256': '4e76dc327f2c5d0f7a46cbd29b3169336ff81db241428faa3970d1f862ea61ee', 'buildinfo_sha256': '461dc14ce404909b889c716c9850a477e0a3dac2ed0487af4e5f42d258aca9a3', 'source_sha256': '088522b3613b28fdbcfa61f1f7e476bf6dc6b0120a8f74409e9527580c9f9d3b', 'identity': {'architecture': 'amd64', 'build': 'Ubuntu apt/libapt-pkg6.0t64 2.8.3', 'distribution': 'ubuntu-noble', 'libapt_version': '2.8.3', 'version': '2.8.3'}, 'configuration': {'CMAKE_INSTALL_SYSCONFDIR': '/etc', 'CONF_DIR': '/etc/apt'}, 'derivation': 'CMakeLists.txt:233; CMake/config.h.in:78; apt-pkg/init.cc:151; no CONF_DIR override in build invocation'}


class QualifiedSettings(dict):
    """Private validated policy output; public metadata never grants trust."""
    def __init__(self, settings, records):
        super().__init__(settings)
        self.records = records


def decision_records(document, provenance, expected):
    from diagnostics import identifier
    records = {}
    for key, row in sorted(expected.items()):
        external = row['origin'] == 'TRUSTED_EXPLICITLY_QUALIFIED_EXTERNAL'
        source = row.get('source', 'qualified-external-certificate')
        reference = row.get('certificate_reference', 'launchpad:ubuntu:apt:2.8.3:build:30594802')
        records[key] = dict(
            canonical_key=key,
            source_class=row.get('source_class', 'APT_UPSTREAM_INTRINSIC'),
            source_identity=dict(source_reference=identifier(source)[0],
                source_sha256=row.get('source_sha256', document['source']['sha256']),
                build_or_certificate_reference=identifier(reference)[0], verification_status='VERIFIED'),
            apt_identity=dict(version=provenance['identity']['version'], build='ubuntu-noble-apt-2.8.3-amd64',
                architecture=provenance['identity']['architecture'], binary_binding_status='VERIFIED', observation_status='OBSERVED'),
            baseline_identity=dict(sha256=BASELINE_SHA256, source_sha256=document['source']['sha256']),
            value_evidence=dict(value_class=row['kind'].upper(), qualified_value_match=True),
            override_status='QUALIFIED' if external else 'NONE_PROVEN',
            decision_class=row['origin'], reason_code='QUALIFIED_PROVENANCE_VALUE_MATCH')
    return records


def validate_decision_records(settings, text, provenance):
    # Re-evaluate private inputs, not public diagnostics or self-declared classes.
    require(type(settings) is QualifiedSettings, 'APT_PROVENANCE_ABSENT')
    fresh = configuration_policy(text, provenance)
    require(settings == fresh and settings.records == fresh.records, 'APT_PROVENANCE_INVALID')
    return settings.records
