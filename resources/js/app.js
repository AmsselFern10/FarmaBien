import './bootstrap';
import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import collapse from '@alpinejs/collapse';

// Registrar plugins
Alpine.plugin(persist);
Alpine.plugin(collapse);

// Configurar Alpine
window.Alpine = Alpine;

// Función helper para pantallas
Alpine.magic('screen', () => (size) => {
    const sizes = {
        'sm': 640,
        'md': 768,
        'lg': 1024,
        'xl': 1280,
        '2xl': 1536,
    };
    return window.innerWidth >= sizes[size];
});

Alpine.start();