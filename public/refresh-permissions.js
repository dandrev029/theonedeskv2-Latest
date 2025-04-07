// This script will force refresh the permissions in the browser
// Run this in the browser console to update the permissions

// Clear the token to force a re-login
localStorage.removeItem('token');

// Reload the page
window.location.reload();

// After the page reloads, log in again and the permissions should be updated
