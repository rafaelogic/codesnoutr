/**
 * Button Enhancement Script
 * Adds additional visual feedback and accessibility improvements
 */

document.addEventListener('DOMContentLoaded', function() {
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
            circle.remove();
        }, 600);
    }

    // Add ripple effect to all buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
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

    // Add hover sound feedback (optional - can be enabled via data attribute)
    buttons.forEach(button => {
        if (button.dataset.soundFeedback === 'true') {
            button.addEventListener('mouseenter', function() {
                // Play subtle hover sound
                if (window.AudioContext || window.webkitAudioContext) {
                    playHoverSound();
                }
            });
        }
    });

    function playHoverSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {
            // Silently fail if audio is not supported
        }
    }

    // Add focus enhancement for keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });

    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
});

// CSS for ripple effect and enhancements
const style = document.createElement('style');
style.textContent = `
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

    /* Button shine effect on hover */
    .btn::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: left 0.5s ease-in-out;
        pointer-events: none;
    }

    .btn:hover::after {
        left: 100%;
    }

    /* Disable shine effect for ghost and outline buttons */
    .btn--ghost::after,
    .btn--outline-primary::after,
    .btn--outline-secondary::after {
        display: none;
    }

    /* Enhanced loading state */
    .btn--loading {
        position: relative;
    }

    .btn--loading::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        border-radius: inherit;
        z-index: 1;
    }

    .dark .btn--loading::before {
        background: rgba(0, 0, 0, 0.5);
    }

    /* Enhanced button variants for better contrast */
    .btn--primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: 1px solid #1d4ed8;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn--primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        border-color: #1e40af;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .btn--secondary {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        border: 2px solid #9ca3af;
        color: #374151;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .btn--secondary:hover {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        border-color: #6b7280;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .dark .btn--secondary {
        background: linear-gradient(135deg, #4b5563 0%, #374151 100%);
        border-color: #6b7280;
        color: #f9fafb;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .dark .btn--secondary:hover {
        background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
        border-color: #9ca3af;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    /* Enhanced danger, success, warning buttons */
    .btn--danger {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border: 1px solid #991b1b;
    }

    .btn--success {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
        border: 1px solid #166534;
    }

    .btn--warning {
        background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
        border: 1px solid #a16207;
    }

    /* Enhanced ghost button */
    .btn--ghost {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .dark .btn--ghost {
        background: rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .btn--ghost:hover {
        background: rgba(255, 255, 255, 0.95);
        border-color: rgba(0, 0, 0, 0.2);
    }

    .dark .btn--ghost:hover {
        background: rgba(0, 0, 0, 0.5);
        border-color: rgba(255, 255, 255, 0.3);
    }
`;

document.head.appendChild(style);