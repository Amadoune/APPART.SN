"""Bounded failure metadata only. Never changes policy admission or reason text."""
from contextlib import contextmanager
from contextvars import ContextVar
from functools import wraps
import json
import re

_source = ContextVar('apt_diagnostic_source', default=('OTHER_EXACT', 'policy-input', None))
RECORD_LIMIT = 2048
_sensitive = re.compile(r'(?i)password|passwd|credential|secret|token|authorization|private|gh[pousr]_|github_pat_|[A-Za-z0-9]{32,}')


def identifier(value, maximum=256):
    if not isinstance(value, str):
        return 'UNAVAILABLE', False
    # Unknown name syntax is not treated as a license to publish arbitrary text.
    if len(value) > maximum:
        return 'OVERLONG_REDACTED', True
    if not re.fullmatch(r'[A-Za-z0-9_.:/-]+', value):
        return 'UNSAFE_IDENTIFIER_REDACTED', True
    # Paths/identifiers with credential labels are redacted in full: removing
    # only the label could leave the actual secret suffix visible.
    safe = 'REDACTED' if _sensitive.search(value) else value
    # Parsers supply their own normalized names. Preserve one-line spelling
    # and case-sensitive filesystem paths rather than normalize them again.
    return safe, safe != value


def category(reason):
    if reason == 'APT_UNKNOWN_OPTION' or reason in {'unknown source option', 'unknown or duplicate source field'}:
        return 'UNKNOWN_OPTION'
    if reason == 'APT_NAMESPACE_VALUE_UNGOVERNED':
        return 'UNKNOWN_NAMESPACE'
    if reason == 'APT_BOOLEAN_MALFORMED':
        return 'MALFORMED_TRUST_VALUE'
    if reason.startswith('APT_UNSAFE_'):
        return 'UNSAFE_RECOGNIZED_OPTION'
    if any(word in reason for word in ('source URI', 'source path', 'source port', 'unapproved source', 'source trust', 'Signed-By')):
        return 'SOURCE_PROVENANCE_FAILURE'
    if reason == 'INTERNAL_POLICY_FAILURE':
        return reason
    return 'PARSER_FAILURE'


def record(reason, name=None, value=None, presence='VALUE_UNAVAILABLE', boolean=False):
    fmt, source_id, path = _source.get()
    normalized, redacted = identifier(name)
    namespace, ns_redacted = identifier(name.rsplit('::', 1)[0] if isinstance(name, str) and '::' in name else name)
    safe_id, id_redacted = identifier(source_id, 64)
    safe_path, path_redacted = identifier(path) if path is not None else ('NOT_OBSERVED', False)
    classification = 'VALUE_OTHER'
    if boolean and isinstance(value, str):
        if value.lower() in {'true', 'yes', '1', 'on', 'with', 'enable'}:
            classification = 'BOOLEAN_TRUE'
        elif value.lower() in {'false', 'no', '0', 'off', 'without', 'disable'}:
            classification = 'BOOLEAN_FALSE'
        else:
            classification = 'BOOLEAN_MALFORMED'
    result = dict(format=fmt, source_id=safe_id, source_path=safe_path,
                  normalized_name=normalized, namespace=namespace,
                  value_presence=presence, value_class=classification,
                  reason_category=category(reason),
                  name_redacted=redacted or ns_redacted,
                  source_redacted=id_redacted or path_redacted)
    # Reason text stays exclusively in the existing safe first-failure field.
    assert len(json.dumps(result, sort_keys=True).encode('utf-8')) <= RECORD_LIMIT
    return result


@contextmanager
def option_context(name, value=None, presence='VALUE_PRESENT', boolean=False):
    try:
        yield
    except Exception as exc:
        if not hasattr(exc, 'diagnostic'):
            reason = str(exc) if type(exc).__name__ == 'PolicyFailure' else 'INTERNAL_POLICY_FAILURE'
            exc.diagnostic = record(reason, name, value, presence, boolean)
        raise


def source_context(fmt):
    def decorate(fn):
        @wraps(fn)
        def wrapped(*args, **kwargs):
            source_id = kwargs.pop('source_id', 'effective-apt-config' if fmt == 'APT_CONFIG' else 'policy-input')
            path = kwargs.pop('source_path', None)
            token = _source.set((fmt, source_id, path))
            try:
                return fn(*args, **kwargs)
            except Exception as exc:
                if not hasattr(exc, 'diagnostic'):
                    reason = str(exc) if type(exc).__name__ == 'PolicyFailure' else 'INTERNAL_POLICY_FAILURE'
                    exc.diagnostic = record(reason)
                raise
            finally:
                _source.reset(token)
        return wrapped
    return decorate
