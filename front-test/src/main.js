import {
  apiFetch,
  loadInitialState,
  persistBaseUrl,
  persistToken,
} from './api.js';

const MAX_LOG_ENTRIES = 50;

const state = {
  baseUrl: '',
  token: '',
  rememberToken: true,
  logs: [],
};

function createElement(tag, props = {}, ...children) {
  const el = document.createElement(tag);
  Object.entries(props).forEach(([key, value]) => {
    if (key === 'className') {
      el.className = value;
    } else if (key === 'onClick') {
      el.addEventListener('click', value);
    } else if (key.startsWith('on') && typeof value === 'function') {
      el.addEventListener(key.slice(2).toLowerCase(), value);
    } else if (key === 'text') {
      el.textContent = value;
    } else {
      el.setAttribute(key, value);
    }
  });
  for (const child of children) {
    if (child === null || child === undefined) continue;
    if (typeof child === 'string') {
      el.appendChild(document.createTextNode(child));
    } else {
      el.appendChild(child);
    }
  }
  return el;
}

function addLog(entry) {
  state.logs.unshift(entry);
  if (state.logs.length > MAX_LOG_ENTRIES) {
    state.logs.length = MAX_LOG_ENTRIES;
  }
  renderLogs();
  renderLastResponse(entry);
}

function renderLogs() {
  const container = document.getElementById('logs');
  if (!container) return;
  container.innerHTML = '';
  if (state.logs.length === 0) {
    container.textContent = 'Aucune requête pour le moment.';
    return;
  }

  const list = createElement('ul');
  state.logs.forEach((log) => {
    const item = createElement(
      'li',
      {},
      `[${log.timestamp}] ${log.method} ${log.url} -> ${log.status} (${log.durationMs.toFixed(
        1,
      )} ms)`,
    );
    list.appendChild(item);
  });
  container.appendChild(list);
}

function renderLastResponse(entry) {
  const container = document.getElementById('last-response');
  if (!container) return;
  container.innerHTML = '';

  if (!entry) {
    container.textContent = 'Aucune réponse encore.';
    return;
  }

  const meta = {
    ok: entry.ok,
    status: entry.status,
    durationMs: Number.isFinite(entry.durationMs)
      ? entry.durationMs.toFixed(1)
      : null,
    url: entry.url,
    method: entry.method,
    error: entry.error || null,
  };

  const headers = entry.headersSent || {};
  const bodySent = entry.bodySent ?? null;

  const metaPre = createElement(
    'pre',
    {},
    JSON.stringify(
      {
        request: {
          url: meta.url,
          method: meta.method,
          headers,
          body: bodySent,
        },
        response: {
          ok: meta.ok,
          status: meta.status,
          durationMs: meta.durationMs,
          error: meta.error,
        },
      },
      null,
      2,
    ),
  );

  const jsonPre = createElement(
    'pre',
    {},
    entry.json ? JSON.stringify(entry.json, null, 2) : 'JSON: null',
  );

  const textPre = createElement(
    'pre',
    {},
    entry.text !== null && entry.text !== undefined
      ? entry.text
      : 'Texte brut: null',
  );

  container.appendChild(metaPre);
  container.appendChild(jsonPre);
  container.appendChild(textPre);
}

async function runRequest({ path, method, body, useAuth }) {
  const result = await apiFetch({
    baseUrl: state.baseUrl,
    token: state.token,
    path,
    method,
    body,
    useAuth,
  });

  addLog({
    ...result,
    timestamp: new Date().toISOString(),
  });

  return result;
}

function buildBaseUrlSection() {
  const input = createElement('input', {
    type: 'text',
    value: state.baseUrl,
    size: '50',
  });

  input.addEventListener('input', () => {
    state.baseUrl = input.value.trim();
  });

  const saveBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () => {
        persistBaseUrl(state.baseUrl);
        alert('Base URL sauvegardée');
      },
    },
    'Sauvegarder base URL',
  );

  const wrapper = createElement(
    'section',
    {},
    createElement('h2', {}, 'Configuration API'),
    createElement(
      'div',
      {},
      'Base URL API: ',
      input,
      ' ',
      saveBtn,
      createElement(
        'span',
        {},
        ' (ex: http://127.0.0.1:8000/api ou http://localhost:8000/api)',
      ),
    ),
  );
  return wrapper;
}

function buildAuthSection() {
  const tokenSpan = createElement('span', {}, state.token || '(aucun token)');
  const rememberCheckbox = createElement('input', {
    type: 'checkbox',
  });
  rememberCheckbox.checked = state.rememberToken;

  rememberCheckbox.addEventListener('change', () => {
    state.rememberToken = rememberCheckbox.checked;
    if (!state.rememberToken) {
      persistToken('');
    } else if (state.token) {
      persistToken(state.token);
    }
  });

  function updateTokenDisplay(newToken) {
    state.token = newToken || '';
    tokenSpan.textContent = state.token || '(aucun token)';
    if (state.rememberToken) {
      persistToken(state.token);
    }
  }

  // Register form
  const regName = createElement('input', { type: 'text', placeholder: 'name' });
  const regEmail = createElement('input', {
    type: 'email',
    placeholder: 'email',
  });
  const regPassword = createElement('input', {
    type: 'password',
    placeholder: 'password',
  });

  const regBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: async () => {
        const body = {
          name: regName.value,
          email: regEmail.value,
          password: regPassword.value,
          avatar_url: null,
          nationality: null,
          user_type: 'user',
        };
        const res = await runRequest({
          path: '/register',
          method: 'POST',
          body,
          useAuth: false,
        });
        if (res.ok && res.json && res.json.access_token) {
          updateTokenDisplay(res.json.access_token);
        }
      },
    },
    'Register',
  );

  // Login form
  const loginEmail = createElement('input', {
    type: 'email',
    placeholder: 'email',
  });
  const loginPassword = createElement('input', {
    type: 'password',
    placeholder: 'password',
  });

  const loginBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: async () => {
        const body = {
          email: loginEmail.value,
          password: loginPassword.value,
        };
        const res = await runRequest({
          path: '/login',
          method: 'POST',
          body,
          useAuth: false,
        });
        if (res.ok && res.json && res.json.access_token) {
          updateTokenDisplay(res.json.access_token);
        }
      },
    },
    'Login',
  );

  const meBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: '/user',
          method: 'GET',
          body: null,
          useAuth: true,
        }),
    },
    'GET /user',
  );

  const logoutBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: async () => {
        await runRequest({
          path: '/logout',
          method: 'POST',
          body: null,
          useAuth: true,
        });
        updateTokenDisplay('');
      },
    },
    'POST /logout',
  );

  const clearTokenBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () => {
        updateTokenDisplay('');
      },
    },
    'Clear token',
  );

  const copyBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: async () => {
        if (!state.token) return;
        try {
          await navigator.clipboard.writeText(state.token);
          alert('Token copié dans le presse-papiers');
        } catch {
          alert('Impossible de copier le token (permissions navigateur).');
        }
      },
    },
    'Copy token',
  );

  const section = createElement(
    'section',
    {},
    createElement('h2', {}, 'Auth'),
    createElement(
      'div',
      {},
      'Token courant: ',
      tokenSpan,
      ' ',
      copyBtn,
    ),
    createElement(
      'div',
      {},
      createElement('label', {}, rememberCheckbox, ' Remember token'),
    ),
    createElement('h3', {}, 'Register'),
    createElement(
      'div',
      {},
      regName,
      regEmail,
      regPassword,
      ' ',
      regBtn,
    ),
    createElement('h3', {}, 'Login'),
    createElement(
      'div',
      {},
      loginEmail,
      loginPassword,
      ' ',
      loginBtn,
    ),
    createElement('h3', {}, 'Actions Auth'),
    createElement('div', {}, meBtn, ' ', logoutBtn, ' ', clearTokenBtn),
  );

  return section;
}

function buildExplorationSection() {
  const listButtonsConfig = [
    { label: 'GET /users', path: '/users' },
    { label: 'GET /collections', path: '/collections' },
    { label: 'GET /categories', path: '/categories' },
    { label: 'GET /items', path: '/items' },
    { label: 'GET /criteria', path: '/criteria' },
    { label: 'GET /item-criteria', path: '/item-criteria' },
  ];

  const listButtons = listButtonsConfig.map((cfg) =>
    createElement(
      'button',
      {
        type: 'button',
        onClick: () =>
          runRequest({
            path: cfg.path,
            method: 'GET',
            body: null,
            useAuth: false,
          }),
      },
      cfg.label,
    ),
  );

  const idUsers = createElement('input', {
    type: 'number',
    placeholder: 'user id',
  });
  const idCollections = createElement('input', {
    type: 'number',
    placeholder: 'collection id',
  });
  const idCategories = createElement('input', {
    type: 'number',
    placeholder: 'category id',
  });
  const idItems = createElement('input', {
    type: 'number',
    placeholder: 'item id',
  });
  const idCriteria = createElement('input', {
    type: 'number',
    placeholder: 'criteria id',
  });
  const idItemForCriteria = createElement('input', {
    type: 'number',
    placeholder: 'item id',
  });

  const section = createElement(
    'section',
    {},
    createElement('h2', {}, 'Exploration (GET publics)'),
    createElement('div', {}, ...listButtons),
    createElement('h3', {}, 'GET show par id'),
    createElement(
      'div',
      {},
      idUsers,
      createElement(
        'button',
        {
          type: 'button',
          onClick: () =>
            runRequest({
              path: `/users/${idUsers.value}`,
              method: 'GET',
              body: null,
              useAuth: false,
            }),
        },
        'GET /users/{id}',
      ),
    ),
    createElement(
      'div',
      {},
      idCollections,
      createElement(
        'button',
        {
          type: 'button',
          onClick: () =>
            runRequest({
              path: `/collections/${idCollections.value}`,
              method: 'GET',
              body: null,
              useAuth: false,
            }),
        },
        'GET /collections/{id}',
      ),
    ),
    createElement(
      'div',
      {},
      idCategories,
      createElement(
        'button',
        {
          type: 'button',
          onClick: () =>
            runRequest({
              path: `/categories/${idCategories.value}`,
              method: 'GET',
              body: null,
              useAuth: false,
            }),
        },
        'GET /categories/{id}',
      ),
    ),
    createElement(
      'div',
      {},
      idItems,
      createElement(
        'button',
        {
          type: 'button',
          onClick: () =>
            runRequest({
              path: `/items/${idItems.value}`,
              method: 'GET',
              body: null,
              useAuth: false,
            }),
        },
        'GET /items/{id}',
      ),
    ),
    createElement(
      'div',
      {},
      idCriteria,
      createElement(
        'button',
        {
          type: 'button',
          onClick: () =>
            runRequest({
              path: `/criteria/${idCriteria.value}`,
              method: 'GET',
              body: null,
              useAuth: false,
            }),
        },
        'GET /criteria/{id}',
      ),
    ),
    createElement('h3', {}, 'GET /items/{item_id}/criteria'),
    createElement(
      'div',
      {},
      idItemForCriteria,
      createElement(
        'button',
        {
          type: 'button',
          onClick: () =>
            runRequest({
              path: `/items/${idItemForCriteria.value}/criteria`,
              method: 'GET',
              body: null,
              useAuth: false,
            }),
        },
        'GET /items/{item_id}/criteria',
      ),
    ),
  );

  return section;
}

function buildWriteSection() {
  // Collections create
  const collTitle = createElement('input', {
    type: 'text',
    placeholder: 'title',
  });
  const collDesc = createElement('input', {
    type: 'text',
    placeholder: 'description',
  });
  const collCreateBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: '/collections',
          method: 'POST',
          body: { title: collTitle.value, description: collDesc.value || null },
          useAuth: true,
        }),
    },
    'POST /collections',
  );

  // Categories create
  const catTitle = createElement('input', {
    type: 'text',
    placeholder: 'title',
  });
  const catCreateBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: '/categories',
          method: 'POST',
          body: { title: catTitle.value },
          useAuth: true,
        }),
    },
    'POST /categories',
  );

  // Criteria create
  const critName = createElement('input', {
    type: 'text',
    placeholder: 'name',
  });
  const critCreateBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: '/criteria',
          method: 'POST',
          body: { name: critName.value },
          useAuth: true,
        }),
    },
    'POST /criteria',
  );

  // Item create
  const itemTitle = createElement('input', {
    type: 'text',
    placeholder: 'title',
  });
  const itemDesc = createElement('input', {
    type: 'text',
    placeholder: 'description',
  });
  const itemImage = createElement('input', {
    type: 'text',
    placeholder: 'image_url',
  });
  const itemStatus = createElement('input', {
    type: 'checkbox',
  });
  const cat1Id = createElement('input', {
    type: 'number',
    placeholder: 'category1_id (obligatoire)',
  });
  const cat2Id = createElement('input', {
    type: 'number',
    placeholder: 'category2_id (optionnel)',
  });

  const itemCreateBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: '/items',
          method: 'POST',
          body: {
            title: itemTitle.value,
            description: itemDesc.value || null,
            image_url: itemImage.value || null,
            status: !!itemStatus.checked,
            category1_id: cat1Id.value ? Number(cat1Id.value) : null,
            category2_id: cat2Id.value ? Number(cat2Id.value) : null,
          },
          useAuth: true,
        }),
    },
    'POST /items',
  );

  // Scores create
  const scoreItemId = createElement('input', {
    type: 'number',
    placeholder: 'id_item',
  });
  const scoreCritId = createElement('input', {
    type: 'number',
    placeholder: 'id_criteria',
  });
  const scoreValue = createElement('input', {
    type: 'number',
    min: '0',
    max: '2',
    placeholder: 'value (0,1,2)',
  });
  const scoreCreateBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: '/item-criteria',
          method: 'POST',
          body: {
            id_item: Number(scoreItemId.value),
            id_criteria: Number(scoreCritId.value),
            value: Number(scoreValue.value),
          },
          useAuth: true,
        }),
    },
    'POST /item-criteria',
  );

  // Simple delete forms (id + bouton)
  const delCollectionId = createElement('input', {
    type: 'number',
    placeholder: 'collection id',
  });
  const delCollectionBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: `/collections/${delCollectionId.value}`,
          method: 'DELETE',
          body: null,
          useAuth: true,
        }),
    },
    'DELETE /collections/{id}',
  );

  const delItemId = createElement('input', {
    type: 'number',
    placeholder: 'item id',
  });
  const delItemBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: `/items/${delItemId.value}`,
          method: 'DELETE',
          body: null,
          useAuth: true,
        }),
    },
    'DELETE /items/{id}',
  );

  const delCategoryId = createElement('input', {
    type: 'number',
    placeholder: 'category id',
  });
  const delCategoryBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: `/categories/${delCategoryId.value}`,
          method: 'DELETE',
          body: null,
          useAuth: true,
        }),
    },
    'DELETE /categories/{id}',
  );

  const delCriteriaId = createElement('input', {
    type: 'number',
    placeholder: 'criteria id',
  });
  const delCriteriaBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: `/criteria/${delCriteriaId.value}`,
          method: 'DELETE',
          body: null,
          useAuth: true,
        }),
    },
    'DELETE /criteria/{id}',
  );

  const delScoreItemId = createElement('input', {
    type: 'number',
    placeholder: 'item_id',
  });
  const delScoreCritId = createElement('input', {
    type: 'number',
    placeholder: 'criterion_id',
  });
  const delScoreBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () =>
        runRequest({
          path: `/items/${delScoreItemId.value}/criteria/${delScoreCritId.value}`,
          method: 'DELETE',
          body: null,
          useAuth: true,
        }),
    },
    'DELETE /items/{item_id}/criteria/{crit_id}',
  );

  const section = createElement(
    'section',
    {},
    createElement('h2', {}, 'Écriture (protégé, nécessite token)'),
    createElement('h3', {}, 'Collections'),
    createElement(
      'div',
      {},
      collTitle,
      collDesc,
      ' ',
      collCreateBtn,
    ),
    createElement(
      'div',
      {},
      delCollectionId,
      ' ',
      delCollectionBtn,
    ),
    createElement('h3', {}, 'Categories'),
    createElement('div', {}, catTitle, ' ', catCreateBtn),
    createElement(
      'div',
      {},
      delCategoryId,
      ' ',
      delCategoryBtn,
    ),
    createElement('h3', {}, 'Criteria'),
    createElement('div', {}, critName, ' ', critCreateBtn),
    createElement(
      'div',
      {},
      delCriteriaId,
      ' ',
      delCriteriaBtn,
    ),
    createElement('h3', {}, 'Items'),
    createElement(
      'div',
      {},
      itemTitle,
      itemDesc,
      itemImage,
      createElement(
        'label',
        {},
        itemStatus,
        ' status (true=publié)',
      ),
      cat1Id,
      cat2Id,
      ' ',
      itemCreateBtn,
    ),
    createElement(
      'div',
      {},
      delItemId,
      ' ',
      delItemBtn,
    ),
    createElement('h3', {}, 'Scores (item_criteria)'),
    createElement(
      'div',
      {},
      scoreItemId,
      scoreCritId,
      scoreValue,
      ' ',
      scoreCreateBtn,
    ),
    createElement(
      'div',
      {},
      delScoreItemId,
      delScoreCritId,
      ' ',
      delScoreBtn,
    ),
  );

  return section;
}

function buildFreeRequestSection() {
  const methodSelect = createElement(
    'select',
    {},
    ...['GET', 'POST', 'PUT', 'DELETE'].map((m) =>
      createElement('option', { value: m, selected: m === 'GET' }, m),
    ),
  );
  const pathInput = createElement('input', {
    type: 'text',
    size: '40',
    placeholder: '/items ou items/1',
  });
  const bodyTextarea = createElement('textarea', {
    rows: '5',
    cols: '60',
    placeholder: '{ "title": "..." }',
  });
  const authCheckbox = createElement('input', { type: 'checkbox' });

  const sendBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: async () => {
        let body = null;
        const raw = bodyTextarea.value.trim();
        if (raw) {
          try {
            body = JSON.parse(raw);
          } catch (e) {
            alert('Body JSON invalide: ' + e.message);
            return;
          }
        }
        const method = methodSelect.value;
        const path = pathInput.value || '/';

        await runRequest({
          path,
          method,
          body,
          useAuth: authCheckbox.checked,
        });
      },
    },
    'Envoyer requête',
  );

  const section = createElement(
    'section',
    {},
    createElement('h2', {}, 'Requête libre'),
    createElement(
      'div',
      {},
      'Méthode: ',
      methodSelect,
      ' Chemin relatif: ',
      pathInput,
      ' ',
      createElement(
        'label',
        {},
        authCheckbox,
        ' Utiliser Authorization Bearer',
      ),
    ),
    createElement('div', {}, 'Body JSON (optionnel):'),
    bodyTextarea,
    createElement('div', {}, sendBtn),
  );

  return section;
}

function buildLogSection() {
  const clearBtn = createElement(
    'button',
    {
      type: 'button',
      onClick: () => {
        state.logs = [];
        renderLogs();
        renderLastResponse(null);
      },
    },
    'Clear log',
  );

  const section = createElement(
    'section',
    {},
    createElement('h2', {}, 'Log des requêtes'),
    clearBtn,
    createElement('div', { id: 'logs' }),
    createElement('h3', {}, 'Dernière réponse détaillée'),
    createElement('div', { id: 'last-response' }),
  );
  return section;
}

function buildScenarioSection() {
  const button = createElement(
    'button',
    {
      type: 'button',
      onClick: async () => {
        const random = Math.floor(Math.random() * 1000000);
        const email = `ecoal.${random}@example.com`;

        // Register
        let res = await runRequest({
          path: '/register',
          method: 'POST',
          body: {
            name: `User ${random}`,
            email,
            password: 'secret123',
            avatar_url: null,
            nationality: null,
            user_type: 'user',
          },
          useAuth: false,
        });
        if (!res.ok || !res.json?.access_token) return;
        state.token = res.json.access_token;
        if (state.rememberToken) persistToken(state.token);

        // Create collection
        res = await runRequest({
          path: '/collections',
          method: 'POST',
          body: {
            title: `Collection ${random}`,
            description: 'Scenario end-to-end',
          },
          useAuth: true,
        });
        if (!res.ok) return;

        // Ensure at least one category exists (create one quickly)
        res = await runRequest({
          path: '/categories',
          method: 'POST',
          body: { title: `Mécanisme ${random}` },
          useAuth: true,
        });
        if (!res.ok) return;
        const categoryId = res.json?.id;

        // Ensure at least one criterion exists
        res = await runRequest({
          path: '/criteria',
          method: 'POST',
          body: { name: `Durabilité ${random}` },
          useAuth: true,
        });
        if (!res.ok) return;
        const criterionId = res.json?.id_criteria;

        // Create item
        res = await runRequest({
          path: '/items',
          method: 'POST',
          body: {
            title: `Item ${random}`,
            description: 'Item créé par scenario end-to-end',
            image_url: null,
            status: true,
            category1_id: categoryId,
            category2_id: null,
          },
          useAuth: true,
        });
        if (!res.ok) return;
        const itemId = res.json?.id;

        // Assign score
        await runRequest({
          path: '/item-criteria',
          method: 'POST',
          body: {
            id_item: itemId,
            id_criteria: criterionId,
            value: 2,
          },
          useAuth: true,
        });

        // GET item criteria
        await runRequest({
          path: `/items/${itemId}/criteria`,
          method: 'GET',
          body: null,
          useAuth: false,
        });
      },
    },
    'Scenario end-to-end',
  );

  const section = createElement(
    'section',
    {},
    createElement('h2', {}, 'Scenario end-to-end (option bonus)'),
    createElement(
      'p',
      {},
      'Ce bouton va: register + login + create collection + create category + create criterion + create item + assign score + GET item criteria.',
    ),
    button,
  );
  return section;
}

function bootstrap() {
  const root = document.getElementById('app');
  const { baseUrl, token } = loadInitialState();
  state.baseUrl = baseUrl;
  state.token = token;

  const baseSection = buildBaseUrlSection();
  const authSection = buildAuthSection();
  const explorationSection = buildExplorationSection();
  const writeSection = buildWriteSection();
  const freeSection = buildFreeRequestSection();
  const scenarioSection = buildScenarioSection();
  const logSection = buildLogSection();

  root.appendChild(createElement('h1', {}, 'ECOAL API Tester'));
  root.appendChild(baseSection);
  root.appendChild(authSection);
  root.appendChild(explorationSection);
  root.appendChild(writeSection);
  root.appendChild(freeSection);
  root.appendChild(scenarioSection);
  root.appendChild(logSection);

  renderLogs();
  renderLastResponse(null);
}

bootstrap();

