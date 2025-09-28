// CodeSnoutr specific JavaScript functionality
// Note: Alpine.js is automatically handled by Livewire - no need to import/start manually

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode
    initializeDarkMode();
    
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize copy to clipboard functionality
    initializeCopyToClipboard();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize button enhancements
    initializeButtonEnhancements();
});

/**
 * Initialize dark mode functionality
 */
function initializeDarkMode() {
    // The dark mode is already handled by Alpine.js in the layout
    // This is for any additional dark mode specific functionality
    
    // Listen for system theme changes
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            document.documentElement.classList.toggle('dark', e.matches);
        }
    });
}

/**
 * Initialize tooltips for truncated text
 */
function initializeTooltips() {
    const truncatedElements = document.querySelectorAll('.truncate-tooltip');
    
    truncatedElements.forEach(element => {
        if (element.scrollWidth > element.clientWidth) {
            element.title = element.textContent;
        }
    });
}

/**
 * Initialize copy to clipboard functionality
 */
function initializeCopyToClipboard() {
    const copyButtons = document.querySelectorAll('[data-copy-target]');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-copy-target');
            const target = document.getElementById(targetId);
            
            if (target) {
                const textToCopy = target.textContent || target.value;
                
                navigator.clipboard.writeText(textToCopy).then(() => {
                    showToast('Copied to clipboard!', 'success');
                }).catch(() => {
                    // Fallback for older browsers
                    fallbackCopyTextToClipboard(textToCopy);
                });
            }
        });
    });
}

/**
 * Fallback copy to clipboard for older browsers
 */
function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            showToast('Copied to clipboard!', 'success');
        }
    } catch (err) {
        console.error('Unable to copy to clipboard', err);
        showToast('Failed to copy to clipboard', 'error');
    }
    
    document.body.removeChild(textArea);
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initializeSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white transition-all duration-300 transform translate-x-0`;
    
    switch (type) {
        case 'success':
            toast.className += ' bg-green-500';
            break;
        case 'error':
            toast.className += ' bg-red-500';
            break;
        case 'warning':
            toast.className += ' bg-yellow-500';
            break;
        default:
            toast.className += ' bg-blue-500';
    }
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

/**
 * Debounce function for performance optimization
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance optimization
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

/**
 * Initialize enhanced button functionality
 */
function initializeButtonEnhancements() {
    // Add ripple effect to buttons
    function createRipple(event) {
        const button = event.currentTarget;
        
        // Don't add ripple if button is disabled or loading
        if (button.disabled || button.classList.contains('btn--loading')) {
            return;
        }

        const circle = document.createElement('span');
        const diameter = Math.max(button.clientWidth, button.clientHeight);
        const radius = diameter / 2;

        circle.style.width = circle.style.height = `${diameter}px`;
        circle.style.left = `${event.clientX - button.offsetLeft - radius}px`;
        circle.style.top = `${event.clientY - button.offsetTop - radius}px`;
        circle.classList.add('ripple');

        const ripple = button.getElementsByClassName('ripple')[0];
        if (ripple) {
            ripple.remove();
        }

        button.appendChild(circle);
        
        // Remove ripple after animation
        setTimeout(() => {
            if (circle.parentNode) {
                circle.remove();
            }
        }, 600);
    }

    // Add ripple effect to all buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        // Remove existing listeners to prevent duplicates
        button.removeEventListener('click', createRipple);
        button.addEventListener('click', createRipple);
        
        // Add keyboard interaction feedback
        button.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.classList.add('btn--pressed');
            }
        });
        
        button.addEventListener('keyup', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                this.classList.remove('btn--pressed');
            }
        });
    });

    // Add focus enhancement for keyboard navigation
    let isUsingKeyboard = false;
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            isUsingKeyboard = true;
            document.body.classList.add('keyboard-navigation');
        }
    });

    document.addEventListener('mousedown', function() {
        isUsingKeyboard = false;
        document.body.classList.remove('keyboard-navigation');
    });
}

// Add enhanced button styles
const buttonStyles = document.createElement('style');
buttonStyles.id = 'enhanced-button-styles';
buttonStyles.textContent = `
    /* Ripple Effect */
    .btn {
        position: relative;
        overflow: hidden;
    }

    .ripple {
        position: absolute;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.6);
        pointer-events: none;
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
    }

    .btn--primary .ripple,
    .btn--danger .ripple,
    .btn--success .ripple,
    .btn--warning .ripple {
        background-color: rgba(255, 255, 255, 0.4);
    }

    .btn--secondary .ripple,
    .btn--ghost .ripple,
    .btn--outline-primary .ripple,
    .btn--outline-secondary .ripple {
        background-color: rgba(0, 0, 0, 0.1);
    }

    .dark .btn--secondary .ripple,
    .dark .btn--ghost .ripple,
    .dark .btn--outline-primary .ripple,
    .dark .btn--outline-secondary .ripple {
        background-color: rgba(255, 255, 255, 0.2);
    }

    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    /* Enhanced pressed state */
    .btn--pressed {
        transform: scale(0.95);
        transition: transform 0.1s ease-in-out;
    }

    /* Enhanced focus for keyboard navigation */
    .keyboard-navigation .btn:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
    }

    /* Enhanced visual feedback */
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced disabled state */
    .btn:disabled,
    .btn[disabled] {
        transform: none !important;
        box-shadow: none !important;
        opacity: 0.6;
        cursor: not-allowed;
    }
`;

// Add styles to head if not already present
if (!document.getElementById('enhanced-button-styles')) {
    document.head.appendChild(buttonStyles);
}

// Global utilities
window.CodeSnoutr = {
    showToast,
    debounce,
    throttle,
    initializeButtonEnhancements
};

// LiveWire hooks for better integration
document.addEventListener('livewire:navigated', function () {
    // Re-initialize components that might have been replaced
    initializeTooltips();
    initializeCopyToClipboard();
    initializeSmoothScrolling();
    initializeButtonEnhancements();
});