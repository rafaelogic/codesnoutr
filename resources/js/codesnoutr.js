/**
 * CodeSnoutr JavaScript
 * Handles interactive features and UI enhancements
 */

// Dark mode toggle functionality
function initDarkMode() {
    // Check for saved theme preference or default to 'light' mode
    const currentTheme = localStorage.getItem('theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    if (currentTheme === 'dark') {
        document.documentElement.classList.add('dark');
    }
}

// Toggle dark mode (for compatibility with existing code)
function toggleDarkMode() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    // Dispatch custom event for Livewire components
    window.dispatchEvent(new CustomEvent('theme-changed', {
        detail: { theme: isDark ? 'dark' : 'light' }
    }));
}

// Copy to clipboard functionality
function copyToClipboard(text, button) {
    navigator.clipboard.writeText(text).then(() => {
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Copied!';
        button.classList.add('bg-green-600', 'hover:bg-green-700');
        button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-600', 'hover:bg-green-700');
            button.classList.add('bg-gray-600', 'hover:bg-gray-700');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

// Smooth scroll to element
function scrollToElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Auto-hide notifications
function setupNotifications() {
    const notifications = document.querySelectorAll('.notification[data-auto-hide]');
    notifications.forEach(notification => {
        const delay = parseInt(notification.dataset.autoHide) || 5000;
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, delay);
    });
}

// Search highlighting
function highlightSearchTerms(container, searchTerm) {
    if (!searchTerm || searchTerm.length < 2) return;
    
    const elements = container.querySelectorAll('p, span, div:not([data-no-highlight])');
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    
    elements.forEach(element => {
        if (element.children.length === 0) {
            element.innerHTML = element.textContent.replace(regex, '<mark class="bg-yellow-200 dark:bg-yellow-600">$1</mark>');
        }
    });
}

// File size formatter
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Time formatter
function formatDuration(seconds) {
    if (seconds < 60) {
        return `${seconds.toFixed(1)}s`;
    } else if (seconds < 3600) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}m ${remainingSeconds.toFixed(0)}s`;
    } else {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
}

// Progress bar animation
function animateProgressBar(element, targetValue, duration = 1000) {
    const startValue = 0;
    const increment = targetValue / (duration / 16);
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        element.style.width = `${currentValue}%`;
        element.setAttribute('aria-valuenow', Math.round(currentValue));
    }, 16);
}

// Keyboard shortcuts
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[name="search"], input[placeholder*="search" i]');
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Ctrl/Cmd + D for dark mode toggle
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            toggleDarkMode();
        }
        
        // Escape to close modals/dropdowns
        if (e.key === 'Escape') {
            const openDropdowns = document.querySelectorAll('[x-data] [x-show="true"]');
            openDropdowns.forEach(dropdown => {
                // Trigger click outside to close Alpine.js dropdowns
                document.body.click();
            });
        }
    });
}

// Table sorting helpers
function sortTable(tableId, columnIndex, direction = 'asc') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Try to parse as numbers first
        const aNum = parseFloat(aValue);
        const bNum = parseFloat(bValue);
        
        if (!isNaN(aNum) && !isNaN(bNum)) {
            return direction === 'asc' ? aNum - bNum : bNum - aNum;
        }
        
        // Fall back to string comparison
        return direction === 'asc' 
            ? aValue.localeCompare(bValue)
            : bValue.localeCompare(aValue);
    });
    
    // Re-append sorted rows
    rows.forEach(row => tbody.appendChild(row));
}

// Auto-refresh functionality
let autoRefreshInterval = null;

function startAutoRefresh(interval = 30000) {
    stopAutoRefresh(); // Clear any existing interval
    
    autoRefreshInterval = setInterval(() => {
        // Trigger Livewire refresh if available
        if (window.Livewire) {
            window.Livewire.emit('refresh');
        }
    }, interval);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// Code syntax highlighting (basic)
function highlightCode(codeElement) {
    const code = codeElement.textContent;
    
    // Basic PHP syntax highlighting
    let highlighted = code
        // PHP tags
        .replace(/(&lt;\?php|\?&gt;)/g, '<span class="text-purple-600">$1</span>')
        // Keywords
        .replace(/\b(class|function|public|private|protected|static|const|if|else|elseif|while|for|foreach|try|catch|finally|return|throw|new|extends|implements|interface|abstract|final|namespace|use|as)\b/g, 
                '<span class="text-blue-600">$1</span>')
        // Strings
        .replace(/(["'])((?:\\.|(?!\1)[^\\])*?)\1/g, '<span class="text-green-600">$1$2$1</span>')
        // Comments
        .replace(/(\/\/.*$|\/\*[\s\S]*?\*\/)/gm, '<span class="text-gray-500">$1</span>')
        // Variables
        .replace(/\$\w+/g, '<span class="text-orange-600">$&</span>');
    
    codeElement.innerHTML = highlighted;
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    setupNotifications();
    setupKeyboardShortcuts();
    
    // Highlight all code blocks
    document.querySelectorAll('pre code, .code-block').forEach(highlightCode);
    
    // Setup copy buttons for code blocks
    document.querySelectorAll('pre').forEach(pre => {
        const button = document.createElement('button');
        button.className = 'absolute top-2 right-2 px-2 py-1 text-xs bg-gray-600 hover:bg-gray-700 text-white rounded opacity-0 group-hover:opacity-100 transition-opacity';
        button.innerHTML = 'Copy';
        button.onclick = () => copyToClipboard(pre.textContent, button);
        
        pre.style.position = 'relative';
        pre.classList.add('group');
        pre.appendChild(button);
    });
});

// Listen for Livewire events
document.addEventListener('livewire:load', () => {
    // Re-initialize features after Livewire updates
    Livewire.hook('message.processed', () => {
        setupNotifications();
        document.querySelectorAll('pre code, .code-block').forEach(highlightCode);
    });
});

// Export functions for use in Livewire components
window.CodeSnoutr = {
    toggleDarkMode,
    copyToClipboard,
    scrollToElement,
    highlightSearchTerms,
    formatFileSize,
    formatDuration,
    animateProgressBar,
    sortTable,
    startAutoRefresh,
    stopAutoRefresh
};
