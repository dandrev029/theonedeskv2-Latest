/**
 * Vue directive for Intersection Observer
 * Used for infinite scrolling
 */
export default {
    inserted: function (el, binding) {
        const options = {
            root: null,
            rootMargin: '0px',
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                if (typeof binding.value === 'function') {
                    binding.value();
                }
            }
        }, options);
        
        observer.observe(el);
        el._observer = observer;
    },
    unbind: function (el) {
        if (el._observer) {
            el._observer.disconnect();
            delete el._observer;
        }
    }
};
