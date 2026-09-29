"""Gate62 non-release execution vehicle. No automatic retry or fallback."""
import hashlib
import json
import os
from pathlib import Path
import re
import shutil
import stat
import subprocess
import signal
import sys
import tarfile
import threading
import urllib.request
import time

from apt_policy import ARCHIVE_KEY, PolicyFailure, configuration_policy, deb822, lists, normalize, require

SPEC_HASH = "9107f9f45f14c976f734e12a061cea08529146f4d15fe315b3a0ef77d7290efc"
SAFE_ENV = {"PATH": "/usr/bin:/bin", "LC_ALL": "C", "TZ": "UTC"}


def sha(data):
    return hashlib.sha256(data).hexdigest()


class Evidence:
    def __init__(self, root):
        self.root = root
        self.start = time.monotonic()
        self.data = {"stages": [], "observations": {}, "first_failure": None, "retry_count": 0}
        self._active_stage = None
        self._output_lock = threading.Lock()

    def save(self):
        self.data["elapsed_seconds"] = time.monotonic() - self.start
        encoded = json.dumps(self.data, sort_keys=True, indent=2).encode()
        require(len(encoded) <= 10485760, "structured evidence overflow")
        path = self.root / "evidence.json"
        path.write_bytes(encoded)

    def _failure(self, exc):
        # Latch the first qualified failure, including failures a caller catches.
        with self._output_lock:
            if self.data["first_failure"] is None:
                reason = str(exc) if isinstance(exc, PolicyFailure) else "internal observation failure: " + type(exc).__name__
                self.data["first_failure"] = {"stage": self._active_stage["stage"], "rule": reason}
                if hasattr(exc, "diagnostic"):
                    # Diagnostic JSON shares the stage stderr allowance. The
                    # original policy failure wins even if retention overflows.
                    # Charge a conservative pretty-printed field envelope,
                    # including indentation, key and separators, not just values.
                    rendered = json.dumps(exc.diagnostic, sort_keys=True, indent=2)
                    encoded = ("diagnostic: " + " ".join("        " + line for line in rendered.splitlines())).encode("utf-8")
                    budget = self._active_stage["output"]["stderr"]
                    budget["observed_bytes"] += len(encoded)
                    if len(encoded) <= max(0, 1048576 - budget["retained_bytes"]):
                        budget["retained_bytes"] += len(encoded)
                        self.data["first_failure"]["diagnostic"] = exc.diagnostic
                    else:
                        budget["overflow"] = True

    def command(self, argv, cwd=None, env=None):
        require(self._active_stage is not None, "command outside qualified stage")
        require(self.data["first_failure"] is None, "previous qualified failure")
        try:
            return self._command(argv, cwd, env)
        except Exception as exc:
            self._failure(exc)
            raise PolicyFailure(self.data["first_failure"]["rule"]) from None

    def _command(self, argv, cwd, env):
        # The stage owns two independent retained-output allowances. A command
        # cannot reset them. Raw private output is counted conservatively too.
        row = self._active_stage
        before = {label: dict(counts) for label, counts in row["output"].items()}
        proc = subprocess.Popen(argv, cwd=cwd, env=env or SAFE_ENV, stdout=subprocess.PIPE, stderr=subprocess.PIPE, start_new_session=True)
        captures = [bytearray(), bytearray()]
        def stop_process():
            try:
                os.killpg(proc.pid, signal.SIGKILL)
            except ProcessLookupError:
                pass
        def capture(pipe, buffer, label):
            try:
                while True:
                    block = pipe.read(16384)
                    if not block:
                        break
                    with self._output_lock:
                        budget = row["output"][label]
                        budget["observed_bytes"] += len(block)
                        available = max(0, 1048576 - budget["retained_bytes"])
                        kept = block[:available]
                        buffer.extend(kept)
                        budget["retained_bytes"] += len(kept)
                        excess = len(block) > available
                        if excess:
                            budget["overflow"] = True
                    if excess:
                        self._failure(PolicyFailure("stage output evidence overflow"))
                        stop_process()
                        break
            except Exception as exc:
                self._failure(exc)
                try:
                    stop_process()
                except Exception as stop_error:
                    self._failure(stop_error)
            finally:
                try:
                    pipe.close()
                except Exception as exc:
                    self._failure(exc)
        readers = [threading.Thread(target=capture, args=(pipe, buffer, label), daemon=True)
                   for pipe, buffer, label in zip((proc.stdout, proc.stderr), captures, ("stdout", "stderr"))]
        started_readers = []
        try:
            for reader in readers:
                reader.start()
                started_readers.append(reader)
            proc.wait(timeout=max(1, 3500 - (time.monotonic() - self.start)))
        except Exception as exc:
            self._failure(PolicyFailure("command deadline reached") if isinstance(exc, subprocess.TimeoutExpired) else exc)
            try:
                stop_process()
                proc.wait()
            except Exception as stop_error:
                self._failure(stop_error)
        finally:
            for reader in started_readers:
                reader.join(timeout=5)
        if any(reader.is_alive() for reader in started_readers):
            self._failure(PolicyFailure("command output pipe did not close"))
            raise PolicyFailure(self.data["first_failure"]["rule"])
        out, err = (bytes(value) for value in captures)
        observation = {"stage": row["stage"], "argv": argv, "exit": proc.returncode,
                       "stdout_bytes": len(out), "stderr_bytes": len(err),
                       "stdout_sha256": sha(out), "stderr_sha256": sha(err),
                       "stage_output_before": before,
                       "stage_output_after": {label: dict(counts) for label, counts in row["output"].items()}}
        index = len(self.data.get("commands", []))
        self.data.setdefault("commands", []).append(observation)
        # Apt configuration may contain credentials: retain only its digest and
        # normalized fields. Other output keeps the existing secret-safe rule.
        try:
            if argv[:2] != ["/usr/bin/apt-config", "dump"]:
                for label, stream in (("stdout", out), ("stderr", err)):
                    text = stream.decode("utf-8", errors="strict")
                    require(not re.search(r"-----BEGIN (?:[A-Z ]*PRIVATE KEY)|(?:gh[pousr]_[A-Za-z0-9]{20,})|https?://[^\s/]+:[^\s/]+@|(?:password|token|secret)\s*[=:]\s*[^\s]+", text, re.I), "sensitive command evidence rejected")
                    name = f"command-{index:04d}-{label}.txt"
                    (self.root / name).write_bytes(stream)
                    observation[label + "_file"] = name
        except Exception as exc:
            self._failure(exc)
        try:
            self.save()
        except Exception as exc:
            self._failure(exc)
        # Preserve the predecessor ordering: output/evidence checks precede the
        # exit-status guard. An already-observed overflow remains first.
        if proc.returncode != 0:
            self._failure(PolicyFailure("qualified command returned nonzero"))
        require(self.data["first_failure"] is None,
                self.data["first_failure"]["rule"] if self.data["first_failure"] else "command evidence failure")
        return out.decode("utf-8", errors="strict"), err.decode("utf-8", errors="strict")

    def stage(self, name, operation):
        require(self._active_stage is None, "nested qualified stage")
        require(self.data["first_failure"] is None, "previous qualified failure")
        require(not any(row["stage"] == name for row in self.data["stages"]), "duplicate qualified stage")
        started = time.monotonic()
        row = {"stage": name, "utc_start": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()), "status": "RUNNING",
               "output_bound_bytes_per_channel": 1048576,
               "output": {label: {"observed_bytes": 0, "retained_bytes": 0, "overflow": False}
                          for label in ("stdout", "stderr")}}
        self._active_stage = row
        self.data["stages"].append(row)
        try:
            self.save()
            result = operation()
            require(self.data["first_failure"] is None, "previous qualified failure")
            row["status"] = "PASS"
            return result
        except Exception as exc:
            row["status"] = "FAIL"
            self._failure(exc)
            raise PolicyFailure(self.data["first_failure"]["rule"]) from None
        finally:
            row["duration_seconds"] = time.monotonic() - started
            try:
                self.save()
            except Exception as exc:
                row["status"] = "FAIL"
                self._failure(exc)
                raise PolicyFailure(self.data["first_failure"]["rule"]) from None
            finally:
                self._active_stage = None

class AptObservation:
    def __init__(self, evidence, spec):
        self.evidence = evidence
        self.spec = spec
        self.seal = {}
        self.total = 0

    def read(self, path):
        path = Path(path)
        info = path.lstat()
        require(stat.S_ISREG(info.st_mode), "nonregular apt configuration or keyring")
        require(info.st_uid == 0 and not info.st_mode & 0o022, "unsafe apt file ownership or mode")
        data = path.read_bytes()
        require(len(data) <= 1048576, "apt file size overflow")
        if str(path) not in self.seal:
            self.total += len(data)
        self.seal[str(path)] = sha(data)
        require(len(self.seal) <= 128 and self.total <= 8388608, "apt observation limit exceeded")
        return data

    def text(self, path):
        data = self.read(path)
        require(b"\x00" not in data, "NUL in apt configuration")
        return data.decode("utf-8", errors="strict")

    def mirrors(self):
        result, priorities = [], set()
        for line in self.text("/etc/apt/apt-mirrors.txt").splitlines():
            if not line.strip() or line.lstrip().startswith("#"):
                continue
            fields = line.split()
            require(len(fields) in {1, 2}, "unknown mirror metadata")
            if len(fields) == 2:
                require(bool(re.fullmatch(r"priority:[0-9]+", fields[1])), "unknown mirror priority")
                require(fields[1] not in priorities, "duplicate mirror priority")
                priorities.add(fields[1])
            result.append(fields[0])
        require(bool(result), "empty mirror list")
        return result

    def keyring(self, path):
        self.read(path)
        owner, _ = self.evidence.command(["/usr/bin/dpkg-query", "-S", path])
        require(owner.strip().startswith("ubuntu-keyring: "), "keyring package provenance missing")
        keys, _ = self.evidence.command(["/usr/bin/gpg", "--batch", "--no-options", "--with-colons", "--show-keys", path])
        fingerprints = []
        primary = False
        for line in keys.splitlines():
            fields = line.split(":")
            if fields[0] == "pub":
                primary = True
            elif fields[0] == "sub":
                primary = False
            elif fields[0] == "fpr" and primary:
                require(len(fields) > 9, "malformed public key fingerprint")
                fingerprints.append(fields[9])
                primary = False
        require(bool(fingerprints), "no archive signing key")
        return fingerprints

    def observe(self):
        require(not os.environ.get("APT_CONFIG"), "ambient APT_CONFIG")
        config, _ = self.evidence.command(["/usr/bin/apt-config", "dump"])
        # Do not retain raw apt configuration, which can contain credentials.
        settings = configuration_policy(config)
        expected_paths = {"dir": "/", "dir::etc": "etc/apt", "dir::etc::sourcelist": "sources.list", "dir::etc::sourceparts": "sources.list.d", "dir::etc::main": "apt.conf", "dir::etc::parts": "apt.conf.d", "dir::etc::trusted": "trusted.gpg", "dir::etc::trustedparts": "trusted.gpg.d"}
        require(all(settings.get(k) == v for k, v in expected_paths.items()), "alternate apt configuration paths")
        self.config_digest = sha(config.encode())
        config_paths = list(Path("/etc/apt/apt.conf.d").iterdir())
        if Path("/etc/apt/apt.conf").exists():
            config_paths.append(Path("/etc/apt/apt.conf"))
        for path in sorted(config_paths, key=lambda p: str(p).encode()):
            self.text(path)
        source_paths = list(Path("/etc/apt/sources.list.d").iterdir())
        if Path("/etc/apt/sources.list").exists():
            source_paths.append(Path("/etc/apt/sources.list"))
        records = []
        for source_index, path in enumerate(sorted(source_paths, key=lambda p: str(p).encode())):
            require(path.suffix in {".list", ".sources"}, "unexpected source file extension")
            contents = self.text(path)
            records.extend(deb822(contents, source_id=f"apt-source-{source_index}", source_path=str(path)) if path.suffix == ".sources" else lists(contents, source_id=f"apt-source-{source_index}", source_path=str(path)))
        self.path_inventory = sorted(str(p) for p in source_paths + config_paths)
        normalized = normalize(records, self.mirrors)
        observed_keys = {}
        for row in normalized:
            if not row["enabled"]:
                continue
            paths = [x for x in row["signed_by"] if x.startswith("/")]
            explicit = [x.rstrip("!").upper() for x in row["signed_by"] if not x.startswith("/")]
            if not paths:
                paths = [str(p) for p in sorted(Path("/etc/apt/trusted.gpg.d").iterdir())]
                if Path("/etc/apt/trusted.gpg").exists():
                    paths.append("/etc/apt/trusted.gpg")
            available = set()
            for path in paths:
                observed_keys[path] = self.keyring(path)
                available.update(observed_keys[path])
            effective = set(explicit) if explicit else available
            require(bool(effective) and effective <= available and effective == {ARCHIVE_KEY}, "unqualified effective archive signer")
        packages = self.spec["canonical_packages"]
        require(len(packages) == len({p["PACKAGE"] for p in packages}) == 30, "noncanonical direct package set")
        bindings = []
        for package in packages:
            matches = [r for r in normalized if r.get("kind") == "deb" and r.get("suite") == "noble" and package["REPOSITORY_COMPONENT"] in r.get("components", [])]
            require(bool(matches), "required package component binding absent")
            bindings.append({"package": package["PACKAGE"], "component": package["REPOSITORY_COMPONENT"], "source_count": len(matches)})
        self.evidence.data["observations"]["apt"] = {"sources": normalized, "key_fingerprints": observed_keys, "intended_bindings": bindings, "seal": self.seal, "config_digest": self.config_digest, "historical_trust_certified": False}
        self.evidence.save()

    def recheck(self):
        config, _ = self.evidence.command(["/usr/bin/apt-config", "dump"])
        require(sha(config.encode()) == self.config_digest, "apt configuration changed after observation")
        current = list(Path("/etc/apt/sources.list.d").iterdir()) + list(Path("/etc/apt/apt.conf.d").iterdir())
        for name in ("/etc/apt/sources.list", "/etc/apt/apt.conf"):
            if Path(name).exists():
                current.append(Path(name))
        require(sorted(str(p) for p in current) == self.path_inventory, "apt configuration file inventory changed")
        for path, expected in self.seal.items():
            require(sha(self.read(path)) == expected, "apt source or keyring changed after observation")


def runtime_guard(observation, layout, required):
    try:
        return (
            observation["php_path"] == layout["php"]
            and observation["php_version"] == "8.5.8"
            and set(required) <= set(observation["extensions"])
            and observation["configuration"] is True
            and observation["composer_path"] == layout["composer_wrapper"]
            and observation["composer_version"] == "2.9.4"
            and observation["composer_php"] == layout["php"]
            and observation["platform"] is True
        )
    except (KeyError, TypeError):
        return False


class Campaign:
    def __init__(self, spec, layout, evidence):
        self.spec, self.layout, self.evidence = spec, layout, evidence
        self.build_env = dict(item.split("=", 1) for item in spec["configure"]["environment"])
        self.runtime_env = dict(SAFE_ENV, PATH=layout["composer_wrapper"].rsplit("/", 1)[0] + ":" + layout["php"].rsplit("/", 1)[0] + ":/usr/bin:/bin", PHPRC=layout["ini"], PHP_INI_SCAN_DIR=layout["scan"], TMPDIR=layout["tmp"], HOME=layout["home"], COMPOSER_HOME=layout["composer_home"], COMPOSER_CACHE_DIR=layout["composer_cache"])
        self.required = [item["EXTENSION"] for item in spec["extension_verification"]]
        self.observation = {}
        self.apt = AptObservation(evidence, spec)

    def runner(self):
        fields = {key: os.environ.get(key) for key in ("RUNNER_NAME", "RUNNER_OS", "RUNNER_ARCH", "ImageOS", "ImageVersion")}
        require(all(fields.values()), "mandatory runner identity absent")
        require(fields["RUNNER_OS"] == "Linux" and fields["RUNNER_ARCH"] == "X64", "runner OS or architecture mismatch")
        os_release = {}
        for line in Path("/etc/os-release").read_text().splitlines():
            if "=" in line:
                k, v = line.split("=", 1)
                os_release[k] = v.strip('"')
        require(os_release.get("ID") == "ubuntu" and os_release.get("VERSION_ID") == "24.04" and os_release.get("VERSION_CODENAME") == "noble", "runner distribution mismatch")
        arch, _ = self.evidence.command(["/usr/bin/dpkg", "--print-architecture"])
        require(arch.strip() == "amd64", "dpkg architecture mismatch")
        # Locate the actual ancestor Runner.Worker, then the co-located listener.
        parent = os.getppid()
        listener = None
        for _ in range(32):
            executable = Path(f"/proc/{parent}/exe").resolve()
            if executable.name == "Runner.Worker":
                listener = executable.with_name("Runner.Listener")
                break
            status = Path(f"/proc/{parent}/status").read_text()
            parent = int(re.search(r"^PPid:\s+(\d+)$", status, re.M).group(1))
            if parent <= 1:
                break
        require(listener is not None and listener.is_file(), "runner agent executable identity unavailable")
        version, _ = self.evidence.command([str(listener), "--version"])
        require(bool(re.fullmatch(r"\d+\.\d+\.\d+", version.strip())), "runner agent version malformed")
        fields.update(RUNNER_VERSION=version.strip(), RUNNER_IMAGE_RELEASE=os.environ.get("ImageRelease", "NOT_EXPOSED"), OS_RELEASE=os_release)
        self.evidence.data["observations"]["runner"] = fields

    def packages(self):
        # python-apt is used only as a read/plan interface to the runner's apt.
        # It is never installed as an opportunistic dependency.
        import apt
        self.apt.recheck()
        out, err = self.evidence.command(["/usr/bin/sudo", "-n", "/usr/bin/apt-get", "-o", "APT::Update::Error-Mode=any", "-o", "Acquire::Retries=0", "-o", "Debug::Acquire::gpgv=true", "update"])
        require(not re.search(r"(^W:|^E:|NO_PUBKEY|BADSIG|EXPKEYSIG|REVKEYSIG|not signed|unauthenticated|insecure)", out + "\n" + err, re.M | re.I), "apt refresh authentication or freshness failure")
        signers = re.findall(r"\bVALIDSIG\s+([A-F0-9]{40})\b", out + "\n" + err)
        require(bool(signers) and set(signers) == {ARCHIVE_KEY}, "apt refreshed signing identity incomplete")
        cache = apt.Cache()
        direct = [p["PACKAGE"] for p in self.spec["canonical_packages"]]
        approved = {}

        def validate(version):
            require(version is not None and version.architecture in {"amd64", "all"}, "candidate architecture unresolved")
            require(bool(version.origins), "candidate origin missing")
            for origin in version.origins:
                if not origin.site:
                    continue
                require(origin.trusted and origin.origin == "Ubuntu" and origin.archive in {"noble", "noble-updates", "noble-security"} and origin.component in {"main", "restricted", "universe", "multiverse"}, "candidate outside authenticated Ubuntu boundary")
            require(any(o.site and o.trusted and o.origin == "Ubuntu" for o in version.origins), "candidate has no authenticated Ubuntu origin")
            record = version.record
            require(bool(re.fullmatch(r"[0-9a-f]{64}", record.get("SHA256", ""))) and record.get("Filename", "").startswith("pool/"), "candidate artifact hash binding incomplete")
            approved[version.package.name] = {"version": version.version, "architecture": version.architecture, "sha256": record["SHA256"], "filename": record["Filename"]}

        for name in direct:
            require(name in cache, "canonical package absent")
            validate(cache[name].candidate)
            require(any(o.component == "main" and o.origin == "Ubuntu" and o.trusted for o in cache[name].candidate.origins), "direct package component drift")
            cache[name].mark_install(auto_fix=False, auto_inst=True, from_user=True)
        require(cache.broken_count == 0, "broken package dependency plan")
        pending = list(direct)
        visited = set()
        while pending:
            name = pending.pop()
            if name in visited:
                continue
            visited.add(name)
            package = cache[name]
            version = package.candidate if package.marked_install or package.marked_upgrade or package.marked_reinstall else package.installed
            validate(version)
            require(not package.marked_delete and not package.marked_downgrade, "package removal or downgrade requested")
            for group in version.get_dependencies("PreDepends", "Depends"):
                choices = [target for dependency in group.or_dependencies for target in dependency.target_versions if target.package.is_installed or target.package.marked_install or target.package.marked_upgrade]
                require(bool(choices), "dependency provider unresolved")
                for choice in choices:
                    validate(choice)
                    pending.append(choice.package.name)
        requested = [name + "=" + cache[name].candidate.version for name in direct]
        command = ["/usr/bin/apt-get", "--no-install-recommends", "--no-remove", "-o", "Acquire::Retries=0", "install"] + requested
        plan, _ = self.evidence.command(command[:1] + ["--simulate"] + command[1:])
        require(not any(line.startswith("Remv ") for line in plan.splitlines()), "simulation proposes removals")
        planned = []
        for line in plan.splitlines():
            if line.startswith("Inst "):
                match = re.match(r"Inst ([^\s:]+)(?::\S+)?(?: \[[^\]]+\])? \(([^ ]+)", line)
                require(match is not None, "unparsed package transaction")
                name, version = match.groups()
                require(name in approved and approved[name]["version"] == version, "simulation differs from authenticated dependency closure")
                planned.append([name, version])
        self.evidence.data["observations"]["package_plan"] = {"direct": requested, "closure": approved, "transaction": planned, "plan_sha256": sha(plan.encode())}
        self.evidence.save()
        self.apt.recheck()
        final_plan, _ = self.evidence.command(command[:1] + ["--simulate"] + command[1:])
        require(final_plan == plan, "package transaction changed before install")
        self.evidence.command(["/usr/bin/sudo", "-n"] + command[:1] + ["--assume-yes"] + command[1:], env=dict(SAFE_ENV, DEBIAN_FRONTEND="noninteractive"))
        self.package_closure = approved

    def download(self, model, filename):
        target = Path(self.layout["downloads"]) / filename
        require(not target.exists(), "download target reused")
        digest = hashlib.sha256()
        total = 0
        with urllib.request.urlopen(model["url"], timeout=60) as response, target.open("xb") as output:
            require(response.url == model["url"], "unapproved artifact redirect")
            while True:
                block = response.read(1024 * 1024)
                if not block:
                    break
                total += len(block)
                require(total <= 536870912, "runtime artifact size limit")
                digest.update(block)
                output.write(block)
        actual = digest.hexdigest()
        self.evidence.data["observations"][filename] = {"expected_sha256": model["sha256"], "actual_sha256": actual, "bytes": total}
        require(actual == model["sha256"], "runtime artifact verification failed")
        return target

    def source(self):
        archive = self.download(self.spec["php_source_identity"], "php-8.5.8.tar.xz")
        with tarfile.open(archive, "r:xz") as contents:
            members = contents.getmembers()
            require(bool(members), "empty source archive")
            for member in members:
                parts = Path(member.name).parts
                require(not member.name.startswith("/") and parts[0] == "php-8.5.8" and ".." not in parts, "unsafe source archive path")
                require(member.isfile() or member.isdir(), "source archive link or special file requires unresolved handling")
            contents.extractall(Path(self.layout["source"]).parent, members=members, filter="data")
        require(Path(self.layout["source"], "configure").is_file(), "verified configure entrypoint absent")

    def configure(self):
        build = Path(self.layout["build"])
        require(not any(build.iterdir()), "build directory not fresh")
        for tool in ("gcc", "g++", "make", "pkg-config", "sh"):
            path = Path("/usr/bin" if tool != "sh" else "/bin") / tool
            require(path.is_file(), "qualified build tool missing")
            owner, _ = self.evidence.command(["/usr/bin/dpkg-query", "-S", str(path.resolve())])
            require(bool(owner.strip()) and not str(path.resolve()).startswith("/usr/local/"), "unqualified build tool ownership")
        for name, minimum in (("sqlite3", "3.7.17"), ("libxml-2.0", "2.9.4"), ("libcurl", "7.61.0"), ("icu-uc", "57.1"), ("icu-io", "57.1"), ("icu-i18n", "57.1"), ("oniguruma", None), ("libpq", "10.0"), ("libsodium", "1.0.8"), ("openssl", "1.1.1")):
            self.evidence.command(["/usr/bin/pkg-config", "--exists", name], env=self.build_env)
            if minimum:
                self.evidence.command(["/usr/bin/pkg-config", "--atleast-version=" + minimum, name], env=self.build_env)
            flags, _ = self.evidence.command(["/usr/bin/pkg-config", "--cflags", "--libs", name], env=self.build_env)
            require("/usr/local" not in flags and "-Wl,-rpath" not in flags, "unqualified library resolution")
        flags = [flag.replace("${PHP_INSTALL_PREFIX}", self.layout["prefix"]) for flag in self.spec["configure"]["flags"]]
        self.evidence.command(["/bin/sh", self.layout["source"] + "/configure"] + flags, cwd=build, env=self.build_env)
        makefile = (build / "Makefile").read_text()
        require(re.search(r"^prefix\s*=\s*" + re.escape(self.layout["prefix"]) + r"\s*$", makefile, re.M) is not None, "generated install prefix mismatch")

    def compile(self):
        self.evidence.command(self.spec["compile"]["argv"], cwd=self.layout["build"], env=self.build_env)

    def install(self):
        self.evidence.command(self.spec["install"]["argv"], cwd=self.layout["build"], env=self.build_env)

    def php(self):
        layout = self.layout
        expected_ini = self.spec["configuration"]["ini_template"].replace("<RESOLVED_QUALIFICATION_ROOT>", layout["root"]).encode()
        Path(layout["ini"]).write_bytes(expected_ini)
        require(not any(Path(layout["scan"]).iterdir()), "configuration scan directory not empty")
        require(shutil.which("php", path=self.runtime_env["PATH"]) == layout["php"], "ambient PHP selected")
        require(str(Path(layout["php"]).resolve()) == layout["php"], "PHP path symlink mismatch")
        probe = r'''$f=['proc_close','proc_get_status','proc_open','proc_terminate','shell_exec']; $d=[];foreach(['disable_functions','open_basedir','auto_prepend_file','auto_append_file','sys_temp_dir'] as $k){$d[$k]=ini_get($k);}echo json_encode(['binary'=>realpath(PHP_BINARY),'version'=>PHP_VERSION,'sapi'=>PHP_SAPI,'ini'=>php_ini_loaded_file(),'scan'=>php_ini_scanned_files(),'extensions'=>get_loaded_extensions(),'extension_dir'=>ini_get('extension_dir'),'functions'=>array_map('is_callable',$f),'directives'=>$d],JSON_THROW_ON_ERROR);'''
        observed, _ = self.evidence.command([layout["php"], "-r", probe], env=self.runtime_env)
        data = json.loads(observed)
        require(set(data) == {"binary", "version", "sapi", "ini", "scan", "extensions", "extension_dir", "functions", "directives"}, "PHP observation schema mismatch")
        require(data["binary"] == layout["php"] and data["version"] == "8.5.8" and data["sapi"] == "cli", "PHP executable identity mismatch")
        require(set(self.required) <= set(data["extensions"]), "required extension not loaded")
        require(data["ini"] == layout["ini"] and data["scan"] in {False, "", None}, "ambient PHP configuration")
        require(all(data["functions"]) and len(data["functions"]) == 5, "required process function disabled")
        require(data["directives"] == {"disable_functions": "", "open_basedir": "", "auto_prepend_file": "", "auto_append_file": "", "sys_temp_dir": layout["tmp"]}, "PHP configuration mismatch")
        child = r'''$cmd=[PHP_BINARY,'-r',$argv[1]];$p=proc_open($cmd,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);if(!is_resource($p)){exit(90);}fclose($pipes[0]);echo stream_get_contents($pipes[1]);fclose($pipes[1]);$e=stream_get_contents($pipes[2]);fclose($pipes[2]);if($e!==''){exit(91);}exit(proc_close($p));'''
        child_output, _ = self.evidence.command([layout["php"], "-r", child, probe], env=self.runtime_env)
        require(json.loads(child_output) == data, "PHP child environment mismatch")
        temp_probe = r'''$p=tempnam(sys_get_temp_dir(),'gate62-');if($p===false||dirname($p)!==sys_get_temp_dir()){exit(92);}if(file_put_contents($p,'gate62')!==6||file_get_contents($p)!=='gate62'||!unlink($p)){exit(93);}'''
        self.evidence.command([layout["php"], "-r", temp_probe], env=self.runtime_env)
        linkage, _ = self.evidence.command(["/usr/bin/ldd", layout["php"]])
        require("not found" not in linkage and "/usr/local/" not in linkage, "PHP runtime linkage unresolved")
        for library in re.findall(r"(?:=>\s+)?(/[^\s]+)", linkage):
            owner, _ = self.evidence.command(["/usr/bin/dpkg-query", "-S", library])
            require(bool(owner.strip()), "runtime library provenance unresolved")
        require(Path(layout["ini"]).read_bytes() == expected_ini, "configuration bytes changed")
        data["sha256"] = sha(Path(layout["php"]).read_bytes())
        self.evidence.data["observations"]["php"] = data
        self.observation.update(php_path=layout["php"], php_version=data["version"], extensions=data["extensions"], configuration=True)

    def composer(self):
        layout = self.layout
        artifact = self.download(self.spec["composer_identity"], "composer-2.9.4.phar")
        artifact.replace(layout["composer"])
        wrapper = '#!/bin/sh\nexec "' + layout["php"] + '" "' + layout["composer"] + '" "$@"\n'
        Path(layout["composer_wrapper"]).write_text(wrapper)
        Path(layout["composer_wrapper"]).chmod(0o700)
        require(shutil.which("composer", path=self.runtime_env["PATH"]) == layout["composer_wrapper"], "ambient Composer selected")
        version, _ = self.evidence.command([layout["php"], layout["composer"], "--no-plugins", "--no-scripts", "--version", "--no-ansi"], env=self.runtime_env)
        match = re.search(r"^Composer version ([0-9]+\.[0-9]+\.[0-9]+)(?:\s|$)", version)
        require(match is not None and match.group(1) == "2.9.4", "Composer version mismatch")
        require(Path(layout["composer_wrapper"]).read_text() == wrapper, "Composer wrapper bytes changed")
        self.observation.update(composer_path=layout["composer_wrapper"], composer_version=match.group(1), composer_php=layout["php"])
        self.evidence.data["observations"]["composer"] = {"path": layout["composer_wrapper"], "version": match.group(1), "php": layout["php"], "wrapper_sha256": sha(wrapper.encode())}

    def platform(self):
        workspace = Path(os.environ["GITHUB_WORKSPACE"])
        before = {name: sha((workspace / name).read_bytes()) for name in ("composer.json", "composer.lock")}
        require(sha(Path(self.layout["composer"]).read_bytes()) == self.spec["composer_identity"]["sha256"], "Composer artifact changed")
        output, _ = self.evidence.command([self.layout["php"], self.layout["composer"]] + self.spec["project_platform"]["argv_suffix"], cwd=workspace, env=dict(self.runtime_env, COMPOSER_DISABLE_NETWORK="1"))
        results = json.loads(output)
        require(isinstance(results, list) and bool(results) and all(item.get("status") == "success" for item in results), "project platform mismatch")
        require(before == {name: sha((workspace / name).read_bytes()) for name in before}, "project platform authority changed")
        self.evidence.data["observations"]["platform"] = results
        self.observation["platform"] = True

    def negative(self):
        import copy
        require(runtime_guard(self.observation, self.layout, self.required), "real runtime positive guard failed")
        modifications = [dict(php_path="/usr/bin/php"), dict(php_version="8.5.11"), dict(extensions=self.required[1:]), dict(configuration=False), dict(composer_path="/usr/bin/composer"), dict(composer_version="2.10.3"), dict(composer_php="/usr/bin/php"), dict(platform=False)]
        results = []
        for changes in modifications:
            fixture = copy.deepcopy(self.observation)
            fixture.update(changes)
            rejected = not runtime_guard(fixture, self.layout, self.required)
            results.append({"modified_fields": sorted(changes), "rejected": rejected})
            require(rejected, "negative runtime observation admitted")
        self.evidence.data["observations"]["negative_matrix"] = results


def main():
    require(sys.platform == "linux", "Hosted Linux execution required")
    require(os.environ.get("GITHUB_EVENT_NAME") == "workflow_dispatch" and os.environ.get("GITHUB_RUN_ATTEMPT") == "1", "non-release first manual attempt required")
    spec_bytes = Path(__file__).with_name("gate61-spec.json").read_bytes()
    require(sha(spec_bytes) == SPEC_HASH, "frozen Gate61 specification mismatch")
    spec = json.loads(spec_bytes)
    run_id = os.environ.get("GITHUB_RUN_ID", "")
    require(run_id.isdigit(), "invalid qualification run identity")
    temporary = Path(os.environ["RUNNER_TEMP"])
    require(temporary.is_absolute() and temporary.resolve() == temporary and temporary.stat().st_uid == os.getuid(), "unqualified runner temporary root")
    root = temporary / ("appart-php858-qualification-" + run_id + "-1")
    require(not root.exists(), "qualification root already exists")
    require(not any(c in str(root) for c in "\"'\r\n\x00"), "unsafe qualification root characters")
    workspace = Path(os.environ["GITHUB_WORKSPACE"]).resolve()
    require(not root.is_relative_to(workspace) and not workspace.is_relative_to(root), "repository and qualification root overlap")
    root.mkdir(mode=0o700)
    layout = {key: value.replace(spec["layout"]["root"], str(root)) for key, value in spec["layout"].items()}
    for key in ("build", "prefix", "scan", "tmp", "evidence", "downloads", "home", "composer_home", "composer_cache"):
        Path(layout[key]).mkdir(mode=0o700, parents=True, exist_ok=True)
    for key in ("source", "composer", "composer_wrapper", "ini"):
        Path(layout[key]).parent.mkdir(mode=0o700, parents=True, exist_ok=True)
    evidence = Evidence(Path(layout["evidence"]))
    campaign = Campaign(spec, layout, evidence)
    try:
        for name, operation in (("runner observation", campaign.runner), ("apt trust", campaign.apt.observe), ("apt index/package", campaign.packages), ("source verification", campaign.source), ("configure", campaign.configure), ("compile", campaign.compile), ("install", campaign.install), ("post-install PHP verification", campaign.php), ("Composer provisioning/verification", campaign.composer), ("platform", campaign.platform), ("runtime negative matrix", campaign.negative)):
            evidence.stage(name, operation)
        evidence.data["technical_success"] = True
        evidence.data["release_time_fit"] = "UNRESOLVED"
        evidence.data["cache_required"] = "UNRESOLVED"
        evidence.data["time_fit_limitation"] = "Retained successful Packaging B/upload upper bounds are absent; no release-fit assertion is possible from provisioning duration alone."
        return 0
    except Exception:
        return 1
    finally:
        evidence.save()
        # Retain owned temporary state until evidence upload and ephemeral teardown.
        # No cleanup can obscure the first failure or delete outside this root.
        print(json.dumps({"evidence": str(evidence.root), "first_failure": evidence.data["first_failure"]}))


if __name__ == "__main__":
    sys.exit(main())
