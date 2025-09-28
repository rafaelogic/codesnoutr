document.addEventListener("DOMContentLoaded",function(){b(),u(),p(),m(),s()});function b(){window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change",e=>{localStorage.getItem("theme")||document.documentElement.classList.toggle("dark",e.matches)})}function u(){document.querySelectorAll(".truncate-tooltip").forEach(e=>{e.scrollWidth>e.clientWidth&&(e.title=e.textContent)})}function p(){document.querySelectorAll("[data-copy-target]").forEach(e=>{e.addEventListener("click",function(){const t=this.getAttribute("data-copy-target"),n=document.getElementById(t);if(n){const o=n.textContent||n.value;navigator.clipboard.writeText(o).then(()=>{r("Copied to clipboard!","success")}).catch(()=>{f(o)})}})})}function f(a){const e=document.createElement("textarea");e.value=a,e.style.position="fixed",e.style.left="-999999px",e.style.top="-999999px",document.body.appendChild(e),e.focus(),e.select();try{document.execCommand("copy")&&r("Copied to clipboard!","success")}catch(t){console.error("Unable to copy to clipboard",t),r("Failed to copy to clipboard","error")}document.body.removeChild(e)}function m(){document.querySelectorAll('a[href^="#"]').forEach(e=>{e.addEventListener("click",function(t){const n=document.querySelector(this.getAttribute("href"));n&&(t.preventDefault(),n.scrollIntoView({behavior:"smooth",block:"start"}))})})}function r(a,e="info"){const t=document.createElement("div");switch(t.className="fixed top-4 right-4 z-50 px-4 py-2 rounded-md shadow-lg text-white transition-all duration-300 transform translate-x-0",e){case"success":t.className+=" bg-green-500";break;case"error":t.className+=" bg-red-500";break;case"warning":t.className+=" bg-yellow-500";break;default:t.className+=" bg-blue-500"}t.textContent=a,document.body.appendChild(t),setTimeout(()=>{t.style.transform="translateX(0)",t.style.opacity="1"},100),setTimeout(()=>{t.style.transform="translateX(100%)",t.style.opacity="0",setTimeout(()=>{document.body.removeChild(t)},300)},3e3)}function y(a,e){let t;return function(...o){const i=()=>{clearTimeout(t),a(...o)};clearTimeout(t),t=setTimeout(i,e)}}function h(a,e){let t;return function(){const n=arguments,o=this;t||(a.apply(o,n),t=!0,setTimeout(()=>t=!1,e))}}function s(){function a(t){const n=t.currentTarget;if(n.disabled||n.classList.contains("btn--loading"))return;const o=document.createElement("span"),i=Math.max(n.clientWidth,n.clientHeight),l=i/2;o.style.width=o.style.height=`${i}px`,o.style.left=`${t.clientX-n.offsetLeft-l}px`,o.style.top=`${t.clientY-n.offsetTop-l}px`,o.classList.add("ripple");const d=n.getElementsByClassName("ripple")[0];d&&d.remove(),n.appendChild(o),setTimeout(()=>{o.parentNode&&o.remove()},600)}document.querySelectorAll(".btn").forEach(t=>{t.removeEventListener("click",a),t.addEventListener("click",a),t.addEventListener("keydown",function(n){(n.key==="Enter"||n.key===" ")&&this.classList.add("btn--pressed")}),t.addEventListener("keyup",function(n){(n.key==="Enter"||n.key===" ")&&this.classList.remove("btn--pressed")})}),document.addEventListener("keydown",function(t){t.key==="Tab"&&document.body.classList.add("keyboard-navigation")}),document.addEventListener("mousedown",function(){document.body.classList.remove("keyboard-navigation")})}const c=document.createElement("style");c.id="enhanced-button-styles";c.textContent=`
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
`;document.getElementById("enhanced-button-styles")||document.head.appendChild(c);window.CodeSnoutr={showToast:r,debounce:y,throttle:h,initializeButtonEnhancements:s};document.addEventListener("livewire:navigated",function(){u(),p(),m(),s()});
