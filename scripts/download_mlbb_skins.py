#!/usr/bin/env python3
"""Download MLBB skin images and rarity badges from the Fandom wiki.

Images are stored under storage/app/public/mlbb_skins/{hero}/ and
storage/app/public/mlbb_skin_tags/. Progress is saved after every hero
and badge so the script can be re-run after a crash or rate limit.

Usage:
    pip install -r scripts/requirements-mlbb-skins.txt
    python scripts/download_mlbb_skins.py
    python scripts/download_mlbb_skins.py --badges-only
    python scripts/download_mlbb_skins.py --heroes Hayabusa,Gusion
"""

from __future__ import annotations

import argparse
import json
import os
import re
import sys
import time
from datetime import datetime, timezone
from pathlib import Path
from typing import Any
from urllib.parse import urljoin

import requests
from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[1]
WIKI_API = "https://mobile-legends.fandom.com/api.php"
WIKI_BASE = "https://mobile-legends.fandom.com"
MAPI_HEROES = "https://mapi.mobilelegends.com/hero/list"
USER_AGENT = "WasitMlbbSkinDownloader/1.0 (local catalog sync; contact: wassitmarket)"

KNOWN_BADGES = [
    "Collector",
    "Elite",
    "Special",
    "Epic",
    "Legend",
    "Starlight",
    "Annual Starlight",
    "Prime",
    "Luckybox",
    "Limited",
    "Basic",
    "M7",
    "Neobeasts",
    "Neobeast",
    "V.E.N.O.M",
    "VENOM",
    "Transformers",
    "Kung Fu Panda",
    "Sanrio",
    "Aspirants",
    "Zodiac",
    "Superhero",
    "Lightborn",
    "Dragon Tamer",
    "Champion",
    "Collaboration",
    "SABER",
    "MSK",
]

TAG_ALIASES = {
    "starlight": "Starlight",
    "neobeast": "Neobeasts",
    "m7": "M7",
}

RARITY_PRIORITY = [
    "Legend",
    "Collector",
    "Prime",
    "Luckybox",
    "Annual Starlight",
    "Starlight",
    "Epic",
    "Special",
    "Elite",
    "Basic",
]

HERO_ALIASES = {
    "Selina": "Selena",
}


def utc_now() -> str:
    return datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")


def load_env(path: Path) -> dict[str, str]:
    env: dict[str, str] = {}
    if not path.is_file():
        return env
    for raw in path.read_text(encoding="utf-8", errors="ignore").splitlines():
        line = raw.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        value = value.strip().strip('"').strip("'")
        env[key.strip()] = value
    return env


def slug(value: str) -> str:
    value = (value or "").strip().lower()
    value = value.replace("'", "").replace("'", "")
    value = re.sub(r"[^a-z0-9]+", "-", value)
    return value.strip("-") or "item"


def normalize_name(value: str) -> str:
    return re.sub(r"\s+", " ", (value or "").strip().lower())


def extract_lua_table(source: str, offset: int) -> str | None:
    start = source.find("{", offset)
    if start < 0:
        return None
    depth = 0
    for index in range(start, len(source)):
        char = source[index]
        if char == "{":
            depth += 1
        elif char == "}":
            depth -= 1
            if depth == 0:
                return source[start : index + 1]
    return None


def named_lua_table(source: str, name: str) -> str | None:
    match = re.search(r'\["' + re.escape(name) + r'"\]\s*=\s*', source)
    if not match:
        return None
    return extract_lua_table(source, match.end())


def parse_lua_fields(entry: str) -> dict[str, str]:
    return {
        match.group(1): match.group(2)
        for match in re.finditer(r'\["([^"]+)"\]\s*=\s*"([^"]*)"', entry)
    }


def normalize_tag(tag: str) -> str:
    tag = (tag or "").strip()
    if not tag:
        return ""
    return TAG_ALIASES.get(tag.lower(), tag)


def primary_rarity(tag_names: list[str], painted: bool = False) -> str:
    if painted:
        return "Painted"
    names = [name for name in tag_names if name.lower() != "painted"]
    for wanted in RARITY_PRIORITY:
        for name in names:
            if name.lower() == wanted.lower():
                return name
    return names[0] if names else "Basic"


def to_full_size(url: str) -> str:
    return re.sub(r"/revision/latest/scale-to-width-down/\d+", "/revision/latest", url or "")


def resolve_url(url: str) -> str | None:
    url = (url or "").strip()
    if not url or url.startswith("data:"):
        return None
    if url.startswith("//"):
        return "https:" + url
    if url.startswith("/"):
        return urljoin(WIKI_BASE, url)
    return url


def parse_caption(raw: str) -> str:
    text = re.sub(r"<[^>]+>", " ", raw or "")
    text = re.sub(r"\s+", " ", text).strip()
    if " - " in text:
        text = text.split(" - ", 1)[0].strip()
    return text


def humanize_filename(name: str) -> str:
    name = re.sub(r"\.(png|jpg|jpeg|gif|webp)$", "", name or "", flags=re.I)
    name = name.replace("_", " ").strip()
    return name


def atomic_write_json(path: Path, payload: Any) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    tmp = path.with_suffix(path.suffix + ".tmp")
    tmp.write_text(json.dumps(payload, ensure_ascii=False, indent=2), encoding="utf-8")
    tmp.replace(path)


class WikiClient:
    def __init__(self, delay: float) -> None:
        self.delay = max(0.4, delay)
        self.session = requests.Session()
        self.session.headers.update(
            {
                "User-Agent": USER_AGENT,
                "Accept": "application/json,text/html,*/*;q=0.8",
                "Referer": WIKI_BASE + "/",
            }
        )
        self._last_api = 0.0

    def _wait(self, seconds: float) -> None:
        elapsed = time.time() - self._last_api
        remaining = seconds - elapsed
        if remaining > 0:
            time.sleep(remaining)

    def request(self, params: dict[str, Any], retries: int = 6) -> dict[str, Any]:
        payload = {"format": "json", "formatversion": 2, **params}
        wait = self.delay
        for attempt in range(retries):
            self._wait(wait if attempt else self.delay)
            response = self.session.get(WIKI_API, params=payload, timeout=40)
            self._last_api = time.time()
            if response.status_code == 429:
                retry_after = response.headers.get("Retry-After")
                wait = float(retry_after) if retry_after and retry_after.isdigit() else min(90, 8 * (attempt + 1))
                print(f"  rate limited (429). waiting {wait:.0f}s then retrying...")
                continue
            if response.status_code >= 500:
                wait = min(60, 4 * (attempt + 1))
                print(f"  wiki {response.status_code}. waiting {wait:.0f}s...")
                continue
            response.raise_for_status()
            data = response.json()
            if isinstance(data, dict) and data.get("error"):
                code = str(data["error"].get("code") or "")
                if code == "ratelimited":
                    wait = min(90, 8 * (attempt + 1))
                    print(f"  wiki API ratelimited. waiting {wait:.0f}s...")
                    continue
            return data if isinstance(data, dict) else {}
        raise RuntimeError("Fandom wiki kept rate-limiting or failing.")

    def download(self, url: str, dest: Path, force: bool, retries: int = 5) -> bool:
        if dest.is_file() and dest.stat().st_size > 0 and not force:
            return True
        dest.parent.mkdir(parents=True, exist_ok=True)
        wait = 0.4
        for attempt in range(retries):
            try:
                if attempt:
                    time.sleep(wait)
                response = self.session.get(url, timeout=60, stream=True)
                if response.status_code == 429:
                    wait = min(90, 8 * (attempt + 1))
                    print(f"  image 429, waiting {wait:.0f}s...")
                    continue
                if response.status_code >= 400:
                    wait = min(30, 3 * (attempt + 1))
                    continue
                tmp = dest.with_suffix(dest.suffix + ".part")
                with tmp.open("wb") as handle:
                    for chunk in response.iter_content(64 * 1024):
                        if chunk:
                            handle.write(chunk)
                if tmp.stat().st_size < 32:
                    tmp.unlink(missing_ok=True)
                    continue
                tmp.replace(dest)
                return True
            except requests.RequestException as exc:
                wait = min(30, 3 * (attempt + 1))
                print(f"  download error: {exc}")
        return False


def parse_gallery(html: str) -> list[dict[str, str]]:
    if not html.strip():
        return []
    soup = BeautifulSoup(html, "html.parser")
    items: list[dict[str, str]] = []
    seen: set[str] = set()
    for image in soup.select("img.thumbimage, img.mw-file-element"):
        name = parse_caption(image.get("data-caption") or image.get("alt") or "")
        if not name:
            name = humanize_filename(image.get("data-image-name") or "")
        thumb = resolve_url(image.get("data-src") or image.get("src") or "")
        if not name or not thumb:
            continue
        key = normalize_name(name)
        if key in seen:
            continue
        seen.add(key)
        items.append(
            {
                "name": name,
                "thumbnail_url": thumb,
                "image_url": to_full_size(thumb),
            }
        )
    return items


def parse_skin_boxes(html: str) -> dict[str, dict[str, Any]]:
    rarities: dict[str, dict[str, Any]] = {}
    if not html.strip():
        return rarities
    soup = BeautifulSoup(html, "html.parser")
    for box in soup.select("div.skin-box"):
        heading = ""
        previous = box.find_previous(["h1", "h2", "h3", "h4"])
        if previous:
            heading = previous.get_text(" ", strip=True).lower()
        if "statue" in heading:
            continue
        painted = "painted" in heading
        name_el = box.select_one(".skin-box-name")
        name = name_el.get_text(" ", strip=True) if name_el else ""
        if not name:
            continue
        tags: list[dict[str, str | None]] = []
        for tag_img in box.select(".skin-box-tag img"):
            label = humanize_filename(tag_img.get("data-image-name") or tag_img.get("alt") or "")
            label = re.sub(r"\s*skin\s*tag$", "", label, flags=re.I).strip()
            if not label:
                continue
            thumb = resolve_url(tag_img.get("data-src") or tag_img.get("src") or "")
            tags.append({"name": normalize_tag(label), "image_url": to_full_size(thumb) if thumb else None})
        if painted:
            tags.insert(0, {"name": "Painted", "image_url": None})
        rarities[normalize_name(name)] = {
            "name": name,
            "painted": painted,
            "rarity": primary_rarity([str(tag["name"]) for tag in tags if tag.get("name")], painted),
            "tags": tags,
        }
    return rarities


def parse_module_skins(wikitext: str) -> dict[str, dict[str, dict[str, Any]]]:
    catalog: dict[str, dict[str, dict[str, Any]]] = {}
    for match in re.finditer(r'\["([^"]+)"\]\s*=\s*', wikitext):
        hero = match.group(1)
        table = extract_lua_table(wikitext, match.end())
        if not table:
            continue
        if '["skins"]' not in table and '["painted-skins"]' not in table:
            continue
        skins_table = named_lua_table(table, "skins") or table
        painted_table = named_lua_table(table, "painted-skins") or ""
        hero_key = normalize_name(hero)
        catalog.setdefault(hero_key, {})
        for source, painted in ((skins_table, False), (painted_table, True)):
            for entry_match in re.finditer(r'\["\d+(?:-color\d+)?"\]\s*=\s*', source):
                entry = extract_lua_table(source, entry_match.end())
                if not entry or '["name"]' not in entry:
                    continue
                fields = parse_lua_fields(entry)
                name = (fields.get("name") or "").strip()
                if not name:
                    continue
                tag = normalize_tag(fields.get("tag") or "")
                tags: list[dict[str, str | None]] = []
                if tag:
                    tags.append({"name": tag, "image_url": None})
                if painted:
                    tags.append({"name": "Painted", "image_url": None})
                catalog[hero_key][normalize_name(name)] = {
                    "name": name,
                    "painted": painted,
                    "rarity": primary_rarity([str(item["name"]) for item in tags if item.get("name")], painted),
                    "tags": tags,
                }
    return catalog


class Downloader:
    def __init__(self, args: argparse.Namespace) -> None:
        self.args = args
        self.env = load_env(ROOT / ".env")
        self.wiki = WikiClient(args.delay)
        self.public = ROOT / "storage" / "app" / "public"
        self.sync_dir = ROOT / "storage" / "app" / "mlbb-skin-sync"
        self.progress_path = self.sync_dir / "progress.json"
        self.catalog_path = self.sync_dir / "catalog.json"
        self.progress = self.load_progress()
        self.catalog = self.load_catalog()
        self.roles_by_hero = self.load_roles()
        self._db = None

    def load_progress(self) -> dict[str, Any]:
        if self.progress_path.is_file():
            try:
                return json.loads(self.progress_path.read_text(encoding="utf-8"))
            except json.JSONDecodeError:
                pass
        return {"badges": {}, "heroes": {}, "updated_at": None}

    def load_catalog(self) -> dict[str, Any]:
        if self.catalog_path.is_file():
            try:
                data = json.loads(self.catalog_path.read_text(encoding="utf-8"))
                if isinstance(data, dict):
                    data.setdefault("tags", [])
                    data.setdefault("skins", [])
                    return data
            except json.JSONDecodeError:
                pass
        return {"tags": [], "skins": []}

    def save_progress(self) -> None:
        self.progress["updated_at"] = utc_now()
        atomic_write_json(self.progress_path, self.progress)

    def save_catalog(self) -> None:
        self.catalog["updated_at"] = utc_now()
        atomic_write_json(self.catalog_path, self.catalog)

    def load_roles(self) -> dict[str, str]:
        roles: dict[str, str] = {}
        json_path = ROOT / "public" / "storage" / "mlbbskins.json"
        alt = ROOT / "storage" / "app" / "public" / "mlbbskins.json"
        for path in (json_path, alt):
            if not path.is_file():
                continue
            try:
                payload = json.loads(path.read_text(encoding="utf-8"))
            except json.JSONDecodeError:
                continue
            for category in payload.get("categories") or []:
                role = str(category.get("name") or "Unknown")
                for hero in category.get("heroes") or []:
                    name = str(hero.get("hero") or "").strip()
                    if name:
                        roles[normalize_name(name)] = role
        return roles

    def db(self):
        if self._db is not None:
            return self._db
        try:
            import pymysql
        except ImportError:
            return None
        database = self.env.get("DB_DATABASE")
        if not database:
            return None
        try:
            self._db = pymysql.connect(
                host=self.env.get("DB_HOST") or "127.0.0.1",
                user=self.env.get("DB_USERNAME") or "root",
                password=self.env.get("DB_PASSWORD") or "",
                database=database,
                port=int(self.env.get("DB_PORT") or 3306),
                charset="utf8mb4",
                autocommit=True,
            )
            with self._db.cursor() as cursor:
                cursor.execute("SHOW COLUMNS FROM mlbb_skins LIKE 'image_path'")
                if cursor.fetchone() is None:
                    print("Run this first: php artisan migrate --path=database/migrations/2026_08_18_180000_add_images_to_mlbb_skins_and_create_skin_tags_table.php")
                    self._db.close()
                    self._db = None
                    return None
            return self._db
        except Exception as exc:
            print(f"MySQL not connected ({exc}). Files will still be saved.")
            self._db = None
            return None

    def upsert_tag(self, name: str, image_path: str | None, source_url: str | None) -> None:
        tags = [item for item in self.catalog["tags"] if slug(item.get("name", "")) != slug(name)]
        tags.append({"name": name, "image_path": image_path, "source_url": source_url})
        self.catalog["tags"] = tags
        conn = self.db()
        if not conn:
            return
        with conn.cursor() as cursor:
            cursor.execute(
                """
                INSERT INTO mlbb_skin_tags (name, slug, image_path, source_url, created_at, updated_at)
                VALUES (%s, %s, %s, %s, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    image_path = VALUES(image_path),
                    source_url = VALUES(source_url),
                    updated_at = NOW()
                """,
                (name, slug(name), image_path, source_url),
            )

    def upsert_skin(self, skin: dict[str, Any]) -> None:
        key = (slug(skin["hero"]), skin["skin_slug"])
        skins = [
            item
            for item in self.catalog["skins"]
            if (slug(item.get("hero", "")), item.get("skin_slug")) != key
        ]
        skins.append(skin)
        self.catalog["skins"] = skins
        conn = self.db()
        if not conn:
            return
        with conn.cursor() as cursor:
            cursor.execute(
                """
                INSERT INTO mlbb_skins (
                    role, hero, skin, role_slug, hero_slug, skin_slug, sort_order, rarity, painted,
                    image_path, thumbnail_path, source_image_url, tags, synced_at, created_at, updated_at
                ) VALUES (
                    %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE
                    role = VALUES(role),
                    hero = VALUES(hero),
                    skin = VALUES(skin),
                    role_slug = VALUES(role_slug),
                    sort_order = VALUES(sort_order),
                    rarity = VALUES(rarity),
                    painted = VALUES(painted),
                    image_path = VALUES(image_path),
                    thumbnail_path = VALUES(thumbnail_path),
                    source_image_url = VALUES(source_image_url),
                    tags = VALUES(tags),
                    synced_at = NOW(),
                    updated_at = NOW()
                """,
                (
                    skin["role"],
                    skin["hero"],
                    skin["name"],
                    slug(skin["role"]),
                    slug(skin["hero"]),
                    skin["skin_slug"],
                    skin["sort_order"],
                    skin["rarity"],
                    1 if skin["painted"] else 0,
                    skin["image_path"],
                    skin["thumbnail_path"],
                    skin["source_image_url"],
                    json.dumps(skin["tags"], ensure_ascii=False),
                ),
            )

    def file_url(self, file_name: str) -> str | None:
        data = self.wiki.request(
            {
                "action": "query",
                "titles": f"File:{file_name}",
                "prop": "imageinfo",
                "iiprop": "url",
            }
        )
        pages = ((data.get("query") or {}).get("pages")) or []
        if isinstance(pages, dict):
            pages = list(pages.values())
        for page in pages:
            infos = page.get("imageinfo") or []
            if infos and infos[0].get("url"):
                return to_full_size(str(infos[0]["url"]))
        return None

    def fetch_section_html(self, page: str, index: int) -> str:
        data = self.wiki.request(
            {
                "action": "parse",
                "page": page,
                "prop": "text",
                "section": str(index),
                "disablelimitreport": 1,
            }
        )
        text = ((data.get("parse") or {}).get("text")) or ""
        if isinstance(text, dict):
            return str(text.get("*") or "")
        return str(text)

    def fetch_page_html(self, page: str) -> str:
        data = self.wiki.request(
            {
                "action": "parse",
                "page": page,
                "prop": "text",
                "disablelimitreport": 1,
            }
        )
        text = ((data.get("parse") or {}).get("text")) or ""
        if isinstance(text, dict):
            return str(text.get("*") or "")
        return str(text)

    def find_section(self, page: str, names: list[str]) -> int | None:
        data = self.wiki.request({"action": "parse", "page": page, "prop": "sections"})
        wanted = {name.lower() for name in names}
        for section in (data.get("parse") or {}).get("sections") or []:
            line = str(section.get("line") or "").lower()
            anchor = str(section.get("anchor") or "").lower().replace("_", " ")
            if line in wanted or anchor in wanted:
                try:
                    return int(section.get("index"))
                except (TypeError, ValueError):
                    continue
        return None

    def download_badges(self, extra: list[str] | None = None) -> None:
        names = []
        for name in KNOWN_BADGES + (extra or []):
            clean = normalize_tag(name)
            if clean and clean not in names:
                names.append(clean)
        print(f"Downloading {len(names)} rarity badges first...")
        for name in names:
            state = (self.progress.get("badges") or {}).get(name)
            dest = self.public / "mlbb_skin_tags" / f"{slug(name)}.png"
            if state == "ok" and dest.is_file() and not self.args.force:
                print(f"  [skip] {name}")
                continue
            print(f"  badge: {name}")
            url = self.file_url(f"{name} Skin Tag.png")
            saved = bool(url) and self.wiki.download(url, dest, self.args.force)
            self.progress.setdefault("badges", {})[name] = "ok" if saved else "missing"
            self.upsert_tag(name, f"mlbb_skin_tags/{slug(name)}.png" if saved else None, url)
            self.save_progress()
            self.save_catalog()
        print("Badges done.")

    def module_catalog(self) -> dict[str, dict[str, dict[str, Any]]]:
        cached = self.sync_dir / "module-skin-data.json"
        if cached.is_file() and not self.args.force:
            try:
                return json.loads(cached.read_text(encoding="utf-8"))
            except json.JSONDecodeError:
                pass
        print("Fetching Module:Skin/data (all hero skin tags)...")
        data = self.wiki.request({"action": "parse", "page": "Module:Skin/data", "prop": "wikitext"})
        wikitext = ((data.get("parse") or {}).get("wikitext")) or ""
        if isinstance(wikitext, dict):
            wikitext = str(wikitext.get("*") or "")
        catalog = parse_module_skins(str(wikitext))
        atomic_write_json(cached, catalog)
        extras = []
        for hero_skins in catalog.values():
            for meta in hero_skins.values():
                for tag in meta.get("tags") or []:
                    if tag.get("name") and tag["name"] != "Painted":
                        extras.append(str(tag["name"]))
        return catalog

    def hero_names(self, module: dict[str, dict[str, dict[str, Any]]]) -> list[str]:
        names: list[str] = []
        if self.args.heroes:
            names = [item.strip() for item in self.args.heroes.split(",") if item.strip()]
            return names
        for path in (
            ROOT / "public" / "storage" / "mlbbskins.json",
            ROOT / "storage" / "app" / "public" / "mlbbskins.json",
        ):
            if not path.is_file():
                continue
            payload = json.loads(path.read_text(encoding="utf-8"))
            for category in payload.get("categories") or []:
                for hero in category.get("heroes") or []:
                    name = str(hero.get("hero") or "").strip()
                    if name and name not in names:
                        names.append(name)
        conn = self.db()
        if conn:
            with conn.cursor() as cursor:
                cursor.execute("SELECT DISTINCT hero FROM mlbb_skins ORDER BY hero")
                for (hero,) in cursor.fetchall():
                    if hero and hero not in names:
                        names.append(hero)
        if not names:
            try:
                response = self.wiki.session.get(MAPI_HEROES, timeout=30)
                response.raise_for_status()
                for hero in (response.json().get("data") or []):
                    name = str(hero.get("name") or "").strip()
                    if name and name not in names:
                        names.append(name)
            except Exception as exc:
                print(f"Could not load official hero list: {exc}")
        for hero in module:
            pretty = hero.title()
            if pretty not in names and hero not in {normalize_name(item) for item in names}:
                names.append(pretty)
        return names

    def match_meta(self, hero: str, skin_name: str, painted: bool, module: dict[str, dict[str, dict[str, Any]]], boxes: dict[str, dict[str, Any]]) -> dict[str, Any]:
        key = normalize_name(skin_name)
        hero_key = normalize_name(HERO_ALIASES.get(hero, hero))
        match = boxes.get(key) or (module.get(hero_key) or {}).get(key) or {}
        tags = list(match.get("tags") or [])
        is_painted = painted or bool(match.get("painted"))
        if is_painted and not any(str(tag.get("name")) == "Painted" for tag in tags):
            tags.insert(0, {"name": "Painted", "image_url": None})
        localized = []
        for tag in tags:
            name = str(tag.get("name") or "")
            if not name:
                continue
            local = f"mlbb_skin_tags/{slug(name)}.png" if name != "Painted" else None
            image_url = None
            if local and (self.public / local).is_file():
                image_url = local
            localized.append({"name": name, "image_url": image_url})
        return {
            "painted": is_painted,
            "rarity": primary_rarity([item["name"] for item in localized], is_painted),
            "tags": localized,
        }

    def process_hero(self, hero: str, module: dict[str, dict[str, dict[str, Any]]]) -> None:
        wiki_page = HERO_ALIASES.get(hero, hero)
        state = (self.progress.get("heroes") or {}).get(hero) or {}
        if state.get("status") == "done" and not self.args.force:
            print(f"[skip] {hero}")
            return
        print(f"[{hero}] fetching wiki skins...")
        self.progress.setdefault("heroes", {})[hero] = {"status": "in_progress", "at": utc_now()}
        self.save_progress()

        skins: list[dict[str, str]] = []
        for section_names, painted in (
            (["splash art", "splash arts", "splash_art", "splash_arts"], False),
            (["painted skins", "painted skin", "painted_skins", "painted_skin"], True),
        ):
            index = self.find_section(wiki_page, section_names)
            if index is None:
                continue
            html = self.fetch_section_html(wiki_page, index)
            for item in parse_gallery(html):
                item["painted_section"] = "1" if painted else "0"
                skins.append(item)

        boxes: dict[str, dict[str, Any]] = {}
        try:
            boxes = parse_skin_boxes(self.fetch_page_html(f"{wiki_page}/Cosmetics"))
        except Exception as exc:
            print(f"  cosmetics page skipped: {exc}")

        unique: list[dict[str, str]] = []
        seen: set[str] = set()
        for item in skins:
            key = normalize_name(item["name"]) + ("|painted" if item.get("painted_section") == "1" else "")
            if key in seen:
                continue
            seen.add(key)
            unique.append(item)

        saved = 0
        role = self.roles_by_hero.get(normalize_name(hero), "Unknown")
        for order, item in enumerate(unique):
            painted = item.get("painted_section") == "1"
            meta = self.match_meta(hero, item["name"], painted, module, boxes)
            skin_slug = slug(item["name"])
            if meta["painted"]:
                skin_slug = f"{skin_slug}-painted"
            rel = f"mlbb_skins/{slug(hero)}/{skin_slug}.png"
            dest = self.public / rel
            ok = self.wiki.download(item["image_url"], dest, self.args.force)
            if not ok:
                print(f"  failed image: {item['name']}")
                continue
            self.upsert_skin(
                {
                    "role": role,
                    "hero": hero,
                    "name": item["name"],
                    "skin_slug": skin_slug,
                    "sort_order": order,
                    "rarity": meta["rarity"],
                    "painted": meta["painted"],
                    "image_path": rel,
                    "thumbnail_path": rel,
                    "source_image_url": item["image_url"],
                    "tags": meta["tags"],
                }
            )
            saved += 1

        self.progress["heroes"][hero] = {
            "status": "done" if saved else "empty",
            "skins": saved,
            "at": utc_now(),
        }
        self.save_progress()
        self.save_catalog()
        print(f"  saved {saved} skins")

    def run(self) -> int:
        self.sync_dir.mkdir(parents=True, exist_ok=True)
        (self.public / "mlbb_skins").mkdir(parents=True, exist_ok=True)
        (self.public / "mlbb_skin_tags").mkdir(parents=True, exist_ok=True)

        module = self.module_catalog()
        extra_tags = []
        for hero_skins in module.values():
            for meta in hero_skins.values():
                for tag in meta.get("tags") or []:
                    extra_tags.append(str(tag.get("name") or ""))
        self.download_badges(extra_tags)
        if self.args.badges_only:
            print("Badges-only run finished.")
            print(f"Catalog: {self.catalog_path}")
            return 0

        heroes = self.hero_names(module)
        print(f"Heroes to process: {len(heroes)}")
        for index, hero in enumerate(heroes, start=1):
            print(f"\n({index}/{len(heroes)}) {hero}")
            try:
                self.process_hero(hero, module)
            except KeyboardInterrupt:
                print("\nStopped. Progress is saved — run the same command to continue.")
                self.save_progress()
                self.save_catalog()
                return 130
            except Exception as exc:
                print(f"  failed: {exc}")
                self.progress.setdefault("heroes", {})[hero] = {
                    "status": "error",
                    "error": str(exc),
                    "at": utc_now(),
                }
                self.save_progress()
                self.save_catalog()
                time.sleep(self.args.delay * 2)

        print("\nDone.")
        print(f"Images: {self.public / 'mlbb_skins'}")
        print(f"Badges: {self.public / 'mlbb_skin_tags'}")
        print(f"Catalog: {self.catalog_path}")
        if self.db() is None:
            print("Database was not updated. After this finishes run:")
            print("  php artisan mlbb:import-skin-images")
        return 0


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Download MLBB skins and rarity badges locally.")
    parser.add_argument("--delay", type=float, default=1.6, help="Seconds between wiki API calls (default 1.6)")
    parser.add_argument("--heroes", help="Comma-separated hero names to download only those")
    parser.add_argument("--badges-only", action="store_true", help="Download rarity badges only")
    parser.add_argument("--force", action="store_true", help="Re-download files even if they already exist")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    os.chdir(ROOT)
    try:
        return Downloader(args).run()
    except KeyboardInterrupt:
        print("\nStopped. Progress is saved — run the same command to continue.")
        return 130


if __name__ == "__main__":
    sys.exit(main())
