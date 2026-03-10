let tokenGetter = null;
let logCallback = null;

export function setTokenGetter(getter) {
  tokenGetter = getter;
}

export function setLogCallback(cb) {
  logCallback = cb;
}

function getBaseUrl() {
  const fromEnv = import.meta.env.VITE_API_BASE_URL;
  return (fromEnv || 'http://127.0.0.1:8000/api').replace(/\/+$/, '');
}

function buildUrl(path) {
  const base = getBaseUrl();
  const rel = path.replace(/^\/+/, '');
  return `${base}/${rel}`;
}

export async function apiFetch(path, { method = 'GET', body, auth = false } = {}) {
  const url = buildUrl(path);

  const headers = {
    Accept: 'application/json',
  };

  let bodySent = null;
  if (body !== undefined && body !== null) {
    headers['Content-Type'] = 'application/json';
    bodySent = body;
  }

  if (auth && tokenGetter) {
    const token = tokenGetter();
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
  }

  const startedAt = performance.now();
  let status = 0;
  let json = null;
  let text = null;
  let error = null;

  try {
    const response = await fetch(url, {
      method,
      headers,
      body: bodySent ? JSON.stringify(bodySent) : undefined,
    });

    status = response.status;

    if (status === 204) {
      const entry = {
        ok: response.ok,
        status,
        durationMs: performance.now() - startedAt,
        json: null,
        text: null,
        error: null,
        url,
        method,
        headersSent: headers,
        bodySent,
      };
      if (logCallback) {
        logCallback(entry);
      }
      return entry;
    }

    text = await response.text();
    if (text) {
      try {
        json = JSON.parse(text);
      } catch {
        json = null;
      }
    }

    const entry = {
      ok: response.ok,
      status,
      durationMs: performance.now() - startedAt,
      json,
      text,
      error: null,
      url,
      method,
      headersSent: headers,
      bodySent,
    };

    if (logCallback) {
      logCallback(entry);
    }

    return entry;
  } catch (e) {
    error = e instanceof Error ? e.message : String(e);
    const entry = {
      ok: false,
      status,
      durationMs: performance.now() - startedAt,
      json,
      text,
      error,
      url,
      method,
      headersSent: headers,
      bodySent,
    };
    if (logCallback) {
      logCallback(entry);
    }
    return entry;
  }
}

