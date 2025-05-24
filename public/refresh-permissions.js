// This script will force refresh the permissions in the browser
// Run this in the browser console to update the permissions

console.log('Refreshing permissions...');

// Method 1: Force refresh user data and permissions
if (window.app && window.app.$store) {
    console.log('Refreshing user data via store...');
    window.app.$store.commit('setUser');
    setTimeout(() => {
        console.log('Current permissions:', window.app.$store.state.permissions);
        console.log('ReportController permission:', window.app.$store.state.permissions['App.Http.Controllers.Api.Dashboard.ReportController']);
    }, 1000);
} else {
    console.log('Store not available, clearing token and reloading...');
    // Clear the token to force a re-login
    localStorage.removeItem('token');

    // Reload the page
    window.location.reload();
}

// After the page reloads, log in again and the permissions should be updated
