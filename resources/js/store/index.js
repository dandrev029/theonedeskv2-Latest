import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

const store = new Vuex.Store({
    state: {
        user: false,
        permissions: {},
        settings: false,
        notifications: [],
        unreadNotificationsCount: 0,
        darkMode: localStorage.getItem('darkMode') === 'true'
    },
    mutations: {
        setSettings(state, data) {
            state.settings = data;
        },
        login(state, response) {
            console.log('Login response:', response);
            console.log('User departments in login response:', response.user.departments);
            state.user = response.user;
            state.permissions = response.user.role.permissions;
            localStorage.setItem('token', response.token);
            window.axios.defaults.headers.common['Authorization'] = 'Bearer ' + response.token;
            console.log('User departments after setting state in login:', state.user.departments);
        },
        logout(state) {
            axios.post('api/auth/logout').then(function () {
                state.user = false;
            });
            delete window.axios.defaults.headers.common.Authorization;
            localStorage.removeItem('token');
        },
        setUser(state) {
            if (localStorage.getItem('token')) {
                axios.get('api/auth/user').then(function (response) {
                    console.log('User data from API:', response.data);
                    state.user = response.data;
                    state.permissions = response.data.role.permissions;
                    console.log('User departments after setting state:', state.user.departments);
                });
            }
        },
        updateUser(state, response) {
            state.user = response;
            state.permissions = response.role.permissions;
        },
        // Notification Mutations
        setNotifications(state, notifications) {
            state.notifications = notifications;
        },
        addNotification(state, notification) {
            state.notifications.unshift(notification);
            state.unreadNotificationsCount++;
        },
        setUnreadCount(state, count) {
            state.unreadNotificationsCount = count;
        },
        markAsRead(state, notificationId) {
            const notification = state.notifications.find(n => n.id === notificationId);
            if (notification && !notification.is_read) {
                notification.is_read = true;
                state.unreadNotificationsCount = Math.max(0, state.unreadNotificationsCount - 1);
            }
        },
        markAllAsRead(state) {
            state.notifications.forEach(notification => {
                notification.is_read = true;
            });
            state.unreadNotificationsCount = 0;
        },
        toggleDarkMode(state) {
            state.darkMode = !state.darkMode;
            localStorage.setItem('darkMode', state.darkMode);

            // Apply or remove dark-mode class to body and set data-theme attribute
            if (state.darkMode) {
                document.body.classList.add('dark-mode');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.body.classList.remove('dark-mode');
                document.documentElement.setAttribute('data-theme', 'light');
            }

            // Dispatch a custom event that components can listen for
            document.dispatchEvent(new CustomEvent('darkmode-changed', {
                detail: { darkMode: state.darkMode }
            }));
        },
    },
    actions: {
        fetchNotifications({ commit }) {
            return axios.get('/api/notifications').then(response => {
                commit('setNotifications', response.data.notifications.data);
                commit('setUnreadCount', response.data.unread_count);
                return response.data;
            });
        },
        markNotificationAsRead({ commit }, notificationId) {
            return axios.post(`/api/notifications/${notificationId}/mark-as-read`).then(response => {
                commit('markAsRead', notificationId);
                return response.data;
            });
        },
        markAllNotificationsAsRead({ commit }) {
            return axios.post('/api/notifications/mark-all-as-read').then(response => {
                commit('markAllAsRead');
                return response.data;
            });
        },
        deleteNotification({ commit, state }, notificationId) {
            return axios.delete(`/api/notifications/${notificationId}`).then(response => {
                const notifications = state.notifications.filter(n => n.id !== notificationId);
                commit('setNotifications', notifications);
                commit('setUnreadCount', response.data.unread_count);
                return response.data;
            });
        }
    }
});

export default store;

