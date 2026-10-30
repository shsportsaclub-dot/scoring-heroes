/**
 * Scoring Heroes shared identity/logo registry.
 * Uses the 1000 generated placeholder SVG assets in assets/logo-library/.
 * No hardcoded team colors or invented verified logos.
 * Browser and modern Node ESM compatible.
 */
const DEFAULT_MANIFEST = "/assets/logo-library/manifest.json";
export class SHLogoRegistry {
  constructor({ manifestUrl = DEFAULT_MANIFEST, assetBaseUrl = "/", fetchImpl = globalThis.fetch } = {}) {
    this.manifestUrl = manifestUrl;
    this.assetBaseUrl = assetBaseUrl;
    this.fetchImpl = fetchImpl;
    this.byCode = new Map();
    this.byName = new Map();
    this.ready = false;
  }
  async load() {
    if (!this.fetchImpl) throw new Error("fetch implementation required");
    const response = await this.fetchImpl(this.manifestUrl);
    if (!response.ok) throw new Error(`Logo manifest HTTP ${response.status}`);
    const rows = await response.json();
    if (!Array.isArray(rows)) throw new Error("Logo manifest must be an array");
    this.byCode.clear();
    this.byName.clear();
    for (const row of rows) {
      if (!row.team_code || !row.logo_path) continue;
      const record = Object.freeze({ ...row, logo_url: this.resolveUrl(row.logo_path) });
      this.byCode.set(String(row.team_code).toUpperCase(), record);
      this.byName.set(String(row.display_name || "").trim().toLowerCase(), record);
    }
    this.ready = true;
    return this;
  }
  resolveUrl(path) {
    const relative = String(path).replace(/^\/+/, "");
    return new URL(relative, new URL(this.assetBaseUrl, this.manifestUrl.startsWith("http") ? this.manifestUrl : "https://scoringheroes.invalid")).href.replace("https://scoringheroes.invalid", "");
  }
  get(codeOrName) {
    const key = String(codeOrName || "").trim();
    return this.byCode.get(key.toUpperCase()) || this.byName.get(key.toLowerCase()) || null;
  }
  search(query, limit = 30) {
    const q = String(query || "").trim().toLowerCase();
    return [...this.byCode.values()].filter(r => !q || r.team_code.toLowerCase().includes(q) || r.display_name.toLowerCase().includes(q)).slice(0, Math.max(1, Math.min(1000, limit)));
  }
  teamIdentity(codeOrName, overrides = {}) {
    const record = this.get(codeOrName);
    if (!record) return { team_code: String(codeOrName || ""), display_name: "NOT AVAILABLE", logo_url: null, verification_status: "MISSING", ...overrides };
    return { ...record, ...overrides };
  }
  scorebug(home, away) { return { home: this.teamIdentity(home), away: this.teamIdentity(away) }; }
  broadcastGraphic(event, battingTeam, bowlingTeam) {
    const team = this.teamIdentity(event === "WICKET" ? bowlingTeam : battingTeam);
    return { event, team, logo: team.logo_url, themeColor: team.primary_hue == null ? null : `hsl(${team.primary_hue} 70% 35%)`, isPlaceholder: team.verification_status === "GENERATED_PLACEHOLDER" };
  }
  registrationOptions(query = "") { return this.search(query).map(r => ({ value: r.team_code, label: r.display_name, logo: r.logo_url, verified: r.verification_status !== "GENERATED_PLACEHOLDER" })); }
}
