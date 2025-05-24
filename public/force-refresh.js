// Force refresh user permissions and reload page
console.log('=== FORCE REFRESH PERMISSIONS ===');

// Method 1: Try to refresh via store
if (window.app && window.app.$store) {
    console.log('Refreshing user data via store...');
    
    // Force refresh user data
    axios.get('api/auth/user').then(function (response) {
        console.log('User data refreshed:', response.data);
        
        // Update the store with fresh data
        window.app.$store.commit('updateUser', response.data);
        
        console.log('New permissions:', window.app.$store.state.permissions);
        console.log('ReportController permission:', window.app.$store.state.permissions['App.Http.Controllers.Api.Dashboard.ReportController']);
        
        // Reload the page after a short delay
        setTimeout(() => {
            console.log('Reloading page to apply changes...');
            window.location.reload();
        }, 1000);
        
    }).catch(function (error) {
        console.error('Error refreshing user data:', error);
        console.log('Reloading page anyway...');
        window.location.reload();
    });
} else {
    console.log('Vue app not available, reloading page directly...');
    window.location.reload();
}
