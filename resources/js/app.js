import './bootstrap';
import Alpine from 'alpinejs';

Alpine.store('modal', {
    open: null,
    show(name) { this.open = name; },
    hide() { this.open = null; },
    is(name) { return this.open === name; },
});

window.Alpine = Alpine;
Alpine.start();
