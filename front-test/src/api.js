const STORAGE_KEYS = {
  token: 'ecoal_token',
  baseUrl: 'ecoal_api_base_url',
};

export function loadInitialState() {
  const baseUrl =
    window.localStorage.getItem(STORAGE_KEYS.baseUrl) ||
    (import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api');
  const token = window.localStorage.getItem(STORAGE_KEYS.token) || '';
  return { baseUrl, token };
}

export function persistBaseUrl(baseUrl) {
  window.localStorage.setItem(STORAGE_KEYS.baseUrl, baseUrl);
}

export function persistToken(token) {
  if (token) {
    window.localStorage.setItem(STORAGE_KEYS.token, token);
  } else {
    window.localStorage.removeItem(STORAGE_KEYS.token);
  }
}

function buildUrl(baseUrl, path) {
  const root = baseUrl.replace(/\/+$/, '');
  const rel = path.replace(/^\/+/, '');
  return `${root}/${rel}`;
}

export async function apiFetch({
  baseUrl,
  token,
  path,
  method = 'GET',
  body = null,
  useAuth = false,
}) {
  const url = buildUrl(baseUrl, path);
  const headers = {
    Accept: 'application/json',
  };

  let bodySent = null;
  if (body !== null && body !== undefined) {
    headers['Content-Type'] = 'application/json';
    bodySent = body;
  }

  if (useAuth && token) {
    headers['Authorization'] = `Bearer ${token}`;
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
      // No content
      return {
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
    }

    text = await response.text();
    if (text) {
      try {
        json = JSON.parse(text);
      } catch {
        json = null;
      }
    }

    return {
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
  } catch (e) {
    error = e instanceof Error ? e.message : String(e);
    return {
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
  }
}

