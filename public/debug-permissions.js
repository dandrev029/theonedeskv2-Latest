// Debug script to check permissions state
// Run this in the browser console to debug permissions

console.log('=== PERMISSION DEBUG INFO ===');

// Check if Vue app is available
if (window.app) {
    console.log('✓ Vue app is available');
    
    // Check store
    if (window.app.$store) {
        console.log('✓ Vuex store is available');
        
        // Check user
        console.log('User:', window.app.$store.state.user);
        console.log('User role ID:', window.app.$store.state.user ? window.app.$store.state.user.role_id : 'No user');
        
        // Check permissions
        console.log('All permissions:', window.app.$store.state.permissions);
        console.log('ReportController permission:', window.app.$store.state.permissions['App.Http.Controllers.Api.Dashboard.ReportController']);
        
        // Check if user is admin
        if (window.app.$store.state.user && window.app.$store.state.user.role_id === 1) {
            console.log('✓ User is admin (role_id = 1)');
        } else {
            console.log('✗ User is not admin');
        }
        
        // List all available permissions
        console.log('Available permissions:');
        Object.keys(window.app.$store.state.permissions).forEach(key => {
            console.log(`  ${key}: ${window.app.$store.state.permissions[key]}`);
        });
        
    } else {
        console.log('✗ Vuex store is not available');
    }
} else {
    console.log('✗ Vue app is not available');
}

// Check localStorage
console.log('Token in localStorage:', localStorage.getItem('token') ? 'Present' : 'Not present');

console.log('=== END DEBUG INFO ===');
