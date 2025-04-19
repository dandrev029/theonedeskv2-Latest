import Vue from 'vue';

import Loading from '@/components/elements/loading';
import Attachment from '@/components/elements/attachment';
import DarkModeToggle from '@/components/elements/dark-mode-toggle';

import InputSwitch from '@/components/forms/input-switch';
import ImageInput from '@/components/forms/image-input';
import InputSelect from '@/components/forms/input-select';
import InputSelectScrollable from '@/components/forms/input-select-scrollable';
import DropdownSelect from '@/components/forms/dropdown-select';
import InputColor from '@/components/forms/input-color';
import InputWysiwyg from '@/components/forms/input-wysiwyg';

import NotificationDropdown from '@/components/notifications/NotificationDropdown';

// Ticket Components
import TicketCard from '@/components/tickets/TicketCard';
import TicketSkeleton from '@/components/tickets/TicketSkeleton';

Vue.component('loading', Loading);
Vue.component('attachment', Attachment);
Vue.component('dark-mode-toggle', DarkModeToggle);

Vue.component('input-switch', InputSwitch);
Vue.component('image-input', ImageInput);
Vue.component('input-select', InputSelect);
Vue.component('input-select-scrollable', InputSelectScrollable);
Vue.component('dropdown-select', DropdownSelect);
Vue.component('input-color', InputColor);
Vue.component('input-wysiwyg', InputWysiwyg);

Vue.component('notification-dropdown', NotificationDropdown);

// Register Ticket Components
Vue.component('ticket-card', TicketCard);
Vue.component('ticket-skeleton', TicketSkeleton);
