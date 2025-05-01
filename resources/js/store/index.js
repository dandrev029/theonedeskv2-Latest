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

                    // Emit an event to notify components that user data is loaded
                    Vue.nextTick(() => {
                        console.log('Emitting user-loaded event');
                        if (window.app && window.app.$root) {
                            window.app.$root.$emit('user-loaded', response.data);
                        }
                    });
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
            // Check for duplicates by ID
            const existingIndex = state.notifications.findIndex(n => n.id === notification.id);
            if (existingIndex !== -1) {
                // If notification already exists, update it instead of adding a duplicate
                state.notifications.splice(existingIndex, 1, notification);
                console.log('Updated existing notification instead of adding duplicate');
                return;
            }

            // Check for content duplicates within the last 10 seconds
            const contentKey = `${notification.title}|${notification.message}`;
            const now = Date.now();
            const recentDuplicate = state.notifications.some(n => {
                const nContentKey = `${n.title}|${n.message}`;
                if (nContentKey === contentKey) {
                    // Check if the notification was created within the last 10 seconds
                    const existingTime = n.timestamp || new Date(n.created_at).getTime();
                    const timeDiff = Math.abs(now - existingTime);
                    return timeDiff < 10000; // 10 seconds in milliseconds
                }
                return false;
            });

            if (recentDuplicate) {
                console.log('Skipping duplicate notification in store:', notification.title);
                return;
            }

            // Add the notification to the beginning of the array
            state.notifications.unshift(notification);

            // Only increment unread count if notification is not read
            if (!notification.is_read) {
                state.unreadNotificationsCount++;
            }

            // Limit the number of notifications in the store to prevent memory issues
            if (state.notifications.length > 50) {
                state.notifications = state.notifications.slice(0, 50);
            }
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
            return axios.get('/api/notifications')
                .then(response => {
                    if (response && response.data) {
                        // Check if notifications data exists
                        if (response.data.notifications && response.data.notifications.data) {
                            commit('setNotifications', response.data.notifications.data);
                        } else {
                            console.warn('No notifications data in response');
                            commit('setNotifications', []);
                        }

                        // Check if unread count exists
                        if (response.data.unread_count !== undefined) {
                            commit('setUnreadCount', response.data.unread_count);
                        } else {
                            console.warn('No unread count in response');
                            commit('setUnreadCount', 0);
                        }

                        return response.data;
                    } else {
                        console.warn('Invalid response format from notifications API');
                        commit('setNotifications', []);
                        commit('setUnreadCount', 0);
                        return { notifications: { data: [] }, unread_count: 0 };
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                    commit('setNotifications', []);
                    commit('setUnreadCount', 0);
                    return { notifications: { data: [] }, unread_count: 0 };
                });
        },
        markNotificationAsRead({ commit }, notificationId) {
            return axios.post(`/api/notifications/${notificationId}/mark-as-read`)
                .then(response => {
                    commit('markAsRead', notificationId);
                    return response.data;
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                    return { success: false, error: error.message };
                });
        },
        markAllNotificationsAsRead({ commit }) {
            return axios.post('/api/notifications/mark-all-as-read')
                .then(response => {
                    commit('markAllAsRead');
                    return response.data;
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                    return { success: false, error: error.message };
                });
        },
        deleteNotification({ commit, state }, notificationId) {
            return axios.delete(`/api/notifications/${notificationId}`)
                .then(response => {
                    const notifications = state.notifications.filter(n => n.id !== notificationId);
                    commit('setNotifications', notifications);

                    // Check if unread count exists in response
                    if (response && response.data && response.data.unread_count !== undefined) {
                        commit('setUnreadCount', response.data.unread_count);
                    }

                    return response.data;
                })
                .catch(error => {
                    console.error('Error deleting notification:', error);
                    // Still remove from local state even if API call fails
                    const notifications = state.notifications.filter(n => n.id !== notificationId);
                    commit('setNotifications', notifications);
                    return { success: false, error: error.message };
                });
        }
    }
});

export default store;

