// Force refresh permissions without logging out
// Run this in the browser console

console.log('Force refreshing permissions...');

if (window.app && window.app.$store) {
    // Force refresh user data which will reload permissions
    axios.get('api/auth/user').then(function (response) {
        console.log('User data refreshed:', response.data);
        
        // Update the store with fresh data
        window.app.$store.commit('updateUser', response.data);
        
        console.log('New permissions:', window.app.$store.state.permissions);
        console.log('ReportController permission:', window.app.$store.state.permissions['App.Http.Controllers.Api.Dashboard.ReportController']);
        
        // Check if Reports menu should be visible now
        setTimeout(() => {
            const reportsMenuItem = document.querySelector('a[href="/dashboard/reports"]');
            if (reportsMenuItem) {
                console.log('✓ Reports menu item is now visible!');
            } else {
                console.log('✗ Reports menu item is still not visible');
                console.log('Try refreshing the page manually');
            }
        }, 500);
        
    }).catch(function (error) {
        console.error('Error refreshing user data:', error);
        console.log('Try logging out and logging back in');
    });
} else {
    console.log('Vue app or store not available. Try refreshing the page.');
}
