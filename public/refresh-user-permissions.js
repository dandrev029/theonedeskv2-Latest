// Force refresh user permissions
console.log('=== REFRESHING USER PERMISSIONS ===');

if (window.app && window.app.$store) {
    console.log('Refreshing user data...');
    
    // Force refresh user data which will reload permissions
    axios.get('api/auth/user').then(function (response) {
        console.log('User data refreshed:', response.data);
        
        // Update the store with fresh data
        window.app.$store.commit('updateUser', response.data);
        
        console.log('New permissions:', window.app.$store.state.permissions);
        console.log('ReportController permission:', window.app.$store.state.permissions['App.Http.Controllers.Api.Dashboard.ReportController']);
        
        // Reload the page to apply changes
        setTimeout(() => {
            console.log('Reloading page...');
            window.location.reload();
        }, 1000);
        
    }).catch(function (error) {
        console.error('Error refreshing user data:', error);
    });
} else {
    console.log('Vue app not available, reloading page...');
    window.location.reload();
}
