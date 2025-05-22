import './bootstrap';
import '@fortawesome/fontawesome-free/css/all.min.css';
import Alpine from 'alpinejs';

// Registrar componente de acciones del volumen
Alpine.data('volumeActions', (userId, volumeId) => ({
    status: null,
    async checkStatus() {
        const response = await fetch(`/users/${userId}/volumes/${volumeId}/check-status`);
        const data = await response.json();
        this.status = data.status;
    },
    async triggerAction(action) {
        const button = event?.currentTarget;
        if (button) {
            button.classList.add('scale-90');
            setTimeout(() => button.classList.remove('scale-90'), 100);
        }
        await action();
    },
    async addToLibrary() {
        await this.triggerAction(async () => {
            await fetch(`/users/${userId}/volumes/${volumeId}/add-to-library`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            await this.checkStatus();
        });
    },
    async addToWishlist() {
        await this.triggerAction(async () => {
            await fetch(`/users/${userId}/volumes/${volumeId}/add-to-wishlist`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            await this.checkStatus();
        });
    },
    async remove() {
        await this.triggerAction(async () => {
            await fetch(`/users/${userId}/volumes/${volumeId}/remove`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                }
            });
            await this.checkStatus();
        });
    }
}));

window.Alpine = Alpine;

Alpine.start();
