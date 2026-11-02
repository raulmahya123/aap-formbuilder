import './bootstrap';
import './ui-actions';
import './ui-feedback';
import './ui-modules';
import './ui-table-export';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

window.Alpine = Alpine;
Alpine.plugin(collapse);

Alpine.start();
