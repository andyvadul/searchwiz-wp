# SearchWiz Troubleshooting Guide

Solutions for common issues with SearchWiz.

## Common Issues

### Search Results Not Appearing

**Symptoms:**
- No results dropdown when typing
- Empty results container

**Solutions:**

1. **Check minimum character setting**
   - Go to Settings → SearchWiz → General
   - Verify "Minimum Characters" is set appropriately (default: 3)
   - Try typing more characters

2. **Verify Ajax is enabled**
   - Settings → SearchWiz → General → Enable Ajax Search
   - Must be checked for instant results

3. **Check for JavaScript errors**
   - Open browser Developer Tools (F12)
   - Look for errors in Console tab
   - Report any SearchWiz-related errors

4. **Clear caches**
   - Clear browser cache
   - Clear any caching plugin cache
   - Try in incognito/private browsing mode

### Search Not Finding Content

**Symptoms:**
- Known content not appearing in results
- Partial matches not working

**Solutions:**

1. **Verify post type is searchable**
   - Settings → SearchWiz → Advanced → Post Types
   - Ensure desired content types are checked

2. **Check exclusion settings**
   - Settings → SearchWiz → Advanced → Exclusions
   - Verify content isn't in excluded categories

3. **Rebuild search index**
   - Settings → SearchWiz → Advanced → Rebuild Index
   - Wait for completion message

### Styling Issues

**Symptoms:**
- Results not displaying correctly
- Broken layout or missing styles

**Solutions:**

1. **Theme conflicts**
   - Try switching to a default theme (Twenty Twenty-Four)
   - If it works, your theme may have conflicting CSS

2. **Plugin conflicts**
   - Deactivate other plugins temporarily
   - Reactivate one by one to find conflict

3. **Cache issues**
   - Clear all caches
   - Check if styles load correctly

### Performance Issues

**Symptoms:**
- Slow search results
- High server load during searches

**Solutions:**

1. **Reduce results count**
   - Settings → SearchWiz → Display → Results per page
   - Lower number = faster response

2. **Limit post types**
   - Only enable post types you need
   - Fewer types = faster queries

3. **Check server resources**
   - Verify adequate PHP memory
   - Consider server upgrade if needed

## Debug Mode

Enable debug mode to diagnose issues:

1. Go to Settings → SearchWiz → Advanced
2. Enable "Debug Mode"
3. Reproduce the issue
4. Check browser console for detailed logs
5. **Disable when done** - affects performance

## Getting Help

If these solutions don't resolve your issue:

1. **Check documentation** - [Features Guide](features.md)
2. **WordPress.org forums** - [Support Forum](https://wordpress.org/support/plugin/searchwiz/)
3. **GitHub issues** - [Report a bug](https://github.com/andyvadul/searchwiz-wp/issues)

When reporting issues, include:
- WordPress version
- PHP version
- Active theme
- Other active plugins
- Steps to reproduce
- Any error messages

