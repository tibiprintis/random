CREATE TABLE IF NOT EXISTS jobs (
  id TEXT PRIMARY KEY,
  status TEXT NOT NULL,
  cache_key TEXT NOT NULL,
  file_name TEXT,
  stl_format TEXT,
  params_json TEXT NOT NULL,
  triangles INTEGER,
  size_bytes INTEGER,
  created_at TEXT NOT NULL,
  updated_at TEXT NOT NULL,
  error_json TEXT
);
CREATE UNIQUE INDEX IF NOT EXISTS idx_jobs_cache ON jobs(cache_key);
