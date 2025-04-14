import Vue from 'vue';
import moment from 'moment';
import 'moment-timezone';

// Global filters
Vue.filter('momentFormatDateTimeAgo', function (value) {
    return moment(value).locale(window.app && window.app.app_date_locale ? window.app.app_date_locale : 'en').fromNow();
});

Vue.filter('momentFormatDateTime', function (value) {
    // Use Asia/Manila timezone if app_timezone is not set
    const timezone = window.app && window.app.app_timezone ? window.app.app_timezone : 'Asia/Manila';
    const locale = window.app && window.app.app_date_locale ? window.app.app_date_locale : 'en';
    const format = window.app && window.app.app_date_format ? window.app.app_date_format : 'YYYY-MM-DD';

    // Check if the value is null or undefined
    if (!value) return '';

    // Create a moment object from the value
    // First try parsing as UTC ISO string (which is how dates are stored in the database)
    let momentValue = moment.utc(value);

    // Convert to the target timezone for display
    momentValue = momentValue.tz(timezone);

    // Format with the time component
    return momentValue.locale(locale).format(format + ' h:mm A');
});

Vue.filter('momentFormatDate', function (value) {
    const locale = window.app && window.app.app_date_locale ? window.app.app_date_locale : 'en';
    const format = window.app && window.app.app_date_format ? window.app.app_date_format : 'YYYY-MM-DD';

    return moment(value).locale(locale).format(format);
});
