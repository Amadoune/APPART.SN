"""Gate59 source normalization. Pure parsers; no package-manager mutation."""
import itertools
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
        require(key in FIELDS and key not in stanza, "unknown or duplicate source field")
        require("-----BEGIN" not in value, "embedded key unsupported")
        stanza[key] = value.strip()
        previous = key
    return records


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
                require("=" in token, "malformed source option")
                key, value = token.split("=", 1)
                require(key in OPTIONS, "unknown source option")
                key = OPTIONS[key]
                require(key not in record, "duplicate source option")
                record[key] = value.replace(",", " ")
        records.append(record)
    return records


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


def configuration_policy(text):
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
