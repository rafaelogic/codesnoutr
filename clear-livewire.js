// Script to clear Livewire state and force a clean reload
// Run this in browser console to reset Livewire completely

console.log('Clearing Livewire state...');

// Clear all Livewire components
if (typeof Livewire !== 'undefined') {
    Object.keys(Livewire.all()).forEach(id => {
        try {
            Livewire.all()[id].destroy();
            console.log('Destroyed component:', id);
        } catch (e) {
            console.log('Could not destroy component:', id, e);
        }
    });
}

// Clear local storage
localStorage.clear();
sessionStorage.clear();

// Clear any cached data
if ('caches' in window) {
    caches.keys().then(names => {
        names.forEach(name => {
            caches.delete(name);
        });
    });
}

console.log('Livewire state cleared. Reloading page...');

// Force reload
setTimeout(() => {
    location.reload(true);
}, 1000);
