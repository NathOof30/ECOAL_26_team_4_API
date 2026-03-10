# ECOAL Frontend - Issues Analysis & Improvement Plan

**Document Purpose:** Analysis of current issues blocking proper functionality and strategic planning for API and Frontend improvements.

---

## 1. CRITICAL ISSUES OVERVIEW

### 1.1 Category Display (HIGH PRIORITY)
**Current Behavior:** Items display categories as generated IDs instead of actual names.
- Example: "Mechanism #1" and "Period #2" instead of actual category names
- This is non-user-friendly and breaks the core experience

**Root Cause:** 
- Mapper functions (`mechanismLabel()` and `periodLabel()` in `src/domain/items/items.mapper.js`) generate fallback ID-based labels when category names are unavailable
- API may be returning either:
  - Category objects `{id: number, title: string}` without title field populated
  - Missing category relationships entirely
  - Field name inconsistencies (e.g., `name` vs `title` vs `category_name`)

**Solution:**
1. **Frontend (Quick Fix):**
   - Modify mapper to explicitly request category data when available
   - Add debugging to log what API returns for categories
   - Implement graceful fallback that shows "[Category Name Missing]" instead of "#ID"

2. **Backend (Proper Fix):**
   - API should return full category objects with names in item responses
   - Consider endpoint enhancement: `/api/items/{id}` should include nested category names
   - Alternative: Create dedicated category endpoints for bulk loading

**Implementation Plan:**
```
Step 1: Log API responses to understand structure
Step 2: Verify category data is being sent from backend
Step 3: If yes → Fix mapper to extract names correctly
Step 4: If no → Request API enhancement to include category names
```

---

### 1.2 Scores Not Displaying (HIGH PRIORITY)
**Current Behavior:** Item scores are not visible on any screen.

**Root Cause:**
- Scores may not be loaded when items are displayed
- Score data might be in separate endpoint (`/api/lighter/{id}/scores`)
- UI components not rendering score data even if available
- Score data structure may not be properly mapped

**Solution:**
1. **Frontend:**
   - Fetch scores alongside items (either via nested endpoint or separate call)
   - Add score display component to item cards
   - Implement score visualization (e.g., bar chart, star rating, numeric labels)

2. **Backend (if needed):**
   - Ensure scores are accessible via item detail endpoint
   - Create optimized endpoint: `/api/items/{id}/with-scores` to include all related data
   - Return score metadata (criteria name, value, max value, unit)

**Investigation Needed:**
- Check if scores are in API response but not rendered
- Verify score endpoint structure: `/api/lighter/{lighterId}/scores` or similar
- Confirm authentication allows score access

---

### 1.3 Post-Login Navigation Redirect (MEDIUM PRIORITY)
**Current Behavior:** Upon login, user is automatically redirected to CreateCollection screen.

**Problem:**
- User should NOT be forced to create a collection immediately
- User should be able to browse existing collections first
- Many users will have existing collections (like current test user)
- Creates poor UX and blocks users from accessing their own data

**Root Cause:**
- Navigation logic in auth flow automatically directs to CreateCollection
- No check for existing collections before redirect
- Combined with Issue #1.4, this creates a contradictory state

**Solution:**
1. **Post-login Navigation Logic:**
   ```
   After successful login:
   - Fetch user's existing collections
   - If collections exist → Redirect to CollectionTab (DashboardOverview)
   - If no collections → Redirect to CreateCollection or CollectionTab with "empty state" message
   ```

2. **Implementation:**
   - Modify auth service to check collection existence before navigation
   - Create conditional navigation based on collection count
   - Show appropriate empty state if no collections

---

### 1.4 Inconsistent Collection State (CRITICAL)
**Current Behavior:** Login succeeds but user is asked to create collection despite already having one.

**Problem:**
- User redirected to CreateCollection even though they have existing collections
- Existing items and collections are not visible
- Indicates data persistence or loading issue

**Root Cause:**
Multiple possibilities:
1. Collections not loaded after login (async timing issue)
2. Session/auth state not properly persisted
3. Wrong user data being loaded
4. AsyncStorage not syncing properly
5. API not returning user's collections

**Solution:**
1. **Debug First:**
   - Log user ID after login
   - Log collections returned from API
   - Check AsyncStorage content
   - Verify session token is valid

2. **Fix Navigation Logic:**
   - After login, explicitly load user collections
   - Wait for collections to load before rendering navigation
   - Show loading indicator if data is being fetched
   - Only redirect to CreateCollection if collections array is empty

3. **Persist Session Data:**
   - Store user collections in AsyncStorage with cache timestamp
   - Check cache validity on app load
   - Refresh data periodically

---

### 1.5 File Upload Not Working (HIGH PRIORITY)
**Current Behavior:** Image uploads for items and avatars fail (404 errors on stored files).

**Symptoms:**
- Form submission succeeds but images not accessible
- URLs point to `localhost:8081/storage/avatars/...` and `localhost:8081/storage/items/...`
- These paths return 404 - files don't exist on frontend server
- Backend likely stores files at different location (e.g., 127.0.0.1:8000)

**Root Cause:**
1. Files uploaded to backend (127.0.0.1:8000) but frontend tries to load from localhost:8081
2. File storage path mismatch between where they're stored and where app expects them
3. CORS or proxy not configured for static file serving
4. Frontend dev server not configured to proxy storage requests

**Solution:**

**Option A: Backend-Serve Storage Files**
```
Modify image URLs in response:
- Backend should return full URLs: http://127.0.0.1:8000/storage/avatars/{filename}
- Or return relative paths and configure frontend base URL
```

**Option B: Frontend Proxy**
```
Configure frontend dev server to proxy storage requests:
/storage/avatars/* → http://127.0.0.1:8000/storage/avatars/*
/storage/items/* → http://127.0.0.1:8000/storage/items/*
```

**Option C: Environment Configuration**
```
Add environment variable for storage base URL:
REACT_APP_STORAGE_URL=http://127.0.0.1:8000/storage
Use in image components: `${REACT_APP_STORAGE_URL}/avatars/${filename}`
```

**Recommended Approach:** Option C (most flexible and scalable)

**Implementation Steps:**
1. Check what URLs backend returns for uploaded files
2. If relative: add base URL configuration
3. If already full URLs: verify backend is actually storing files
4. Test with curl/Postman to verify file exists on backend
5. Configure frontend to use backend URL for image loading

---

## 2. API ENHANCEMENT CONSIDERATIONS

### 2.1 Should We Modify the API?

**Current State:**
- API exists and partially works
- Some data is missing or in non-optimal format
- Frontend makes multiple requests to load item details

**Analysis:**

| Aspect | Current Approach | Optimized Approach |
|--------|-------------------|-------------------|
| **Categories** | Frontend fetches item, receives category ID only | API returns category names in item response |
| **Scores** | Separate endpoint call required | Include scores in item detail response |
| **Collections** | Basic list endpoint | Endpoint with item count, created date, etc. |
| **User Collections** | Default empty on login | Include user's collections in auth response |
| **File Storage** | Returns relative paths | Returns full accessible URLs |

### 2.2 Recommended API Improvements (Priority Order)

**CRITICAL - Do This First:**
1. **Fix file storage URLs**
   - Return full backend URLs in upload responses
   - Or configure reverse proxy on frontend

2. **Include category names in item responses**
   - Modify `/api/items/{id}` endpoint
   - Ensure category objects include display names

**HIGH PRIORITY - Do Next:**
3. **Create `/api/user` endpoint enhancement**
   - Return user profile with collections count
   - Help determine if user has collections after login

4. **Optimize item details endpoint**
   - `/api/items/{id}` should include:
     - Full category data (names, not just IDs)
     - All scores for the item
     - Item images/avatars with accessible URLs

**MEDIUM PRIORITY - Nice to Have:**
5. **Create bulk category endpoint**
   - `/api/categories` - fetch all categories once
   - Cache on frontend instead of individual lookups

6. **Create collection summary endpoint**
   - `/api/collections/{id}` includes item count, last modified, etc.

### 2.3 API Routes to Add/Modify

```
EXISTING (to modify):
- PATCH /api/items/{id}
  → Return full category names, not just IDs
  
- GET /api/items/{id}
  → Include full category objects with names
  → Include scores array
  → Ensure file paths are accessible

NEW (to create):
- GET /api/categories
  → List all categories with IDs and names
  → Cache on frontend
  
- GET /api/user
  → Include collections count/list
  → Help login flow determine navigation
```

---

## 3. DETAILED IMPLEMENTATION ROADMAP

### Phase 1: Immediate Fixes (Unblock Core Use Case)
**Goal:** Get existing user to see their data after login

**Tasks:**
1. Fix post-login navigation logic
   ```javascript
   After login, before navigation:
   - Load user collections (GET /api/collections?user_id=X)
   - If collections.length > 0 → Navigate to CollectionTab
   - If collections.length === 0 → Show empty state OR navigate to CreateCollection
   ```

2. Fix collection loading
   ```javascript
   In DashboardOverview:
   - Add loading state while fetching collections
   - Display collections properly without forcing creation
   ```

3. Debug category display
   ```javascript
   Add logging:
   - Log item objects returned from API
   - Log category field structure
   - Verify which field contains category name
   ```

4. Fix file URLs
   ```javascript
   Create environment config:
   REACT_APP_API_URL=http://127.0.0.1:8000
   REACT_APP_STORAGE_URL=http://127.0.0.1:8000/storage
   
   Use in image components:
   <Image source={{uri: `${REACT_APP_STORAGE_URL}/avatars/${path}`}} />
   ```

**Estimated Time:** 2-3 hours
**Blocking:** Categories display, upload functionality, post-login flow

---

### Phase 2: Data Display Improvements
**Goal:** Show all data (categories, scores) properly

**Tasks:**
1. Implement category name display
   - Update mapper to use actual names
   - Add fallback messaging for missing categories

2. Implement score display
   - Create score visualization component
   - Fetch scores with items
   - Display on item cards/detail views

3. Test file uploads end-to-end
   - Verify files are actually stored
   - Verify URLs work from frontend

**Estimated Time:** 2-3 hours
**Blocking:** Score visibility, category clarity

---

### Phase 3: API Optimization
**Goal:** Reduce requests, improve performance

**Tasks:**
1. Backend modifications
   - Enhance responses to include category names
   - Include scores in item details
   - Fix file URL returns

2. Frontend optimization
   - Reduce API calls per operation
   - Cache category data
   - Implement efficient loading

**Estimated Time:** 3-4 hours
**Impact:** Better performance, cleaner code

---

## 4. TESTING CHECKLIST

After implementing fixes, test these scenarios:

### Authentication Flow
- [ ] Login with existing user who has collections
- [ ] Not automatically redirected to CreateCollection
- [ ] Can see existing collections immediately
- [ ] Can navigate and browse items

### Data Display
- [ ] Category names display correctly (not "Mechanism #1")
- [ ] Scores visible on item cards
- [ ] All required data rendered without errors

### File Operations
- [ ] Upload avatar on profile → visible immediately
- [ ] Upload item image → visible immediately
- [ ] Images accessible from all devices

### Navigation
- [ ] Post-login doesn't force collection creation
- [ ] Can browse public collections without login
- [ ] Can create new collection when needed

---

## 5. CURRENT CONSOLE ERRORS - ANALYSIS

**Observed Errors:**
1. `GET http://localhost:8081/ 404` - Page not served at root
   - Expected, dev server serves bundle

2. `GET http://127.0.0.1:8000/api/user 401` - Unauthorized
   - Need valid auth token in request
   - Check token persistence

3. `GET http://localhost:8081/storage/* 404` - Files not found
   - **This is the upload issue** - see solution in Section 1.5

4. `RootTabs > Explore, RootTabs > Explore > Explore` duplicate screen name warning
   - Minor issue, doesn't break functionality
   - Can fix in navigation configuration

5. `props.pointerEvents` deprecated
   - React Native Web warning
   - Non-blocking, can update to style.pointerEvents

6. `aria-hidden` blocking focus
   - Accessibility issue from navigation library
   - May resolve after React Native Navigation update

---

## 6. SUMMARY & NEXT STEPS

### What's Working:
✅ Authentication and token management
✅ Basic navigation structure  
✅ Collection CRUD operations
✅ Backend API endpoints functional

### What Needs Fixing (Priority Descending):
1. **File URLs** - Upload works but files not accessible
2. **Post-login navigation** - Forces collection creation incorrectly
3. **Category names** - Shows IDs instead of names
4. **Score display** - Scores not shown at all
5. **Session persistence** - Collections not loading after login

### Recommended Action Plan:
1. **Today:** Fix post-login logic and file URLs (unblock user flow)
2. **Tomorrow:** Fix category display and implement score rendering
3. **Next:** Test end-to-end and optimize API if needed

### Decision: API Modifications
**Recommend:** YES, modify API for these points:
- Return full category names in item responses
- Include scores in item detail endpoint
- Return accessible file URLs
- Include collection metadata

**Rationale:** Reduces frontend complexity, prevents data mismatches, improves performance

---

**Document Last Updated:** Current Session
**Status:** Analysis Complete - Ready for Implementation
