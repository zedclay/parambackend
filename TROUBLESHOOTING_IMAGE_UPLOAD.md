# 🔧 Troubleshooting Image Upload for Planning

## Common Issues

### 1. Image Not Uploading

**Check:**
- Browser console for errors
- Network tab to see the request
- Backend logs: `tail -f storage/logs/laravel.log`

**Solutions:**
```bash
# Check storage permissions
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Ensure storage link exists
php artisan storage:link

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### 2. 404 Error on Upload

**Check:**
- Route exists: `php artisan route:list | grep plannings`
- API base URL is correct
- Planning ID exists

### 3. Image Not Displaying

**Check:**
- Storage link: `ls -la public/storage`
- Image path in database
- File exists: `ls -la storage/app/public/plannings/`

**Fix:**
```bash
# Recreate storage link
rm public/storage
php artisan storage:link
```

### 4. CORS Error

**Check:**
- `config/cors.php` allows file uploads
- Frontend API URL is correct

### 5. File Size Too Large

**Check:**
- PHP `upload_max_filesize` in `php.ini`
- Laravel validation: max 10MB (10240 KB)

**Fix:**
```ini
# In php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

## Testing

### Test Upload via cURL:
```bash
curl -X PUT "http://localhost:8000/api/admin/plannings/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@/path/to/image.png"
```

### Check Database:
```bash
php artisan tinker
$planning = App\Models\Planning::find(1);
$planning->image_path;
```

### Check Storage:
```bash
ls -la storage/app/public/plannings/
```

## Debug Steps

1. **Check Browser Console:**
   - Look for "Uploading image for planning:" log
   - Check error messages

2. **Check Network Tab:**
   - Verify request is sent
   - Check response status
   - Check response body

3. **Check Backend Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Verify File Upload:**
   - Check if file is in FormData
   - Verify Content-Type header

## Quick Fixes

```bash
# 1. Clear all caches
php artisan optimize:clear

# 2. Recreate storage link
php artisan storage:link

# 3. Check permissions
chmod -R 775 storage bootstrap/cache

# 4. Restart server
php artisan serve
```

