# 🔧 Troubleshooting Admin CRUD Operations

## Issues Fixed

### 1. ✅ Specialities Endpoint Mismatch
**Problem:** Frontend was calling `/admin/specialities` but route is `/admin/specialites`

**Fixed:** Updated `AcademicManagement.jsx` to use correct endpoint `/admin/specialites`

---

## Common Issues & Solutions

### Issue: "Cannot GET /api/admin/years" or 404 errors

**Possible Causes:**
1. Routes not registered
2. Backend not running
3. Wrong API base URL

**Solutions:**
```bash
# Check if routes are registered
cd backend
php artisan route:list --path=admin/years

# Clear route cache
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Restart backend server
php artisan serve
```

### Issue: "Unauthorized" or 401 errors

**Possible Causes:**
1. Not logged in as admin
2. Token expired
3. Missing Authorization header

**Solutions:**
1. Logout and login again as admin
2. Check browser console for token
3. Verify `axios.defaults.headers.common['Authorization']` is set

### Issue: "Validation failed" errors

**Possible Causes:**
1. Missing required fields
2. Wrong data format
3. Database constraints

**Solutions:**
1. Check browser console for validation errors
2. Verify all required fields are filled
3. Check database schema matches model

### Issue: Data not showing after creation

**Possible Causes:**
1. API call succeeded but UI not refreshing
2. Wrong speciality selected
3. Data saved to different speciality

**Solutions:**
1. Check browser console for API responses
2. Verify selected speciality matches
3. Refresh the page

---

## Testing Steps

### 1. Test Backend Routes

```bash
cd backend
php artisan route:list | grep admin
```

Should show:
- `GET|HEAD  api/admin/years`
- `POST      api/admin/years`
- `GET|HEAD  api/admin/semesters`
- `POST      api/admin/semesters`
- `GET|HEAD  api/admin/groups`
- `POST      api/admin/groups`

### 2. Test API Endpoints Directly

```bash
# Get auth token first (login)
TOKEN="your_token_here"

# Test GET years
curl -X GET "http://localhost:8000/api/admin/years?speciality_id=1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Test POST year
curl -X POST "http://localhost:8000/api/admin/years" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "speciality_id": 1,
    "year_number": 1,
    "name": {"fr": "Première Année", "ar": "السنة الأولى"},
    "order": 1
  }'
```

### 3. Check Browser Console

Open browser DevTools (F12) and check:
1. **Console tab:** Look for errors
2. **Network tab:** Check API requests/responses
3. **Application tab:** Verify token is stored

### 4. Verify Database

```bash
cd backend
php artisan tinker

# Check if tables exist
DB::table('years')->count();
DB::table('semesters')->count();
DB::table('groups')->count();

# Check if data exists
App\Models\Year::with('speciality')->get();
```

---

## Frontend Debugging

### Enable Detailed Logging

The component now logs:
- API request data
- API response data
- Error details
- Validation errors

Check browser console for these logs.

### Common Frontend Issues

1. **CORS Errors**
   - Check `config/cors.php`
   - Verify frontend URL is in allowed origins

2. **Base URL Wrong**
   - Check `.env` file: `VITE_API_URL`
   - Default: `http://localhost:8000/api`
   - Production: `https://infspsb.com/api`

3. **Token Not Sent**
   - Check `AuthContext.jsx`
   - Verify token is in localStorage
   - Check axios headers

---

## Quick Fixes

### Clear All Caches

```bash
cd backend
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Rebuild Frontend

```bash
cd frontend
npm run build
```

### Check Logs

```bash
# Backend logs
cd backend
tail -f storage/logs/laravel.log

# Check for errors
grep -i error storage/logs/laravel.log
```

---

## Verification Checklist

- [ ] Backend server is running
- [ ] Frontend is built and running
- [ ] Logged in as admin
- [ ] Token is valid
- [ ] Routes are registered
- [ ] Database tables exist
- [ ] Migrations are run
- [ ] CORS is configured
- [ ] API base URL is correct
- [ ] Browser console shows no errors

---

## Still Not Working?

1. **Check Network Tab:**
   - Open DevTools → Network
   - Try creating a year
   - Check the request/response

2. **Check Backend Logs:**
   ```bash
   tail -f backend/storage/logs/laravel.log
   ```

3. **Test with Postman/curl:**
   - Test API directly
   - Verify backend works

4. **Check Database:**
   ```bash
   php artisan tinker
   App\Models\Year::all();
   ```

---

## Contact

If issues persist, provide:
1. Browser console errors
2. Network tab request/response
3. Backend logs
4. Steps to reproduce

